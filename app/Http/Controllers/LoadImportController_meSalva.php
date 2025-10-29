<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Imports\LoadsImport;
use App\Models\Load;
use App\Models\Dispatcher;
use App\Models\Carrier;
use App\Models\Container;
use App\Models\Employeer;
use App\Services\BillingService;
use Maatwebsite\Excel\Facades\Excel;

class LoadImportController extends Controller
{
    /**
     * 1. FORMULÁRIO para importar planilha Excel
     */
    public function upload()
    {
        return 1;

        return view('load.upload'); // resources/views/loads/import.blade.php
    }

    /**
     * 2. PROCESSAR importação de planilha
     */
    public function importar(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:xlsx,xls',
            'carrier_id' => 'required|exists:carriers,id',
            'dispatcher_id' => 'required|exists:dispatchers,id',
        ]);

        // Passa os IDs para a classe de importação
        // Excel::import(new LoadsImport(
        //     $request->input('carrier_id'),
        //     $request->input('dispatcher_id')
        // ), $request->file('arquivo'));

        Excel::import(new LoadsImport(
            $request->input('carrier_id'),
            $request->input('dispatcher_id'),
            $request->input('employee_id') // novo parâmetro
        ), $request->file('arquivo'));

        return redirect()->route('loads.index')
                        ->with('success', 'Planilha importada com sucesso!');
    }

    /**
     * 3. FORMULÁRIO para cadastro/edição manual de um Load
     */
    public function create()
    {
        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();
        $loads = Load::all();
        return view('load.create', compact('loads', 'dispatchers', 'carriers')); // resources/views/loads/index.blade.php
    }

    /**
     * 4. PROCESSAR cadastro/edição manual de um Load
     */
    public function store(Request $request)
    {
        // Validação básica (ajuste conforme necessidade)
        $request->validate([
            // 'load_id'                 => 'required|integer',
            'internal_load_id'        => 'nullable|string|max:255',
            'creation_date'           => 'nullable|date_format:Y-m-d',
            'dispatcher'              => 'nullable|string|max:255',
            'trip'                    => 'nullable|string|max:255',
            'year_make_model'         => 'nullable|string|max:255',
            'vin'                     => 'nullable|string|max:255',
            'lot_number'              => 'nullable|string|max:255',
            'has_terminal'            => 'nullable|integer',
            'dispatched_to_carrier'   => 'nullable|string|max:255',
            'pickup_name'             => 'nullable|string|max:255',
            'pickup_address'          => 'nullable|string|max:255',
            'pickup_city'             => 'nullable|string|max:255',
            'pickup_state'            => 'nullable|string|max:255',
            'pickup_zip'              => 'nullable|string|max:50',
            'scheduled_pickup_date'   => 'nullable|date_format:Y-m-d',
            'pickup_phone'            => 'nullable|string|max:50',
            'pickup_mobile'           => 'nullable|string|max:50',
            'actual_pickup_date'      => 'nullable|date_format:Y-m-d',
            'buyer_number'            => 'nullable|integer',
            'pickup_notes'            => 'nullable|string',
            'delivery_name'           => 'nullable|string|max:255',
            'delivery_address'        => 'nullable|string|max:255',
            'delivery_city'           => 'nullable|string|max:255',
            'delivery_state'          => 'nullable|string|max:255',
            'delivery_zip'            => 'nullable|string|max:50',
            'scheduled_delivery_date' => 'nullable|date_format:Y-m-d',
            'actual_delivery_date'    => 'nullable|date_format:Y-m-d',
            'delivery_phone'          => 'nullable|string|max:50',
            'delivery_mobile'         => 'nullable|string|max:50',
            'delivery_notes'          => 'nullable|string',
            'shipper_name'            => 'nullable|string|max:255',
            'shipper_phone'           => 'nullable|string|max:50',
            'price'                   => 'nullable|numeric',
            'expenses'                => 'nullable|numeric',
            'broker_fee'              => 'nullable|numeric',
            'driver_pay'              => 'nullable|numeric',
            'payment_method'          => 'nullable|string|max:255',
            'paid_amount'             => 'nullable|numeric',
            'paid_method'             => 'nullable|string|max:255',
            'reference_number'        => 'nullable|string|max:255',
            'receipt_date'            => 'nullable|date_format:Y-m-d',
            'payment_terms'           => 'nullable|string|max:255',
            'payment_notes'           => 'nullable|string',
            'payment_status'          => 'nullable|string|max:255',
            'invoice_number'          => 'nullable|string|max:255',
            'invoice_notes'           => 'nullable|string',
            'invoice_date'            => 'nullable|date_format:Y-m-d',
            'driver'                  => 'nullable|string|max:255',
        ]);

        // Monta array de atributos (mesmo formato usado na importação)
        $data = [
            'dispatcher_id'           => $request->input('dispatcher_id'),
            'carrier_id'              => $request->input('carrier_id'),
            'internal_load_id'        => $request->input('internal_load_id'),
            'creation_date'           => $request->input('creation_date'),
            'dispatcher'              => $request->input('dispatcher'),
            'trip'                    => $request->input('trip'),
            'year_make_model'         => $request->input('year_make_model'),
            'vin'                     => $request->input('vin'),
            'lot_number'              => $request->input('lot_number'),
            'has_terminal'            => $request->input('has_terminal'),
            'dispatched_to_carrier'   => $request->input('dispatched_to_carrier'),
            'pickup_name'             => $request->input('pickup_name'),
            'pickup_address'          => $request->input('pickup_address'),
            'pickup_city'             => $request->input('pickup_city'),
            'pickup_state'            => $request->input('pickup_state'),
            'pickup_zip'              => $request->input('pickup_zip'),
            'scheduled_pickup_date'   => $request->input('scheduled_pickup_date'),
            'pickup_phone'            => $request->input('pickup_phone'),
            'pickup_mobile'           => $request->input('pickup_mobile'),
            'actual_pickup_date'      => $request->input('actual_pickup_date'),
            'buyer_number'            => $request->input('buyer_number'),
            'pickup_notes'            => $request->input('pickup_notes'),
            'delivery_name'           => $request->input('delivery_name'),
            'delivery_address'        => $request->input('delivery_address'),
            'delivery_city'           => $request->input('delivery_city'),
            'delivery_state'          => $request->input('delivery_state'),
            'delivery_zip'            => $request->input('delivery_zip'),
            'scheduled_delivery_date' => $request->input('scheduled_delivery_date'),
            'actual_delivery_date'    => $request->input('actual_delivery_date'),
            'delivery_phone'          => $request->input('delivery_phone'),
            'delivery_mobile'         => $request->input('delivery_mobile'),
            'delivery_notes'          => $request->input('delivery_notes'),
            'shipper_name'            => $request->input('shipper_name'),
            'shipper_phone'           => $request->input('shipper_phone'),
            'price'                   => $request->input('price'),
            'expenses'                => $request->input('expenses'),
            'broker_fee'              => $request->input('broker_fee'),
            'driver_pay'              => $request->input('driver_pay'),
            'payment_method'          => $request->input('payment_method'),
            'paid_amount'             => $request->input('paid_amount'),
            'paid_method'             => $request->input('paid_method'),
            'reference_number'        => $request->input('reference_number'),
            'receipt_date'            => $request->input('receipt_date'),
            'payment_terms'           => $request->input('payment_terms'),
            'payment_notes'           => $request->input('payment_notes'),
            'payment_status'          => $request->input('payment_status'),
            'invoice_number'          => $request->input('invoice_number'),
            'invoice_notes'           => $request->input('invoice_notes'),
            'invoice_date'            => $request->input('invoice_date'),
            'driver'                  => $request->input('driver'),
        ];


        // Verificar limites antes de criar
        $billingService = app(BillingService::class);
        $usageCheck = $billingService->checkUsageLimits(auth()->user());

        if (!$usageCheck['allowed']) {
            return redirect()->route('subscription.plans')
                        ->with('error', 'You have reached your plan limits. Please upgrade to continue.')
                        ->with('usage_info', $usageCheck);
        }

        // Update or Create em Load: se existir, atualiza; caso contrário, cria novo
        Load::updateOrCreate(
            ['load_id' => $request->input('load_id')],
            $data
        );

        // Tracking de uso
        app(BillingService::class)->trackUsage(auth()->user(), 'load');

        // Rastrear uso
        $billingService->trackUsage(auth()->user(), 'load');

        return redirect()->route('loads.index')
                         ->with('success', 'Registro salvo/atualizado com sucesso!');
    }

    public function show($id)
    {
        $load = Load::findOrFail($id);

        // Se você quiser retornar como JSON:
        return response()->json($load);
    }

    public function edit($id)
    {
        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();
        $load = Load::findOrFail($id);
        return view('load.edit', compact('load', 'dispatchers', 'carriers'));
    }

    public function update(Request $request, $id)
    {
        $load = Load::findOrFail($id);

        // Validação básica (ajuste conforme necessidade)
        $request->validate([
            'load_id'                 => 'required|integer|unique:loads,load_id,' . $load->id,
            'internal_load_id'        => 'nullable|string|max:255',
            'creation_date'           => 'nullable|date_format:Y-m-d',
            'dispatcher'              => 'nullable|string|max:255',
            'trip'                    => 'nullable|string|max:255',
            'year_make_model'         => 'nullable|string|max:255',
            'vin'                     => 'nullable|string|max:255',
            'lot_number'              => 'nullable|string|max:255',
            'has_terminal'            => 'nullable|integer',
            'dispatched_to_carrier'   => 'nullable|string|max:255',
            'pickup_name'             => 'nullable|string|max:255',
            'pickup_address'          => 'nullable|string|max:255',
            'pickup_city'             => 'nullable|string|max:255',
            'pickup_state'            => 'nullable|string|max:255',
            'pickup_zip'              => 'nullable|string|max:50',
            'scheduled_pickup_date'   => 'nullable|date_format:Y-m-d',
            'pickup_phone'            => 'nullable|string|max:50',
            'pickup_mobile'           => 'nullable|string|max:50',
            'actual_pickup_date'      => 'nullable|date_format:Y-m-d',
            'buyer_number'            => 'nullable|integer',
            'pickup_notes'            => 'nullable|string',
            'delivery_name'           => 'nullable|string|max:255',
            'delivery_address'        => 'nullable|string|max:255',
            'delivery_city'           => 'nullable|string|max:255',
            'delivery_state'          => 'nullable|string|max:255',
            'delivery_zip'            => 'nullable|string|max:50',
            'scheduled_delivery_date' => 'nullable|date_format:Y-m-d',
            'actual_delivery_date'    => 'nullable|date_format:Y-m-d',
            'delivery_phone'          => 'nullable|string|max:50',
            'delivery_mobile'         => 'nullable|string|max:50',
            'delivery_notes'          => 'nullable|string',
            'shipper_name'            => 'nullable|string|max:255',
            'shipper_phone'           => 'nullable|string|max:50',
            'price'                   => 'nullable|numeric',
            'expenses'                => 'nullable|numeric',
            'broker_fee'              => 'nullable|numeric',
            'driver_pay'              => 'nullable|numeric',
            'payment_method'          => 'nullable|string|max:255',
            'paid_amount'             => 'nullable|numeric',
            'paid_method'             => 'nullable|string|max:255',
            'reference_number'        => 'nullable|string|max:255',
            'receipt_date'            => 'nullable|date_format:Y-m-d',
            'payment_terms'           => 'nullable|string|max:255',
            'payment_notes'           => 'nullable|string',
            'payment_status'          => 'nullable|string|max:255',
            'invoice_number'          => 'nullable|string|max:255',
            'invoice_notes'           => 'nullable|string',
            'invoice_date'            => 'nullable|date_format:Y-m-d',
            'driver'                  => 'nullable|string|max:255',
        ]);

        // Monta array de atributos atualizados
        $data = [
            'dispatcher_id'           => $request->input('dispatcher_id'),
            'carrier_id'              => $request->input('carrier_id'),
            'load_id'                 => $request->input('load_id'),
            'internal_load_id'        => $request->input('internal_load_id'),
            'creation_date'           => $request->input('creation_date'),
            'dispatcher'              => $request->input('dispatcher'),
            'trip'                    => $request->input('trip'),
            'year_make_model'         => $request->input('year_make_model'),
            'vin'                     => $request->input('vin'),
            'lot_number'              => $request->input('lot_number'),
            'has_terminal'            => $request->input('has_terminal'),
            'dispatched_to_carrier'   => $request->input('dispatched_to_carrier'),
            'pickup_name'             => $request->input('pickup_name'),
            'pickup_address'          => $request->input('pickup_address'),
            'pickup_city'             => $request->input('pickup_city'),
            'pickup_state'            => $request->input('pickup_state'),
            'pickup_zip'              => $request->input('pickup_zip'),
            'scheduled_pickup_date'   => $request->input('scheduled_pickup_date'),
            'pickup_phone'            => $request->input('pickup_phone'),
            'pickup_mobile'           => $request->input('pickup_mobile'),
            'actual_pickup_date'      => $request->input('actual_pickup_date'),
            'buyer_number'            => $request->input('buyer_number'),
            'pickup_notes'            => $request->input('pickup_notes'),
            'delivery_name'           => $request->input('delivery_name'),
            'delivery_address'        => $request->input('delivery_address'),
            'delivery_city'           => $request->input('delivery_city'),
            'delivery_state'          => $request->input('delivery_state'),
            'delivery_zip'            => $request->input('delivery_zip'),
            'scheduled_delivery_date' => $request->input('scheduled_delivery_date'),
            'actual_delivery_date'    => $request->input('actual_delivery_date'),
            'delivery_phone'          => $request->input('delivery_phone'),
            'delivery_mobile'         => $request->input('delivery_mobile'),
            'delivery_notes'          => $request->input('delivery_notes'),
            'shipper_name'            => $request->input('shipper_name'),
            'shipper_phone'           => $request->input('shipper_phone'),
            'price'                   => $request->input('price'),
            'expenses'                => $request->input('expenses'),
            'broker_fee'              => $request->input('broker_fee'),
            'driver_pay'              => $request->input('driver_pay'),
            'payment_method'          => $request->input('payment_method'),
            'paid_amount'             => $request->input('paid_amount'),
            'paid_method'             => $request->input('paid_method'),
            'reference_number'        => $request->input('reference_number'),
            'receipt_date'            => $request->input('receipt_date'),
            'payment_terms'           => $request->input('payment_terms'),
            'payment_notes'           => $request->input('payment_notes'),
            'payment_status'          => $request->input('payment_status'),
            'invoice_number'          => $request->input('invoice_number'),
            'invoice_notes'           => $request->input('invoice_notes'),
            'invoice_date'            => $request->input('invoice_date'),
            'driver'                  => $request->input('driver'),
        ];

        // Atualiza o modelo com os novos dados
        $load->fill($data);
        $load->save();

        return redirect()->route('loads.index')
                         ->with('success', 'Load atualizado com sucesso!');
    }

    public function updateEmployee(Request $request, Load $load)
    {
        $request->validate([
            'employee_id' => 'nullable|exists:employees,id'
        ]);

        $load->employee_id = $request->employee_id;
        $load->save();

        return response()->json(['success' => true]);
    }

    /**
     * 5. LISTAR todos os Loads em tabela
     */
    public function index()
    {
        return 1;
        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();
        $employees = Employeer::with("user")->get();
        $loads = Load::paginate(10);
        return view('load.index', compact('loads', 'dispatchers', 'carriers', 'employees')); // resources/views/loads/index.blade.php
    }

    public function filter(Request $request)
    {
        $query = Load::query();

        if ($request->filled('load_id')) {
            $query->where('load_id', 'like', '%' . $request->load_id . '%');
        }

        if ($request->filled('internal_load_id')) {
            $query->where('internal_load_id', 'like', '%' . $request->internal_load_id . '%');
        }

        if ($request->filled('dispatcher')) {
            $query->where('dispatcher', 'like', '%' . $request->dispatcher . '%');
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

        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();
        $loads = $query->orderByDesc('id')->paginate(20);

        return view('load.index', compact('loads', 'dispatchers', 'carriers'));
    }

    public function search(Request $request)
    {
        $query = Load::query();

        // Filtro por campo específico
        if ($request->filled('search') && $request->filled('search_field')) {
            $query->where($request->search_field, 'LIKE', '%' . $request->search . '%');
        }

        // Adicione mais filtros específicos conforme necessário
        // Exemplo:
        // if ($request->filled('dispatcher')) {
        //     $query->where('dispatcher', $request->dispatcher);
        // }

        $loads = $query->orderBy('created_at', 'desc')->paginate(20);
        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();

        return view('load.search', compact('loads', 'dispatchers', 'carriers'));
    }

    public function kanbaMode()
    {
        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();
        $loads = Load::where("status_move", "no_moved")->paginate(10);

        // $containers = Container::where('user_id', auth()->id())->get();

        // Pegando os containers com os loads associados via tabela pivô
        // $containers = Container::with('loads')->get();
        $containers = Container::with(['containerLoads.loadItem'])->get();


        return view('load.kanbaMode', compact('loads', 'dispatchers', 'carriers', 'containers'));
    }

    public function kanbaFilter(Request $request)
    {
        $query = Load::query();

        if ($request->filled('load_id')) {
            $query->where('load_id', 'like', '%' . $request->load_id . '%');
        }

        if ($request->filled('internal_load_id')) {
            $query->where('internal_load_id', 'like', '%' . $request->internal_load_id . '%');
        }

        if ($request->filled('dispatcher')) {
            $query->where('dispatcher', 'like', '%' . $request->dispatcher . '%');
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

        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();
        $loads = $query->orderByDesc('id')->paginate(20);

        $containers = Container::with(['containerLoads.loadItem'])->get();

        return view('load.kanbaMode', compact('loads', 'dispatchers', 'carriers', 'containers'));
    }

    public function kanbaSearch(Request $request)
    {
        $query = Load::query();

        // Filtro por campo específico
        if ($request->filled('search') && $request->filled('search_field')) {
            $query->where($request->search_field, 'LIKE', '%' . $request->search . '%');
        }

        // Adicione mais filtros específicos conforme necessário
        // Exemplo:
        // if ($request->filled('dispatcher')) {
        //     $query->where('dispatcher', $request->dispatcher);
        // }

        $loads = $query->orderBy('created_at', 'desc')->paginate(20);
        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();

        $containers = Container::with(['containerLoads.loadItem'])->get();

        return view('load.kanbaSearch', compact('loads', 'dispatchers', 'carriers', 'containers'));
    }

    // Remove um loads
    public function destroy(string $id)
    {
        $load = Load::findOrFail($id);
        $load->delete();

        return redirect()->route('loads.index')->with('success', 'load removido com sucesso.');
    }

    public function apagarVarios(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'IDs inválidos.']);
        }

        try {
            Load::whereIn('id', $ids)->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao apagar registros.']);
        }
    }

    public function destroyAll()
    {
        // Excluir primeiro os filhos, se for necessário
        DB::table('containers_loads')->delete();

        // Depois exclui todos os loads
        \App\Models\Load::query()->delete();

        return response()->json([
            'message' => 'Todas as cargas foram excluídas com sucesso.'
        ]);
    }


}
