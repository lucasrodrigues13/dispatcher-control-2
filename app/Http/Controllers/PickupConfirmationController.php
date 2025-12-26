<?php

namespace App\Http\Controllers;

use App\Models\Load;
use App\Models\LoadPickupConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Get enqueued jobs and failed jobs
     */
    public function getEnqueuedJobs(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');

            // Get pending jobs
            $pendingJobsQuery = DB::table('jobs')
                ->where('queue', 'default')
                ->orderBy('created_at', 'desc');

            if ($search) {
                $pendingJobsQuery->where('payload', 'like', "%{$search}%");
            }

            $pendingJobs = $pendingJobsQuery->get()->map(function($job) {
                $payload = json_decode($job->payload, true);
                $loadId = $this->extractLoadIdFromPayload($payload);
                
                return [
                    'id' => $job->id,
                    'load_id' => $loadId,
                    'status' => 'pending',
                    'attempts' => $job->attempts ?? 0,
                    'created_at' => $job->created_at,
                    'available_at' => $job->available_at,
                    'payload' => $payload,
                ];
            });

            // Get failed jobs
            $failedJobsQuery = DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc');

            if ($search) {
                $failedJobsQuery->where('payload', 'like', "%{$search}%");
            }

            $failedJobs = $failedJobsQuery->get()->map(function($job) {
                $payload = json_decode($job->payload, true);
                $loadId = $this->extractLoadIdFromPayload($payload);
                
                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'load_id' => $loadId,
                    'status' => 'failed',
                    'attempts' => $job->attempts ?? 0,
                    'failed_at' => $job->failed_at,
                    'exception' => $job->exception,
                    'error_message' => $this->extractErrorMessage($job->exception),
                ];
            });

            // Get processed jobs (jobs that were completed)
            // We'll check load_pickup_confirmations to see which loads were processed
            $processedLoadIds = LoadPickupConfirmation::pluck('load_id')->toArray();
            
            // Combine all jobs
            $allJobs = collect()
                ->merge($pendingJobs->map(fn($j) => array_merge($j, ['status' => 'pending'])))
                ->merge($failedJobs->map(fn($j) => array_merge($j, ['status' => 'failed'])))
                ->map(function($job) use ($processedLoadIds) {
                    if (isset($job['load_id']) && in_array($job['load_id'], $processedLoadIds)) {
                        $job['status'] = 'processed';
                    }
                    return $job;
                });

            // Apply search filter if needed
            if ($search) {
                $allJobs = $allJobs->filter(function($job) use ($search) {
                    return stripos($job['load_id'] ?? '', $search) !== false ||
                           stripos($job['status'] ?? '', $search) !== false;
                });
            }

            // Paginate manually
            $currentPage = $request->get('page', 1);
            $items = $allJobs->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $total = $allJobs->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $items,
                    'current_page' => $currentPage,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching enqueued jobs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching jobs: ' . $e->getMessage()
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
            
            if (!$confirmation->call_transcription_url) {
                return redirect()->back()->with('error', 'Transcription URL not available');
            }

            // Download and return the file
            $content = file_get_contents($confirmation->call_transcription_url);
            
            return response($content)
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
            \Artisan::call('queue:retry', ['id' => $uuid]);

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
