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
use Illuminate\Support\Facades\Auth;

class LoadImportController extends Controller
{
    /**
     * 1. FORMULÁRIO para importar planilha Excel
     */
    public function upload()
    {
        return view('load.upload'); // resources/views/loads/import.blade.php
    }

    /**
     * 2. PROCESSAR importação de planilha
     */
    public function importar(Request $request)
{

    try {
        $request->validate([
            'arquivo' => 'required|file|mimes:xlsx,xls|max:10240',
            'carrier_id' => 'required|exists:carriers,id',
            'dispatcher_id' => 'required|exists:dispatchers,id',
        ]);

        $file = $request->file('arquivo');

        if (!$file || !$file->isValid()) {
            throw new \Exception('Arquivo inválido');
        }

        // Cria diretório na pasta public se não existir
        $uploadDir = public_path('uploads');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Gera nome único para o arquivo
        $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
        $destination = $uploadDir . '/' . $fileName;

        // Move o arquivo para public/uploads
        if (!move_uploaded_file($file->getPathname(), $destination)) {
            throw new \Exception('Falha ao mover arquivo para public/uploads');
        }

        \Log::info('Arquivo salvo em: ' . $destination);

        // Importa usando o caminho completo
        Excel::import(new LoadsImport(
            $request->input('carrier_id'),
            $request->input('dispatcher_id'),
            $request->input('employee_id')
        ), $destination);

        // Remove o arquivo após importação (opcional)
        if (file_exists($destination)) {
            unlink($destination);
        }

        return redirect()->route('loads.index')
                        ->with('success', 'Planilha importada com sucesso!');

    } catch (\Exception $e) {
        \Log::error('Erro na importação: ' . $e->getMessage());
        return redirect()->back()
                ->withErrors(['erro' => 'Erro ao importar: ' . $e->getMessage()]);
    }
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
            'load_id'                 => 'nullable|string|max:255',
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

        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();
        $employees = Employeer::with("user")->get();

        //$userId = Auth::id(); // ou Auth::user()->id

        // Buscar o dispatcher_id relacionado ao user_id autenticado
        //$dispatcher = Dispatcher::where('user_id', $userId)->first();

        //if ($dispatcher) {
            // Buscar as loads do dispatcher logado
            $loads = Load::paginate(50);
        //} else {
            // Se o dispatcher não existir, retornar lista vazia
        //    $loads = collect(); // ou new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        //}

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

        if ($request->filled('dispatcher_id')) {
            $query->where('dispatcher_id', 'like', '%' . $request->dispatcher_id . '%');
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', 'like', '%' . $request->carrier_id . '%');
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', 'like', '%' . $request->employee_id . '%');
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
        $employees = Employeer::with("user")->get();

        //$userId = Auth::id(); // ou Auth::user()->id

        // Buscar o dispatcher_id relacionado ao user_id autenticado
        //$dispatcher = Dispatcher::where('user_id', $userId)->first();

        //if ($dispatcher) {
            // Buscar as loads do dispatcher logado
          //  $query = \App\Models\Load::where('dispatcher_id', $dispatcher->id);

            // Ordena e pagina
            $loads = $query->orderByDesc('id')->paginate(50);

        //} else {
            // Se o dispatcher não existir, retornar lista vazia
          //  $loads = collect(); // ou new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        //}


        return view('load.index', compact('loads', 'dispatchers', 'carriers', 'employees'));
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

        $loads = $query->orderBy('created_at', 'desc')->paginate(50);
        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();

        return view('load.search', compact('loads', 'dispatchers', 'carriers'));
    }




    // Remove um loads
      public function destroy(string $id)
    {
        $load = Load::findOrFail($id);

        // ⭐ NOVO: Antes de deletar, atualizar snapshots das invoices
        $this->updateInvoiceSnapshotsBeforeDelete([$load->load_id]);

        $load->delete();

        return redirect()->route('loads.index')->with('success', 'Load removido com sucesso.');
    }

    public function apagarVarios(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'IDs inválidos.']);
        }

        try {
            // ⭐ NOVO: Buscar load_ids antes de deletar
            $loadIds = Load::whereIn('id', $ids)->pluck('load_id')->toArray();

            // Atualizar snapshots das invoices
            $this->updateInvoiceSnapshotsBeforeDelete($loadIds);

            Load::whereIn('id', $ids)->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao apagar registros.']);
        }
    }

    /**
     * Apagar todos os loads
     */
    public function destroyAll()
    {
        try {
            // ⭐ NOVO: Buscar todos os load_ids antes de deletar
            $loadIds = Load::pluck('load_id')->toArray();

            // Atualizar snapshots das invoices
            $this->updateInvoiceSnapshotsBeforeDelete($loadIds);

            // Excluir primeiro os filhos
            DB::table('containers_loads')->delete();
            Load::query()->delete();

            return response()->json([
                'message' => 'Todas as cargas foram excluídas com sucesso.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao excluir cargas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ⭐ NOVO MÉTODO: Atualizar snapshots das invoices antes de deletar loads
     */
    private function updateInvoiceSnapshotsBeforeDelete(array $loadIds)
    {
        if (empty($loadIds)) {
            return;
        }

        try {
            // Buscar todas as invoices que referenciam estes loads
            $charges = \App\Models\TimeLineCharge::all()->filter(function ($charge) use ($loadIds) {
                $chargeLoadIds = $charge->getLoadIdsArrayAttribute();
                return !empty(array_intersect($chargeLoadIds, $loadIds));
            });

            foreach ($charges as $charge) {
                // Se a invoice ainda não tem snapshot salvo, criar agora
                if (empty($charge->load_details)) {
                    $charge->saveLoadSnapshot();
                } else {
                    // Atualizar snapshot com dados mais recentes dos loads que serão deletados
                    $chargeLoadIds = $charge->getLoadIdsArrayAttribute();
                    $loadsToUpdate = array_intersect($chargeLoadIds, $loadIds);

                    if (!empty($loadsToUpdate)) {
                        // Buscar dados atuais dos loads que serão deletados
                        $currentLoads = Load::whereIn('load_id', $loadsToUpdate)
                            ->select([
                                'id', 'load_id', 'year_make_model', 'dispatcher', 'broker_fee',
                                'driver_pay', 'driver', 'lot_number', 'paid_amount', 'paid_method',
                                'payment_notes', 'payment_status', 'payment_terms', 'payment_method',
                                'invoiced_fee', 'price', 'carrier_id', 'dispatcher_id', 'vin',
                                'pickup_city', 'delivery_city', 'scheduled_pickup_date', 'actual_pickup_date',
                                'scheduled_delivery_date', 'actual_delivery_date', 'created_at'
                            ])
                            ->get()
                            ->toArray();

                        // Atualizar o snapshot existente
                        $existingDetails = $charge->load_details ?? [];

                        // Remover loads antigos e adicionar versões atualizadas
                        $updatedDetails = array_filter($existingDetails, function($item) use ($loadsToUpdate) {
                            return !in_array($item['load_id'], $loadsToUpdate);
                        });

                        // Adicionar versões atualizadas
                        $updatedDetails = array_merge($updatedDetails, $currentLoads);

                        $charge->update(['load_details' => array_values($updatedDetails)]);
                    }
                }
            }

        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar snapshots das invoices: ' . $e->getMessage());
        }
    }


    /**
     * ⭐ NOVO: Método para debug da importação - adicionar no LoadImportController
     */
    public function debugImport(Request $request)
    {
        try {
            $request->validate([
                'arquivo' => 'required|file|mimes:xlsx,xls|max:10240',
            ]);

            $file = $request->file('arquivo');

            if (!$file || !$file->isValid()) {
                throw new \Exception('Arquivo inválido');
            }

            // Salvar temporariamente
            $uploadDir = public_path('uploads');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = 'debug_' . time() . '_' . $file->getClientOriginalName();
            $destination = $uploadDir . '/' . $fileName;

            if (!move_uploaded_file($file->getPathname(), $destination)) {
                throw new \Exception('Falha ao mover arquivo');
            }

            // Analisar arquivo
            $data = Excel::toArray([], $destination);

            if (empty($data) || empty($data[0])) {
                throw new \Exception('Arquivo vazio ou sem dados');
            }

            $headers = $data[0][0]; // Primeira linha (cabeçalhos)
            $sampleRow = isset($data[0][1]) ? $data[0][1] : []; // Segunda linha (exemplo)
            $totalRows = count($data[0]) - 1; // Total de linhas (menos cabeçalho)

            // Remover arquivo temporário
            if (file_exists($destination)) {
                unlink($destination);
            }

            // Analisar mapeamento
            $mappingAnalysis = $this->analyzeMappingPotential($headers, $sampleRow);

            return response()->json([
                'success' => true,
                'analysis' => [
                    'total_rows' => $totalRows,
                    'headers_found' => $headers,
                    'sample_data' => $sampleRow,
                    'mapping_suggestions' => $mappingAnalysis['suggestions'],
                    'unmapped_headers' => $mappingAnalysis['unmapped'],
                    'missing_fields' => $mappingAnalysis['missing']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * ⭐ NOVO: Analisar potencial de mapeamento
     */
    private function analyzeMappingPotential($headers, $sampleRow)
    {
        // Campos que esperamos no banco de dados
        $expectedFields = [
            'load_id' => ['load_id', 'loadid', 'id', 'load', 'numero_carga', 'carga_id'],
            'internal_load_id' => ['internal_load_id', 'internal_id', 'interno', 'id_interno'],
            'year_make_model' => ['year_make_model', 'veiculo', 'vehicle', 'carro', 'make_model'],
            'vin' => ['vin', 'chassi', 'numero_chassi'],
            'lot_number' => ['lot_number', 'lote', 'numero_lote', 'lot'],
            'dispatcher' => ['dispatcher', 'despachante', 'despachador'],
            'trip' => ['trip', 'viagem', 'trip_number'],
            'driver' => ['driver', 'motorista', 'driver_name', 'nome_motorista'],
            'price' => ['price', 'preco', 'valor', 'amount', 'total'],
            'paid_amount' => ['paid_amount', 'valor_pago', 'amount_paid', 'pago'],
            'broker_fee' => ['broker_fee', 'taxa_corretor', 'broker', 'comissao_broker'],
            'driver_pay' => ['driver_pay', 'pagamento_motorista', 'driver_payment'],
            'pickup_city' => ['pickup_city', 'cidade_coleta', 'pickup_cidade', 'coleta_cidade'],
            'pickup_state' => ['pickup_state', 'estado_coleta', 'pickup_estado'],
            'pickup_address' => ['pickup_address', 'endereco_coleta', 'pickup_addr'],
            'delivery_city' => ['delivery_city', 'cidade_entrega', 'delivery_cidade'],
            'delivery_state' => ['delivery_state', 'estado_entrega', 'delivery_estado'],
            'delivery_address' => ['delivery_address', 'endereco_entrega', 'delivery_addr'],
            'scheduled_pickup_date' => ['scheduled_pickup_date', 'data_coleta_programada', 'pickup_date'],
            'actual_pickup_date' => ['actual_pickup_date', 'data_coleta_real', 'pickup_actual'],
            'scheduled_delivery_date' => ['scheduled_delivery_date', 'data_entrega_programada', 'delivery_date'],
            'actual_delivery_date' => ['actual_delivery_date', 'data_entrega_real', 'delivery_actual'],
            'payment_status' => ['payment_status', 'status_pagamento', 'status_pago'],
            'invoice_number' => ['invoice_number', 'numero_fatura', 'invoice', 'fatura'],
        ];

        $suggestions = [];
        $mapped = [];

        // Encontrar correspondências
        foreach ($expectedFields as $dbField => $possibleNames) {
            $bestMatch = null;
            $bestScore = 0;

            foreach ($headers as $index => $header) {
                foreach ($possibleNames as $possible) {
                    $score = $this->calculateSimilarity($header, $possible);

                    if ($score > $bestScore && $score > 0.7) { // 70% de similaridade mínima
                        $bestScore = $score;
                        $bestMatch = [
                            'header' => $header,
                            'index' => $index,
                            'score' => $score,
                            'sample' => $sampleRow[$index] ?? null
                        ];
                    }
                }
            }

            if ($bestMatch) {
                $suggestions[$dbField] = $bestMatch;
                $mapped[] = $bestMatch['index'];
            }
        }

        // Headers não mapeados
        $unmapped = [];
        foreach ($headers as $index => $header) {
            if (!in_array($index, $mapped)) {
                $unmapped[] = [
                    'header' => $header,
                    'index' => $index,
                    'sample' => $sampleRow[$index] ?? null
                ];
            }
        }

        // Campos faltantes
        $missing = [];
        foreach ($expectedFields as $dbField => $possibleNames) {
            if (!isset($suggestions[$dbField])) {
                $missing[] = $dbField;
            }
        }

        return [
            'suggestions' => $suggestions,
            'unmapped' => $unmapped,
            'missing' => $missing
        ];
    }

    /**
     * ⭐ NOVO: Calcular similaridade entre strings
     */
    private function calculateSimilarity($str1, $str2)
    {
        // Normalizar strings
        $str1 = strtolower(trim(str_replace([' ', '-', '_'], '', $str1)));
        $str2 = strtolower(trim(str_replace([' ', '-', '_'], '', $str2)));

        // Calcular similaridade usando different text algorithms
        $levenshtein = 1 - (levenshtein($str1, $str2) / max(strlen($str1), strlen($str2)));
        $similar = 0;
        similar_text($str1, $str2, $similar);
        $similarText = $similar / 100;

        // Verificar se uma string contém a outra
        $contains = 0;
        if (strpos($str1, $str2) !== false || strpos($str2, $str1) !== false) {
            $contains = 0.8;
        }

        // Retornar a maior similaridade
        return max($levenshtein, $similarText, $contains);
    }

    /**
     * ⭐ NOVO: Preview da importação antes de executar
     */
    public function previewImport(Request $request)
    {
        try {
            $request->validate([
                'arquivo' => 'required|file|mimes:xlsx,xls|max:10240',
                'carrier_id' => 'required|exists:carriers,id',
                'dispatcher_id' => 'required|exists:dispatchers,id',
            ]);

            $file = $request->file('arquivo');

            // Salvar temporariamente
            $uploadDir = public_path('uploads');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = 'preview_' . time() . '_' . $file->getClientOriginalName();
            $destination = $uploadDir . '/' . $fileName;

            if (!move_uploaded_file($file->getPathname(), $destination)) {
                throw new \Exception('Falha ao mover arquivo');
            }

            // Usar a classe LoadsImport para processar algumas linhas
            $import = new \App\Imports\LoadsImport(
                $request->input('carrier_id'),
                $request->input('dispatcher_id'),
                $request->input('employee_id')
            );

            // Ler dados da planilha
            $data = Excel::toArray($import, $destination);

            if (empty($data) || empty($data[0])) {
                throw new \Exception('Arquivo vazio ou sem dados');
            }

            $headers = $data[0][0]; // Cabeçalhos
            $preview = [];

            // Processar apenas as primeiras 5 linhas para preview
            $maxRows = min(6, count($data[0])); // 5 linhas + cabeçalho

            for ($i = 1; $i < $maxRows; $i++) {
                $row = array_combine($headers, $data[0][$i]);

                // Simular o processamento sem salvar
                $processedData = $this->simulateRowProcessing($row, $request->input('carrier_id'), $request->input('dispatcher_id'));
                $preview[] = [
                    'original_row' => $row,
                    'processed_data' => $processedData,
                    'missing_fields' => $this->findMissingFields($processedData)
                ];
            }

            // Remover arquivo temporário
            if (file_exists($destination)) {
                unlink($destination);
            }

            return response()->json([
                'success' => true,
                'total_rows' => count($data[0]) - 1,
                'headers' => $headers,
                'preview' => $preview,
                'statistics' => $this->generatePreviewStatistics($preview)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * ⭐ NOVO: Simular processamento de uma linha
     */
    private function simulateRowProcessing($row, $carrierId, $dispatcherId)
    {
        // Replicar a lógica do LoadsImport::getValue()
        $getValue = function($possibleKeys) use ($row) {
            foreach ($possibleKeys as $key) {
                if (isset($row[$key]) && !empty($row[$key])) {
                    return trim($row[$key]);
                }

                foreach ($row as $rowKey => $rowValue) {
                    if (strtolower(str_replace(' ', '_', $rowKey)) === strtolower(str_replace(' ', '_', $key))) {
                        if (!empty($rowValue)) {
                            return trim($rowValue);
                        }
                    }
                }
            }
            return null;
        };

        $getNumeric = function($possibleKeys) use ($getValue) {
            $value = $getValue($possibleKeys);
            if ($value === null || $value === '') return null;

            $cleaned = preg_replace('/[^\d.,\-]/', '', $value);
            $cleaned = str_replace(',', '.', $cleaned);
            return is_numeric($cleaned) ? (float) $cleaned : null;
        };

        // Mapear campos principais
        return [
            'load_id' => $getValue(['load_id', 'loadid', 'id', 'load', 'numero_carga']),
            'internal_load_id' => $getValue(['internal_load_id', 'internal_id', 'interno']),
            'carrier_id' => $carrierId,
            'dispatcher_id' => $dispatcherId,
            'year_make_model' => $getValue(['year_make_model', 'veiculo', 'vehicle', 'carro']),
            'vin' => $getValue(['vin', 'chassi', 'numero_chassi']),
            'driver' => $getValue(['driver', 'motorista', 'driver_name']),
            'price' => $getNumeric(['price', 'preco', 'valor', 'amount', 'total']),
            'pickup_city' => $getValue(['pickup_city', 'cidade_coleta', 'pickup_cidade']),
            'delivery_city' => $getValue(['delivery_city', 'cidade_entrega', 'delivery_cidade']),
            'scheduled_pickup_date' => $getValue(['scheduled_pickup_date', 'data_coleta_programada']),
            'actual_delivery_date' => $getValue(['actual_delivery_date', 'data_entrega_real']),
            'payment_status' => $getValue(['payment_status', 'status_pagamento', 'status_pago']),
        ];
    }

    /**
     * ⭐ NOVO: Encontrar campos faltantes
     */
    private function findMissingFields($processedData)
    {
        $required = ['load_id', 'carrier_id', 'dispatcher_id'];
        $important = ['year_make_model', 'price', 'pickup_city', 'delivery_city'];

        $missing = [];

        foreach ($required as $field) {
            if (empty($processedData[$field])) {
                $missing[] = ['field' => $field, 'level' => 'required'];
            }
        }

        foreach ($important as $field) {
            if (empty($processedData[$field])) {
                $missing[] = ['field' => $field, 'level' => 'important'];
            }
        }

        return $missing;
    }

    /**
     * ⭐ NOVO: Gerar estatísticas do preview
     */
    private function generatePreviewStatistics($preview)
    {
        $stats = [
            'total_previewed' => count($preview),
            'fields_found' => [],
            'missing_required' => 0,
            'missing_important' => 0,
            'completeness_score' => 0
        ];

        $fieldsCount = [];
        $totalFields = 0;
        $populatedFields = 0;

        foreach ($preview as $row) {
            foreach ($row['processed_data'] as $field => $value) {
                if (!isset($fieldsCount[$field])) {
                    $fieldsCount[$field] = 0;
                }

                $totalFields++;

                if (!empty($value)) {
                    $fieldsCount[$field]++;
                    $populatedFields++;
                }
            }

            foreach ($row['missing_fields'] as $missing) {
                if ($missing['level'] === 'required') {
                    $stats['missing_required']++;
                } elseif ($missing['level'] === 'important') {
                    $stats['missing_important']++;
                }
            }
        }

        $stats['fields_found'] = $fieldsCount;
        $stats['completeness_score'] = $totalFields > 0 ? round(($populatedFields / $totalFields) * 100, 2) : 0;

        return $stats;
    }


}
