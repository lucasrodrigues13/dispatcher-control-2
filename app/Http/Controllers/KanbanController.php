<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Container;
use App\Models\Dispatcher;
use App\Models\Employee;
use App\Models\Load;
use App\Models\LoadPickupConfirmation;
use App\Models\LoadPickupConfirmationAttempt;
use App\Jobs\ConfirmPickupLoadJob;
use App\Services\LoadService;
use App\Services\BillingService;
use Illuminate\Support\Facades\DB;
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

        // Load all loads with filters applied, including pending pickup confirmation attempts
        $loads = $query->with('pendingPickupConfirmationAttempt')->orderByDesc('updated_at')->get();

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

            // Validar telefones antes de continuar
            $phoneValidation = $this->validatePhoneNumbers($loads);
            if (!$phoneValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'The phone number field is not filled in; please enter it before making a connection.',
                    'data' => [
                        'loads_without_phone' => $phoneValidation['loads_without_phone']
                    ]
                ], 400);
            }

            // Validar créditos antes de enfileirar
            $creditValidation = $this->validateCredits($loads);
            if (!$creditValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $creditValidation['message'],
                    'data' => [
                        'credits_balance' => $creditValidation['credits_balance'],
                        'required_credits' => $creditValidation['required_credits']
                    ]
                ], 400);
            }

            // Verificar se há tentativas pendentes e filtrar loads
            $loadsToProcess = [];
            $skippedLoads = [];
            
            foreach ($loads as $load) {
                // Verificar se já existe tentativa pendente
                if (LoadPickupConfirmationAttempt::hasPendingAttempt($load->id)) {
                    $skippedLoads[] = $load->id;
                    continue;
                }
                
                $loadsToProcess[] = $load;
            }

            // Retornar erro se todos os loads já têm tentativas pendentes
            if (empty($loadsToProcess)) {
                return response()->json([
                    'success' => false,
                    'message' => 'All selected loads already have pending pickup confirmation attempts. Please wait for the current attempts to complete.',
                    'data' => [
                        'loads_enqueued' => 0,
                        'skipped_load_ids' => $skippedLoads,
                    ]
                ], 400);
            }

            // Enqueue each load for pickup confirmation
            $enqueuedCount = 0;
            $failedLoads = [];
            
            foreach ($loadsToProcess as $load) {
                try {
                    // Criar tentativa antes de enfileirar
                    $attempt = LoadPickupConfirmationAttempt::create([
                        'load_id' => $load->id,
                        'status' => 'pending',
                        'created_by' => auth()->id(),
                    ]);

                    // Enfileirar job
                    ConfirmPickupLoadJob::dispatch($load->id);
                    
                    // O job_uuid será preenchido pelo próprio job quando ele iniciar o processamento
                    // Para rastreamento, usamos a tentativa (attempt) que já tem o load_id

                    $enqueuedCount++;
                } catch (\Exception $e) {
                    Log::error("Error creating attempt for load {$load->id}: " . $e->getMessage());
                    $failedLoads[] = $load->id;
                }
            }
            
            $message = "Successfully enqueued {$enqueuedCount} load(s) for pickup confirmation";
            if (!empty($skippedLoads)) {
                $message .= ". " . count($skippedLoads) . " load(s) were skipped because they already have pending attempts.";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'loads_enqueued' => $enqueuedCount,
                    'load_ids' => array_column($loadsToProcess, 'id'),
                    'skipped_load_ids' => $skippedLoads,
                    'failed_load_ids' => $failedLoads,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Confirm Pickup Loads Error: ' . $e->getMessage(), [
                'load_ids' => $request->input('load_ids'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error confirming loads: ' . $e->getMessage(),
            ], 500);
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

            // Calcular custo final: retorno de custo x 2
            $rawCallCost = isset($arguments['call_cost']) ? (float) $arguments['call_cost'] : 0.00;
            $finalCallCost = $rawCallCost * 2; // Custo da ligação = retorno de custo x 2
            
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
                'call_duration' => isset($arguments['call_duration']) ? (int) $arguments['call_duration'] : null,
                'call_cost' => $finalCallCost > 0 ? $finalCallCost : null, // MoneyCast converte automaticamente para centavos
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

            // Verificar se ligação conectou (para determinar se deduz créditos e tipo de falha)
            $hasCallRecord = !empty($arguments['call_record_url']);
            $hasTranscription = !empty($arguments['call_transcription_url']);
            $hasCallDuration = isset($arguments['call_duration']) && $arguments['call_duration'] > 0;
            $callConnected = $hasCallRecord || $hasTranscription || $hasCallDuration;
            
            // Determinar tipo de falha (se vapi_call_status = 'fail')
            $isCallAttemptFailure = false;
            $failureReason = null;
            
            if ($arguments['vapi_call_status'] === 'fail') {
                // Se não há registro de áudio, transcrição ou duração, é falha na tentativa de conectar
                if (!$callConnected) {
                    $isCallAttemptFailure = true;
                    $failureReason = 'Call attempt failed - call did not connect (no audio record, transcription, or duration)';
                    
                    Log::warning('Pickup confirmation: Call attempt failure', [
                        'load_id' => $load->id,
                        'vapi_call_id' => $arguments['vapi_call_id'],
                        'reason' => $failureReason
                    ]);
                } else {
                    // Ligação conectou mas falhou por outros fatores
                    $failureReason = 'Call connected but failed during the call';
                    
                    Log::info('Pickup confirmation: Call connected but failed', [
                        'load_id' => $load->id,
                        'vapi_call_id' => $arguments['vapi_call_id'],
                        'has_call_record' => $hasCallRecord,
                        'has_transcription' => $hasTranscription,
                        'call_duration' => $arguments['call_duration'] ?? null
                    ]);
                }
            }
            
            // Deduzir créditos apenas se a ligação conectou e tem custo
            // Não deduzir se foi falha na tentativa de conectar (não conectou)
            if ($callConnected && $finalCallCost > 0 && !$isCallAttemptFailure) {
                try {
                    // Buscar dispatcher diretamente pelo ID para evitar conflito com atributo 'dispatcher' (string)
                    $dispatcher = null;
                    if ($load->dispatcher_id) {
                        $dispatcher = Dispatcher::with('user')->find($load->dispatcher_id);
                    }
                    
                    if ($dispatcher && $dispatcher->user) {
                        $billingService = app(BillingService::class);
                        $mainUser = $billingService->getMainUser($dispatcher->user);
                        
                        // MoneyCast trabalha com dólares (float) no código, converte automaticamente para centavos no banco
                        $currentCredits = $mainUser->ai_voice_credits ?? 0.00; // Retorna em dólares
                        
                        if ($currentCredits >= $finalCallCost) {
                            // Deduzir créditos (MoneyCast converte automaticamente para centavos)
                            $newBalance = $currentCredits - $finalCallCost;
                            $mainUser->update([
                                'ai_voice_credits' => max(0, $newBalance) // Garantir que não fique negativo
                            ]);
                            
                            Log::info('Credits deducted for call', [
                                'user_id' => $mainUser->id,
                                'load_id' => $load->id,
                                'vapi_call_id' => $arguments['vapi_call_id'],
                                'raw_cost' => $rawCallCost,
                                'final_cost' => $finalCallCost,
                                'previous_balance' => $currentCredits,
                                'new_balance' => $newBalance,
                            ]);
                        } else {
                            // Créditos insuficientes (não deveria acontecer se validação foi feita antes)
                            Log::warning('Insufficient credits for call deduction', [
                                'user_id' => $mainUser->id,
                                'load_id' => $load->id,
                                'vapi_call_id' => $arguments['vapi_call_id'],
                                'required' => $finalCallCost,
                                'available' => $currentCredits,
                            ]);
                        }
                    } else {
                        Log::warning('Could not find user for credit deduction', [
                            'load_id' => $load->id,
                            'dispatcher_id' => $load->dispatcher_id,
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log erro mas não interrompe o processamento da confirmação
                    Log::error('Error deducting credits for call', [
                        'load_id' => $load->id,
                        'vapi_call_id' => $arguments['vapi_call_id'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Atualizar tentativa relacionada
            $attempt = LoadPickupConfirmationAttempt::getPendingAttempt($load->id);
            if ($attempt) {
                // Se foi falha na tentativa de conectar, marcar como failed
                // Se foi falha durante a ligação ou sucesso, marcar como completed
                if ($isCallAttemptFailure) {
                    $attempt->update([
                        'status' => 'failed',
                        'confirmation_id' => $confirmation->id,
                        'error_message' => $failureReason
                    ]);
                } else {
                    $attempt->update([
                        'status' => 'completed',
                        'confirmation_id' => $confirmation->id,
                    ]);
                }
            } else {
                // Se não encontrou tentativa pendente, buscar a mais recente
                $attempt = LoadPickupConfirmationAttempt::where('load_id', $load->id)
                    ->whereIn('status', ['processing'])
                    ->latest()
                    ->first();
                
                if ($attempt) {
                    if ($isCallAttemptFailure) {
                        $attempt->update([
                            'status' => 'failed',
                            'confirmation_id' => $confirmation->id,
                            'error_message' => $failureReason
                        ]);
                    } else {
                        $attempt->update([
                            'status' => 'completed',
                            'confirmation_id' => $confirmation->id,
                        ]);
                    }
                }
            }

            // Update load status based on confirmation (apenas se não for falha na tentativa de conectar)
            if (!$isCallAttemptFailure) {
                $this->updateLoadPickupStatus($load, $confirmation);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pickup confirmation processed successfully',
                'data' => [
                    'load_id' => $load->id,
                    'confirmation_id' => $confirmation->id,
                    'attempt_id' => $attempt?->id,
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
     * Validate phone numbers for loads
     * 
     * @param \Illuminate\Database\Eloquent\Collection $loads
     * @return array
     */
    private function validatePhoneNumbers($loads): array
    {
        $loadsWithoutPhone = [];
        
        foreach ($loads as $load) {
            $hasPhone = !empty($load->pickup_phone);
            $hasMobile = !empty($load->pickup_mobile);
            
            if (!$hasPhone && !$hasMobile) {
                $loadsWithoutPhone[] = [
                    'id' => $load->id,
                    'load_id' => $load->load_id ?? $load->internal_load_id ?? 'N/A'
                ];
            }
        }
        
        return [
            'valid' => empty($loadsWithoutPhone),
            'loads_without_phone' => $loadsWithoutPhone
        ];
    }

    /**
     * Validate if user has enough credits for calls
     * Estimates minimum cost per call (can be adjusted based on average call duration)
     * 
     * @param \Illuminate\Database\Eloquent\Collection $loads
     * @return array
     */
    private function validateCredits($loads): array
    {
        if ($loads->isEmpty()) {
            return [
                'valid' => true,
                'message' => 'No loads to validate',
                'credits_balance' => 0,
                'required_credits' => 0
            ];
        }

        // Obter usuário principal através do primeiro load
        $firstLoad = $loads->first();
        
        // Buscar dispatcher diretamente pelo ID para evitar conflito com atributo 'dispatcher' (string)
        if (!$firstLoad->dispatcher_id) {
            return [
                'valid' => false,
                'message' => 'Load does not have a dispatcher assigned',
                'credits_balance' => 0,
                'required_credits' => 0
            ];
        }
        
        $dispatcher = Dispatcher::with('user')->find($firstLoad->dispatcher_id);
        
        if (!$dispatcher || !$dispatcher->user) {
            return [
                'valid' => false,
                'message' => 'Could not find user to validate credits',
                'credits_balance' => 0,
                'required_credits' => 0
            ];
        }

        $billingService = app(BillingService::class);
        $mainUser = $billingService->getMainUser($dispatcher->user);
        
        // MoneyCast retorna em dólares (float)
        $currentCredits = $mainUser->ai_voice_credits ?? 0.00;
        
        // Estimar custo mínimo por ligação (exemplo: $0.40 = custo mínimo estimado de $0.20 x 2)
        // Como não sabemos o custo exato antes da ligação, usamos um valor mínimo conservador
        $estimatedCostPerCall = 0.40; // $0.40 por ligação (baseado em custo mínimo estimado x 2)
        $requiredCredits = count($loads) * $estimatedCostPerCall;
        
        if ($currentCredits < $requiredCredits) {
            return [
                'valid' => false,
                'message' => "Insufficient credits. You need at least $" . number_format($requiredCredits, 2) . " to make " . count($loads) . " call(s), but you have $" . number_format($currentCredits, 2) . " available. Please recharge your credits.",
                'credits_balance' => $currentCredits,
                'required_credits' => $requiredCredits
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Credits validated successfully',
            'credits_balance' => $currentCredits,
            'required_credits' => $requiredCredits
        ];
    }

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

