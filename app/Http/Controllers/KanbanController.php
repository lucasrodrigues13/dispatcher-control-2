<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Container;
use App\Models\Dispatcher;
use App\Models\Employee;
use App\Models\Load;
use Illuminate\Http\Request;

class KanbanController extends Controller
{




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
 * Obter configurações de campos do card para o usuário
 */
public function getCardFieldsConfig()
{
    $userId = auth()->id();
    $config = \App\Models\UserCardConfig::getCardFieldsConfig($userId);

    return response()->json($config);
}

/**
 * Salvar configurações de campos do card
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
 * Atualizar método kanbaFilter para incluir novos filtros
 */
public function kanbaFilter(Request $request)
{
    $query = Load::query();

    if ($request->filled('load_id')) {
        $query->where('load_id', 'like', '%' . $request->load_id . '%');
    }

    if ($request->filled('internal_load_id')) {
        $query->where('internal_load_id', 'like', '%' . $request->internal_load_id . '%');
    }

    if ($request->filled('dispatcher_id')) {
        $query->where('dispatcher_id', $request->dispatcher_id);
    }

    if ($request->filled('carrier_id')) {
        $query->where('carrier_id', $request->carrier_id);
    }

    if ($request->filled('vin')) {
        $query->where('vin', 'like', '%' . $request->vin . '%');
    }

    if ($request->filled('pickup_city')) {
        $query->where('pickup_city', 'like', '%' . $request->pickup_city . '%');
    }

    if ($request->filled('delivery_city')) {
        $query->where('delivery_city', 'like', '%' . $request->delivery_city . '%');
    }

    if ($request->filled('scheduled_pickup_date')) {
        $query->whereDate('scheduled_pickup_date', $request->scheduled_pickup_date);
    }

    if ($request->filled('driver')) {
        $query->where('driver', 'like', '%' . $request->driver . '%');
    }

    $dispatchers = Dispatcher::with('user')
        ->where('user_id', auth()->id())
        ->first();
    $carriers = Carrier::with("user")->get();
    $employees = Employee::with("user")->get();
    $loads = $query->orderByDesc('id')->paginate(50);

    $containers = Container::with(['containerLoads.loadItem'])->get();

    return view('load.kanbaMode', compact('loads', 'dispatchers', 'carriers', 'containers', 'employees'));
}

/**
 * Atualizar método kanbaMode para incluir carriers
 */
public function kanbaMode()
{
    $dispatchers = Dispatcher::with('user')
        ->where('user_id', auth()->id())
        ->first();
    $carriers = Carrier::with("user")->get();
    $employees = Employee::with("user")->get();
    $loads = Load::where("status_move", "no_moved")->paginate(10);

    $containers = Container::with(['containerLoads.loadItem'])->get();

    return view('load.kanbaMode', compact('loads', 'dispatchers', 'carriers', 'containers', 'employees'));
}

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

}
