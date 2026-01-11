<?php

namespace App\Http\Controllers;

use App\Models\Load;
use App\Models\LoadPickupConfirmation;
use App\Models\LoadPickupConfirmationAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class PickupConfirmationController extends Controller
{
    /**
     * Display the pickup confirmations index page
     */
    public function index()
    {
        return view('pickup-confirmations.index');
    }

    /**
     * Get paginated pickup confirmations (from load_pickup_confirmations table)
     */
    public function getConfirmations(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');

            $query = LoadPickupConfirmation::with('loadRelation')
                ->orderBy('created_at', 'desc');

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('contact_name', 'like', "%{$search}%")
                      ->orWhere('vapi_call_id', 'like', "%{$search}%")
                      ->orWhere('vapi_call_status', 'like', "%{$search}%")
                      ->orWhereHas('loadRelation', function($q) use ($search) {
                          $q->where('load_id', 'like', "%{$search}%")
                            ->orWhere('internal_load_id', 'like', "%{$search}%");
                      });
                });
            }

            $confirmations = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $confirmations
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pickup confirmations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching confirmations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get enqueued requests (pickup confirmation attempts)
     */
    public function getEnqueuedJobs(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');

            // Buscar tentativas de confirmação de pickup
            // Carregar apenas o creator, os loads serão buscados separadamente para evitar TenantScope
            $query = LoadPickupConfirmationAttempt::with('creator')
                ->orderBy('created_at', 'desc');

            // Aplicar filtro de busca
            if ($search) {
                $query->where(function($q) use ($search) {
                    // Buscar pelos loads que correspondem ao termo (sem TenantScope para encontrar todos)
                    $loadIds = Load::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                        ->where('load_id', 'like', "%{$search}%")
                        ->orWhere('internal_load_id', 'like', "%{$search}%")
                        ->pluck('id')
                        ->toArray();
                    
                    $q->whereIn('load_id', $loadIds)
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('job_uuid', 'like', "%{$search}%")
                    ->orWhere('error_message', 'like', "%{$search}%");
                });
            }

            // Paginar resultados
            $attempts = $query->paginate($perPage);
            
            // Buscar loads diretamente usando os IDs das tentativas, sem TenantScope
            $loadIds = collect($attempts->items())->pluck('load_id')->filter()->unique()->toArray();
            $loads = [];
            if (!empty($loadIds)) {
                $loads = Load::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                    ->whereIn('id', $loadIds)
                    ->get()
                    ->keyBy('id');
            }

            // Formatar dados para exibição
            $formattedAttempts = collect($attempts->items())->map(function($attempt) use ($loads) {
                $load = $loads[$attempt->load_id] ?? null;
                
                // Obter load_id manual (campo string criado pelo usuário no SuperDispatcher)
                // Prioridade: loads.load_id > loads.internal_load_id
                $userLoadId = 'N/A';
                $yearMakeModel = 'N/A';
                
                if ($load) {
                    $userLoadId = $load->load_id ?? $load->internal_load_id ?? 'N/A';
                    $yearMakeModel = $load->year_make_model ?? 'N/A';
                }
                
                return [
                    'id' => $attempt->id,
                    'load_relation_id' => $attempt->load_id, // ID da FK (loads.id)
                    'user_load_id' => $userLoadId, // Campo manual load_id do usuário
                    'year_make_model' => $yearMakeModel,
                    'status' => $attempt->status,
                    'job_uuid' => $attempt->job_uuid,
                    'confirmation_id' => $attempt->confirmation_id,
                    'created_by' => $attempt->created_by,
                    'created_by_name' => $attempt->creator ? ($attempt->creator->name ?? 'N/A') : 'N/A',
                    'error_message' => $attempt->error_message,
                    'created_at' => $attempt->created_at,
                    'updated_at' => $attempt->updated_at,
                    'load' => $load ? [
                        'id' => $load->id,
                        'load_id' => $load->load_id,
                        'internal_load_id' => $load->internal_load_id,
                        'year_make_model' => $load->year_make_model,
                        'pickup_name' => $load->pickup_name,
                        'pickup_city' => $load->pickup_city,
                        'pickup_state' => $load->pickup_state,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $formattedAttempts->values()->all(),
                    'current_page' => $attempts->currentPage(),
                    'per_page' => $attempts->perPage(),
                    'total' => $attempts->total(),
                    'last_page' => $attempts->lastPage(),
                    'from' => $attempts->firstItem(),
                    'to' => $attempts->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching enqueued requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract load ID from job payload
     */
    private function extractLoadIdFromPayload(array $payload): ?int
    {
        try {
            // For ConfirmPickupLoadJob, the load ID is in the payload
            if (isset($payload['data']['commandName']) && 
                strpos($payload['data']['commandName'], 'ConfirmPickupLoadJob') !== false) {
                
                // Try to extract from the serialized command
                if (isset($payload['data']['command'])) {
                    $command = unserialize($payload['data']['command'], ['allowed_classes' => [\App\Jobs\ConfirmPickupLoadJob::class]]);
                    if (is_object($command) && isset($command->loadId)) {
                        return $command->loadId;
                    }
                }
            }
            
            // Alternative: try to find loadId in the payload string
            $payloadString = json_encode($payload);
            if (preg_match('/"loadId":(\d+)/', $payloadString, $matches)) {
                return (int)$matches[1];
            }
        } catch (\Exception $e) {
            Log::warning('Error extracting load ID from payload: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Extract error message from exception string
     */
    private function extractErrorMessage(?string $exception): string
    {
        if (!$exception) {
            return 'No error message available';
        }

        // Try to extract the main error message
        if (preg_match('/message":"([^"]+)"/', $exception, $matches)) {
            return $matches[1];
        }

        // Fallback: return first line
        $lines = explode("\n", $exception);
        return $lines[0] ?? 'Unknown error';
    }

    /**
     * Download transcription file
     */
    public function downloadTranscription($id)
    {
        try {
            $confirmation = LoadPickupConfirmation::findOrFail($id);
            
            if (!$confirmation->transcription) {
                return redirect()->back()->with('error', 'Transcription not available');
            }

            // Return the transcription text as a file
            return response($confirmation->transcription)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="transcription_' . $confirmation->vapi_call_id . '.txt"');
        } catch (\Exception $e) {
            Log::error('Error downloading transcription: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error downloading transcription');
        }
    }

    /**
     * Download audio file
     */
    public function downloadAudio($id)
    {
        try {
            $confirmation = LoadPickupConfirmation::findOrFail($id);
            
            if (!$confirmation->call_record_url) {
                return redirect()->back()->with('error', 'Audio URL not available');
            }

            // Download and return the file
            $content = file_get_contents($confirmation->call_record_url);
            
            return response($content)
                ->header('Content-Type', 'audio/mpeg')
                ->header('Content-Disposition', 'attachment; filename="audio_' . $confirmation->vapi_call_id . '.mp3"');
        } catch (\Exception $e) {
            Log::error('Error downloading audio: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error downloading audio');
        }
    }

    /**
     * Retry failed job manually
     */
    public function retryFailedJob(Request $request, $uuid)
    {
        try {
            $failedJob = DB::table('failed_jobs')->where('uuid', $uuid)->first();
            
            if (!$failedJob) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed job not found'
                ], 404);
            }

            // Retry the job
            Artisan::call('queue:retry', ['uuid' => $uuid]);

            return response()->json([
                'success' => true,
                'message' => 'Job has been queued for retry'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrying failed job: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrying job: ' . $e->getMessage()
            ], 500);
        }
    }
}
