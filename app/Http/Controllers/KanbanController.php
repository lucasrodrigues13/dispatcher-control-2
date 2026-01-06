<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Container;
use App\Models\Dispatcher;
use App\Models\Employee;
use App\Models\Load;
use App\Models\LoadPickupConfirmation;
use App\Jobs\ConfirmPickupLoadJob;
use App\Services\LoadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class KanbanController extends Controller
{
    protected $loadService;

    public function __construct(LoadService $loadService)
    {
        $this->loadService = $loadService;
    }

    /**
     * Kanban Mode View - Display loads in kanban board
     */
    public function kanbanMode(Request $request)
    {
        $dispatchers = Dispatcher::with('user')
            ->where('user_id', auth()->id())
            ->first();
        $carriers = Carrier::with("user")->get();
        $employees = Employee::with("user")->get();
        
        // Use LoadService to build filtered query (reuses same logic as list view)
        $query = $this->loadService->buildFilteredQuery($request);

        // Load all loads with filters applied
        $loads = $query->orderByDesc('updated_at')->get();

        // ⭐ Organize loads by kanban column status
        $loadsByStatus = $this->organizeLoadsByStatus($loads);

        $containers = Container::with(['containerLoads.loadItem'])->get();

        // Check if user has AI Voice Service enabled
        $user = auth()->user();
        $billingService = app(\App\Services\BillingService::class);
        $mainUser = $billingService->getMainUser($user);
        $hasAiVoiceService = false;
        
        if ($mainUser->subscription && $mainUser->subscription->plan) {
            $hasAiVoiceService = (bool) ($mainUser->subscription->plan->ai_voice_service ?? false);
        }

        return view('load.kanbanMode', compact('loads', 'dispatchers', 'carriers', 'containers', 'employees', 'loadsByStatus', 'hasAiVoiceService'));
    }

    /**
     * Organize loads by kanban column status
     */
    private function organizeLoadsByStatus($loads)
    {
        $organized = [
            'new' => [],
            'assigned' => [],
            'picked_up' => [],
            'delivered' => [],
            'billed' => [],
            'paid' => []
        ];

        foreach ($loads as $load) {
            // Use kanban_status field, defaulting to 'new' if not set
            $status = $load->kanban_status ?? 'new';
            
            // If status is not in our list, put in 'new'
            if (!isset($organized[$status])) {
                $status = 'new';
            }
            
            $organized[$status][] = $load;
        }

        return $organized;
    }


    /**
     * Update kanban status via drag and drop
     */
    public function updateKanbanStatus(Request $request, $id)
    {
        try {
            $load = Load::findOrFail($id);
            $newStatus = $request->input('status');
            
            // Validate status
            $validStatuses = ['new', 'assigned', 'picked_up', 'delivered', 'billed', 'paid'];
            if (!in_array($newStatus, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status'
                ], 400);
            }
            
            // Update kanban status
            $load->kanban_status = $newStatus;
            $load->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'id' => $load->id,
                    'status' => $load->kanban_status
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update load via AJAX from kanban card edit
     */
    public function updateLoadAjax(Request $request, $id)
    {
        try {
            $load = Load::findOrFail($id);

            // Validação similar ao método update existente
            $request->validate([
                'load_id' => 'nullable|string|max:255',
                'internal_load_id' => 'nullable|string|max:255',
                'creation_date' => 'nullable|date_format:Y-m-d',
                'dispatcher' => 'nullable|string|max:255',
                'trip' => 'nullable|string|max:255',
                'year_make_model' => 'nullable|string|max:255',
                'vin' => 'nullable|string|max:255',
                'lot_number' => 'nullable|string|max:255',
                'has_terminal' => 'nullable|boolean',
                'dispatched_to_carrier' => 'nullable|string|max:255',
                'pickup_name' => 'nullable|string|max:255',
                'pickup_address' => 'nullable|string|max:255',
                'pickup_city' => 'nullable|string|max:255',
                'pickup_state' => 'nullable|string|max:255',
                'pickup_zip' => 'nullable|string|max:50',
                'scheduled_pickup_date' => 'nullable|date_format:Y-m-d',
                'pickup_phone' => 'nullable|string|max:50',
                'pickup_mobile' => 'nullable|string|max:50',
                'actual_pickup_date' => 'nullable|date_format:Y-m-d',
                'buyer_number' => 'nullable|integer',
                'pickup_notes' => 'nullable|string',
                'delivery_name' => 'nullable|string|max:255',
                'delivery_address' => 'nullable|string|max:255',
                'delivery_city' => 'nullable|string|max:255',
                'delivery_state' => 'nullable|string|max:255',
                'delivery_zip' => 'nullable|string|max:50',
                'scheduled_delivery_date' => 'nullable|date_format:Y-m-d',
                'actual_delivery_date' => 'nullable|date_format:Y-m-d',
                'delivery_phone' => 'nullable|string|max:50',
                'delivery_mobile' => 'nullable|string|max:50',
                'delivery_notes' => 'nullable|string',
                'shipper_name' => 'nullable|string|max:255',
                'shipper_phone' => 'nullable|string|max:50',
                'price' => 'nullable|numeric',
                'expenses' => 'nullable|numeric',
                'broker_fee' => 'nullable|numeric',
                'driver_pay' => 'nullable|numeric',
                'payment_method' => 'nullable|string|max:255',
                'paid_amount' => 'nullable|numeric',
                'paid_method' => 'nullable|string|max:255',
                'reference_number' => 'nullable|string|max:255',
                'receipt_date' => 'nullable|date_format:Y-m-d',
                'payment_terms' => 'nullable|string|max:255',
                'payment_notes' => 'nullable|string',
                'payment_status' => 'nullable|string|max:255',
                'invoice_number' => 'nullable|string|max:255',
                'invoice_notes' => 'nullable|string',
                'invoice_date' => 'nullable|date_format:Y-m-d',
                'driver' => 'nullable|string|max:255',
            ]);

            // Atualizar o load
            $load->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Load updated successfully!',
                'data' => $load
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating load: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get card fields configuration for current user
     */
    public function getCardFieldsConfig()
    {
        $userId = auth()->id();
        $config = \App\Models\UserCardConfig::getCardFieldsConfig($userId);

        return response()->json($config);
    }

    /**
     * Save card fields configuration
     */
    public function saveCardFieldsConfig(Request $request)
    {
        $userId = auth()->id();
        $config = $request->input('config', []);

        try {
            \App\Models\UserCardConfig::saveCardFieldsConfig($userId, $config);

            return response()->json([
                'success' => true,
                'message' => 'Configuration saved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of distinct drivers for filter dropdown
     */
    public function getDriversList()
    {
        try {
            $drivers = Load::whereNotNull('driver')
                          ->where('driver', '!=', '')
                          ->distinct()
                          ->pluck('driver')
                          ->sort()
                          ->values();

            return response()->json($drivers);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Sincronizar kanban_status de todos os loads visíveis na tela
     * Usa os mesmos filtros aplicados no Kanban para sincronizar apenas os loads exibidos
     * Útil para recalcular status após edições manuais
     */
    public function syncNewLoadsKanbanStatus(Request $request)
    {
        try {
            // ⭐ IMPORTANTE: Usar os mesmos filtros do Kanban para sincronizar apenas loads visíveis
            $query = $this->loadService->buildFilteredQuery($request);
            
            // Buscar todos os loads que estão sendo exibidos na tela (com filtros aplicados)
            $loads = $query->orderByDesc('id')->get();
            
            $stats = [
                'new' => 0,
                'assigned' => 0,
                'picked_up' => 0,
                'delivered' => 0,
                'billed' => 0,
                'paid' => 0
            ];
            
            $updated = 0;
            
            foreach ($loads as $load) {
                $oldStatus = $load->kanban_status;
                $newStatus = $this->loadService->determineLoadStatus($load);
                
                // Atualizar apenas se o status mudou
                if ($oldStatus !== $newStatus) {
                    $load->kanban_status = $newStatus;
                    $load->save();
                    $updated++;
                }
                
                $stats[$newStatus]++;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Sync completed! {$updated} load(s) updated out of " . count($loads) . " load(s) processed.",
                'data' => [
                    'total_processed' => count($loads),
                    'updated' => $updated,
                    'stats' => $stats
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm pickup for assigned loads - Enqueue loads for N8N processing
     */
    public function confirmPickupLoads(Request $request)
    {
        try {
            $request->validate([
                'load_ids' => 'required|array',
                'load_ids.*' => 'required|integer|exists:loads,id',
            ]);

            $loadIds = $request->input('load_ids');
            
            // Verify loads are in assigned status
            $loads = Load::whereIn('id', $loadIds)
                ->where('kanban_status', 'assigned')
                ->get();

            if ($loads->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No assigned loads found with the provided IDs'
                ], 404);
            }

            // Enqueue each load for pickup confirmation
            $enqueuedCount = 0;
            foreach ($loads as $load) {
                ConfirmPickupLoadJob::dispatch($load->id);
                $enqueuedCount++;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Successfully enqueued {$enqueuedCount} load(s) for pickup confirmation",
                'data' => [
                    'loads_enqueued' => $enqueuedCount,
                    'load_ids' => $loads->pluck('id')->toArray(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Confirm Pickup Loads Error: ' . $e->getMessage(), [
                'load_ids' => $request->input('load_ids'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't show error to user, just log it
            // Return success even if there's an error (jobs will be processed asynchronously)
            return response()->json([
                'success' => true,
                'message' => 'Loads have been queued for processing. They will be processed asynchronously.',
                'data' => [
                    'loads_enqueued' => count($request->input('load_ids', [])),
                    'load_ids' => $request->input('load_ids', []),
                ]
            ]);
        }
    }

    /**
     * Webhook endpoint to receive pickup confirmation updates from N8N
     */
    public function receivePickupConfirmation(Request $request)
    {
        try {
            // Validate payload structure
            $request->validate([
                'toolCall' => 'required|array',
                'toolCall.function' => 'required|array',
                'toolCall.function.name' => 'required|string',
                'toolCall.function.arguments' => 'required|array',
                'toolCall.function.arguments.loadId' => 'required|string',
                'toolCall.function.arguments.vapi_call_id' => 'required|string',
                'toolCall.function.arguments.vapi_call_status' => 'required|string|in:success,fail',
            ]);

            $toolCall = $request->input('toolCall');
            $function = $toolCall['function'];
            $arguments = $function['arguments'];

            // Find load by load_id or internal_load_id
            $load = Load::where('load_id', $arguments['loadId'])
                ->orWhere('internal_load_id', $arguments['loadId'])
                ->first();

            if (!$load) {
                Log::warning('Load not found for pickup confirmation', [
                    'loadId' => $arguments['loadId'],
                    'vapi_call_id' => $arguments['vapi_call_id'] ?? null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Load not found'
                ], 404);
            }

            // Check if this confirmation already exists (idempotency)
            $existingConfirmation = LoadPickupConfirmation::where('vapi_call_id', $arguments['vapi_call_id'])->first();
            
            if ($existingConfirmation) {
                Log::info('Pickup confirmation already processed', [
                    'vapi_call_id' => $arguments['vapi_call_id'],
                    'load_id' => $load->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Confirmation already processed',
                    'data' => [
                        'load_id' => $load->id,
                        'confirmation_id' => $existingConfirmation->id,
                    ]
                ]);
            }

            // Prepare confirmation data
            $confirmationData = [
                'load_id' => $load->id,
                'contact_name' => $arguments['contactName'] ?? null,
                'car_ready_for_pickup' => $arguments['car_ready_for_pickup'] ?? false,
                'not_ready_when' => isset($arguments['not_ready_when']) ? \Carbon\Carbon::parse($arguments['not_ready_when']) : null,
                'hours_of_operation' => $arguments['hours_of_operation'] ?? null,
                'car_condition' => $arguments['car_condition'] ?? null,
                'is_address_correct' => $arguments['is_address_correct'] ?? true,
                'special_instructions' => $arguments['special_instructions'] ?? null,
                'call_record_url' => $arguments['call_record_url'] ?? null,
                'call_transcription_url' => $arguments['call_transcription_url'] ?? null,
                'vapi_call_id' => $arguments['vapi_call_id'],
                'vapi_call_status' => $arguments['vapi_call_status'],
                'raw_payload' => $request->all(),
            ];

            // Handle different address if provided
            if (isset($arguments['different_address']) && !$arguments['is_address_correct']) {
                $differentAddress = $arguments['different_address'];
                $confirmationData['pickup_address'] = $differentAddress['pickup_address'] ?? null;
                $confirmationData['pickup_city'] = $differentAddress['pickup_city'] ?? null;
                $confirmationData['pickup_state'] = $differentAddress['pickup_state'] ?? null;
                $confirmationData['pickup_zip'] = $differentAddress['pickup_zip'] ?? null;
            }

            // Create confirmation record
            $confirmation = LoadPickupConfirmation::create($confirmationData);

            // Update load status based on confirmation
            $this->updateLoadPickupStatus($load, $confirmation);

            return response()->json([
                'success' => true,
                'message' => 'Pickup confirmation processed successfully',
                'data' => [
                    'load_id' => $load->id,
                    'confirmation_id' => $confirmation->id,
                    'pickup_status' => $load->pickup_status,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Pickup confirmation validation error', [
                'errors' => $e->errors(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Receive Pickup Confirmation Error: ' . $e->getMessage(), [
                'payload' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing pickup confirmation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update load pickup status based on confirmation
     */
    private function updateLoadPickupStatus(Load $load, LoadPickupConfirmation $confirmation): void
    {
        $updateData = [
            'pickup_last_confirmed_at' => now(),
        ];

        // Determine pickup status based on confirmation
        if ($confirmation->car_ready_for_pickup) {
            $updateData['pickup_status'] = 'READY';
        } else {
            $updateData['pickup_status'] = 'NOT_READY';
        }

        // Update address if different address was provided
        if (!$confirmation->is_address_correct && $confirmation->pickup_address) {
            $updateData['pickup_address'] = $confirmation->pickup_address;
            $updateData['pickup_city'] = $confirmation->pickup_city;
            $updateData['pickup_state'] = $confirmation->pickup_state;
            $updateData['pickup_zip'] = $confirmation->pickup_zip;
        }

        // Update special instructions if provided
        if ($confirmation->special_instructions) {
            $updateData['pickup_notes'] = ($load->pickup_notes ? $load->pickup_notes . "\n\n" : '') . 
                "Confirmation: " . $confirmation->special_instructions;
        }

        $load->update($updateData);
    }

}

