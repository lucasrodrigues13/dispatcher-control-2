<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Container;
use App\Models\Dispatcher;
use App\Models\Employee;
use App\Models\Load;
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

        return view('load.kanbanMode', compact('loads', 'dispatchers', 'carriers', 'containers', 'employees', 'loadsByStatus'));
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
     * Confirm assigned loads - Send selected loads to N8N webhook
     */
    public function confirmAssignedLoads(Request $request)
    {
        try {
            $request->validate([
                'load_ids' => 'required|array',
                'load_ids.*' => 'required|integer|exists:loads,id',
            ]);

            $loadIds = $request->input('load_ids');
            
            // Load all selected loads with all relationships
            $loads = Load::with([
                'carrier.user',
                'dispatcher.user',
                'employee.user'
            ])
            ->whereIn('id', $loadIds)
            ->where('kanban_status', 'assigned')
            ->get();

            if ($loads->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No assigned loads found with the provided IDs'
                ], 404);
            }

            // Prepare data for N8N webhook
            $payload = [
                'loads' => $loads->map(function ($load) {
                    return [
                        'id' => $load->id,
                        'load_id' => $load->load_id,
                        'internal_load_id' => $load->internal_load_id,
                        'creation_date' => $load->creation_date?->format('Y-m-d'),
                        'dispatcher' => $load->dispatcher,
                        'trip' => $load->trip,
                        'year_make_model' => $load->year_make_model,
                        'vin' => $load->vin,
                        'lot_number' => $load->lot_number,
                        'has_terminal' => $load->has_terminal,
                        'dispatched_to_carrier' => $load->dispatched_to_carrier,
                        'pickup_name' => $load->pickup_name,
                        'pickup_address' => $load->pickup_address,
                        'pickup_city' => $load->pickup_city,
                        'pickup_state' => $load->pickup_state,
                        'pickup_zip' => $load->pickup_zip,
                        'scheduled_pickup_date' => $load->scheduled_pickup_date?->format('Y-m-d'),
                        'pickup_phone' => $load->pickup_phone,
                        'pickup_mobile' => $load->pickup_mobile,
                        'actual_pickup_date' => $load->actual_pickup_date?->format('Y-m-d'),
                        'buyer_number' => $load->buyer_number,
                        'pickup_notes' => $load->pickup_notes,
                        'delivery_name' => $load->delivery_name,
                        'delivery_address' => $load->delivery_address,
                        'delivery_city' => $load->delivery_city,
                        'delivery_state' => $load->delivery_state,
                        'delivery_zip' => $load->delivery_zip,
                        'scheduled_delivery_date' => $load->scheduled_delivery_date?->format('Y-m-d'),
                        'actual_delivery_date' => $load->actual_delivery_date?->format('Y-m-d'),
                        'delivery_phone' => $load->delivery_phone,
                        'delivery_mobile' => $load->delivery_mobile,
                        'delivery_notes' => $load->delivery_notes,
                        'shipper_name' => $load->shipper_name,
                        'shipper_phone' => $load->shipper_phone,
                        'price' => $load->price,
                        'expenses' => $load->expenses,
                        'broker_fee' => $load->broker_fee,
                        'driver_pay' => $load->driver_pay,
                        'payment_method' => $load->payment_method,
                        'paid_amount' => $load->paid_amount,
                        'paid_method' => $load->paid_method,
                        'reference_number' => $load->reference_number,
                        'receipt_date' => $load->receipt_date?->format('Y-m-d'),
                        'payment_terms' => $load->payment_terms,
                        'payment_notes' => $load->payment_notes,
                        'payment_status' => $load->payment_status,
                        'invoice_number' => $load->invoice_number,
                        'invoice_notes' => $load->invoice_notes,
                        'invoice_date' => $load->invoice_date?->format('Y-m-d'),
                        'driver' => $load->driver,
                        'kanban_status' => $load->kanban_status,
                        'carrier' => $load->carrier ? [
                            'id' => $load->carrier->id,
                            'company_name' => $load->carrier->company_name,
                            'email' => $load->carrier->user->email ?? null,
                        ] : null,
                        'dispatcher_info' => $load->dispatcher ? [
                            'name' => $load->dispatcher,
                        ] : null,
                    ];
                })->toArray(),
                'timestamp' => now()->toIso8601String(),
                'source' => 'dispatcher-control',
            ];

            // Get N8N webhook URL from config (default to placeholder)
            $webhookUrl = config('services.n8n.webhook_url');
            
            // Send to N8N webhook
            $client = new Client();
            $response = $client->post($webhookUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents(), true);
            
            // Process N8N response and update loads
            $updateResult = $this->updateLoadsFromN8NResponse($responseBody, $loadIds);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$loads->count()} load(s) from N8N",
                'data' => [
                    'loads_sent' => $loads->count(),
                    'load_ids' => $loadIds,
                    'n8n_response' => $responseBody,
                    'status_code' => $statusCode,
                    'update_result' => $updateResult,
                ]
            ]);

        } catch (RequestException $e) {
            Log::error('N8N Webhook Error: ' . $e->getMessage(), [
                'load_ids' => $request->input('load_ids'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error sending data to N8N webhook: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Confirm Assigned Loads Error: ' . $e->getMessage(), [
                'load_ids' => $request->input('load_ids'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update loads from N8N response
     * This private method processes the response from N8N and updates all loads
     * 
     * @param array $responseBody The response body from N8N webhook
     * @param array $loadIds Original load IDs that were sent
     * @return array Update result with statistics
     */
    private function updateLoadsFromN8NResponse($responseBody, $loadIds)
    {
        $updatedLoads = [];
        $errors = [];

        // Check if response contains loads array
        if (!isset($responseBody['loads']) || !is_array($responseBody['loads'])) {
            Log::warning('N8N response does not contain loads array', [
                'response_body' => $responseBody
            ]);
            return [
                'updated_loads' => [],
                'errors' => ['Invalid response format: missing loads array'],
                'total_received' => 0,
                'total_updated' => 0,
            ];
        }

        foreach ($responseBody['loads'] as $loadData) {
            try {
                // Validate that load ID exists
                if (!isset($loadData['id'])) {
                    $errors[] = [
                        'load_data' => $loadData,
                        'error' => 'Missing load ID in response',
                    ];
                    continue;
                }

                $load = Load::find($loadData['id']);
                
                if (!$load) {
                    $errors[] = [
                        'load_id' => $loadData['id'],
                        'error' => 'Load not found',
                    ];
                    continue;
                }

                // Prepare update data - update all fields that are present in response
                // Exclude fields that shouldn't be updated (id, relationships, etc.)
                $excludedFields = ['id', 'carrier', 'dispatcher_info', 'kanban_status'];
                $updateData = [];

                foreach ($loadData as $field => $value) {
                    // Skip excluded fields
                    if (in_array($field, $excludedFields)) {
                        continue;
                    }

                    // Handle null values
                    if ($value === null) {
                        $updateData[$field] = null;
                        continue;
                    }

                    // Handle date fields
                    if (in_array($field, [
                        'creation_date',
                        'scheduled_pickup_date',
                        'actual_pickup_date',
                        'scheduled_delivery_date',
                        'actual_delivery_date',
                        'receipt_date',
                        'invoice_date'
                    ])) {
                        try {
                            $updateData[$field] = $value ? \Carbon\Carbon::parse($value) : null;
                        } catch (\Exception $e) {
                            Log::warning("Invalid date format for field {$field}", [
                                'load_id' => $load->id,
                                'value' => $value,
                                'error' => $e->getMessage()
                            ]);
                            // Skip invalid dates
                            continue;
                        }
                    } else {
                        // For other fields, assign as-is
                        $updateData[$field] = $value;
                    }
                }

                // Update the load if there's data to update
                if (!empty($updateData)) {
                    $load->update($updateData);
                    
                    $updatedLoads[] = [
                        'id' => $load->id,
                        'load_id' => $load->load_id,
                        'updated_fields' => array_keys($updateData),
                        'updated_at' => $load->updated_at->toIso8601String(),
                    ];
                } else {
                    $errors[] = [
                        'load_id' => $load->id,
                        'error' => 'No valid fields to update',
                    ];
                }

            } catch (\Exception $e) {
                Log::error('Error updating load from N8N response', [
                    'load_data' => $loadData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $errors[] = [
                    'load_id' => $loadData['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'updated_loads' => $updatedLoads,
            'errors' => $errors,
            'total_received' => count($responseBody['loads']),
            'total_updated' => count($updatedLoads),
        ];
    }
}

