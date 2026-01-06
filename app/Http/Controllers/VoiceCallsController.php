<?php

namespace App\Http\Controllers;

use App\Models\LoadPickupConfirmation;
use App\Models\Load;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoiceCallsController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Display the voice calls index page
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $mainUser = $this->billingService->getMainUser($user);
        
        // Get user's credits balance
        $creditsBalance = $mainUser->ai_voice_credits ?? 0.00;
        
        // Get load IDs that belong to this user (TenantScope will filter automatically)
        $loadIds = Load::pluck('id')->toArray();
        
        // Get calls statistics
        $totalCalls = LoadPickupConfirmation::whereIn('load_id', $loadIds)->count();
        
        // Sum retorna centavos (integer), converter para dólares
        $totalCostCents = LoadPickupConfirmation::whereIn('load_id', $loadIds)
            ->whereNotNull('call_cost')
            ->sum('call_cost') ?? 0;
        $totalCost = $totalCostCents / 100;
        
        $successCalls = LoadPickupConfirmation::whereIn('load_id', $loadIds)
            ->where('vapi_call_status', 'success')
            ->count();
        
        return view('voice-calls.index', compact('creditsBalance', 'totalCalls', 'totalCost', 'successCalls'));
    }

    /**
     * Get paginated voice calls
     */
    public function getCalls(Request $request)
    {
        try {
            $user = auth()->user();
            $mainUser = $this->billingService->getMainUser($user);
            
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');

            // Get load IDs that belong to this user (TenantScope will filter automatically)
            $loadIds = Load::pluck('id')->toArray();

            $query = LoadPickupConfirmation::with(['loadRelation' => function($query) {
                $query->with('dispatcher.user');
            }])
            ->whereIn('load_id', $loadIds)
            ->orderBy('created_at', 'desc');

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('vapi_call_id', 'like', "%{$search}%")
                      ->orWhere('contact_name', 'like', "%{$search}%")
                      ->orWhere('vapi_call_status', 'like', "%{$search}%")
                      ->orWhereHas('loadRelation', function($query) use ($search) {
                          $query->where('load_id', 'like', "%{$search}%")
                                ->orWhere('internal_load_id', 'like', "%{$search}%")
                                ->orWhere('pickup_phone', 'like', "%{$search}%");
                      });
                });
            }

            $calls = $query->paginate($perPage);

            // Format data for frontend
            $formattedCalls = $calls->getCollection()->map(function($call) {
                $load = $call->loadRelation;
                
                // Determine success evaluation based on vapi_call_status
                $successEvaluation = $call->vapi_call_status === 'success' ? 'Pass' : 'Fail';
                
                // Determine ended reason (simplified - can be enhanced later)
                $endedReason = 'Assistant Ended Call';
                if ($call->vapi_call_status === 'fail') {
                    $endedReason = 'Call Failed';
                }
                
                // Format duration
                $duration = $call->call_duration ? $this->formatDuration($call->call_duration) : '-';
                
                return [
                    'id' => $call->id,
                    'call_id' => substr($call->vapi_call_id, 0, 12) . '...',
                    'call_id_full' => $call->vapi_call_id,
                    'load_id' => $load->load_id ?? $load->internal_load_id ?? 'N/A',
                    'customer_phone' => $load->pickup_phone ?? '-',
                    'type' => 'Outbound',
                    'ended_reason' => $endedReason,
                    'success_evaluation' => $successEvaluation,
                    'start_time' => $call->created_at->format('M d, Y, H:i'),
                    'duration' => $duration,
                    'duration_seconds' => $call->call_duration,
                    'cost' => $call->call_cost ? number_format($call->call_cost, 2) : '0.00',
                    'status' => $call->vapi_call_status,
                    'has_audio' => !empty($call->call_record_url),
                    'has_transcription' => !empty($call->call_transcription_url),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $formattedCalls,
                    'current_page' => $calls->currentPage(),
                    'per_page' => $calls->perPage(),
                    'total' => $calls->total(),
                    'last_page' => $calls->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching voice calls: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching calls: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format duration in seconds to human readable format (e.g., "1m 31s")
     */
    private function formatDuration($seconds)
    {
        if (!$seconds || $seconds < 1) {
            return '0s';
        }
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        if ($minutes > 0) {
            return $minutes . 'm ' . $remainingSeconds . 's';
        }
        
        return $remainingSeconds . 's';
    }
}
