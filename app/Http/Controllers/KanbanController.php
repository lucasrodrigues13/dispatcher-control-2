<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Container;
use App\Models\Dispatcher;
use App\Models\Employee;
use App\Models\Load;
use App\Services\LoadService;
use Illuminate\Http\Request;

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
        $loads = $query->orderByDesc('id')->get();

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
     * Sincronizar kanban_status de todos os loads que estão em 'new'
     * Útil para recalcular status após edições manuais
     */
    public function syncNewLoadsKanbanStatus()
    {
        try {
            // Buscar apenas loads com status 'new'
            $loads = Load::where('kanban_status', 'new')->get();
            
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
                'message' => "Sincronização concluída! {$updated} load(s) atualizado(s).",
                'data' => [
                    'total_processed' => count($loads),
                    'updated' => $updated,
                    'stats' => $stats
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar status: ' . $e->getMessage()
            ], 500);
        }
    }
}

