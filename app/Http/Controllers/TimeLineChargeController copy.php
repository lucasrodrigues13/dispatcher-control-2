<?php

namespace App\Http\Controllers;

use App\Models\TimeLineCharge;
use App\Models\ChargeSetup;
use App\Models\Carrier;
use App\Models\Dispatcher;
use App\Models\Load;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // essa classe aqui
use App\Models\Comission;

class TimeLineChargeController extends Controller
{
    /**
     * Exibir a lista de todos os TimeLineCharges.
     */
    public function index()
    {
        $timeLineCharges = TimeLineCharge::with(['carrier.user', 'dispatcher.user'])->paginate(10); 
        return view('invoice.time_line_charge.index', compact('timeLineCharges'));
    }
    
    public function getChargeSetup($id): JsonResponse
    {
        $setup = ChargeSetup::where('carrier_id', $id)->first();

        if (!$setup) {
            return response()->json([
                'message' => 'Charge setup not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Charge setup found.',
            'data' => [
                'charges_setup_array' => $setup->charges_setup_array ?? [],
                'price' => $setup->price,
                'carrier_id' => $setup->carrier_id,
                'dispatcher_id' => $setup->dispatcher_id,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $carriers = Carrier::with('user')->get();
        $dispatchers = Dispatcher::with('user')->get();

        $loads = collect();
        $totalAmount = 0;

        // Filtros checkbox
        $filters = $request->input('filters', []);
        $carrierId = $request->input('carrier_id', 'all');

        if (count($filters) === 0) {
            return view('invoice.time_line_charge.create', compact(
                'carriers',
                'dispatchers',
                'loads',
                'totalAmount'
            ));
        }

        if ($request->filled('date_start') || $request->filled('date_end')) {
            $query = Load::query();

            // Aplica os filtros de datas de acordo com os campos passados
            foreach ($filters as $field => $val) {
                if ($request->filled('date_start')) {
                    $query->whereDate($field, '>=', $request->date_start);
                }
                if ($request->filled('date_end')) {
                    $query->whereDate($field, '<=', $request->date_end);
                }
            }

            // Aplica filtro por carrier_id, se não for 'all'
            if ($carrierId !== 'all') {
                $query->where('carrier_id', $carrierId);
            }

            $loads = $query->get();

            $amountType = $request->input('amount_type', 'price');

            $totalAmount = $amountType === 'paid_amount'
                ? $loads->sum('paid_amount')
                : $loads->sum('price');
        }

        return view('invoice.time_line_charge.create', compact(
            'carriers',
            'dispatchers',
            'loads',
            'totalAmount'
        ));
    }

    /**
     * Armazenar um novo TimeLineCharge.
     */
    public function store(Request $request): JsonResponse
    {
        // return response()->json($request->input('amount_type'));
        // Extrai campos marcados
        $arrayTypeDates = array_keys($request->input('filters', []));
        $loadIds = $request->input('load_ids', []); // array de IDs

        // return response()->json($loadIds);

        // Valida carrier_id
        if (!is_numeric($request->carrier_id)) {
            return response()->json([
                'message' => 'Carrier ID inválido.',
            ], 422);
        }

        // 🔍 Verifica se algum dos load_ids já está cadastrado para este carrier
        $existing = TimeLineCharge::where('carrier_id', $request->carrier_id)
            ->get()
            ->filter(function ($charge) use ($loadIds) {
                $existingLoadIds = is_string($charge->load_ids)
                    ? json_decode($charge->load_ids, true)
                    : $charge->load_ids;

                return !empty(array_intersect($existingLoadIds ?? [], $loadIds));
            });

            if ($existing->isNotEmpty()) {
                return response()->json([
                    'message' => 'Esta Invoice já foi salva.',
                ], 409); // 409 = conflito
            }

            // return response()->json($loadIds);

            // Criação do TimeLineCharge
            $timeLineCharge = TimeLineCharge::create([
                'invoice_id'       => null,
                'price'            => $request->input('total_amount'), // valor numérico
                'status_payment'   => "Invoiced",
                'carrier_id'       => $request->input('carrier_id'),
                'dispatcher_id'    => $request->input('dispatcher_id'),
                'date_start'       => $request->input('date_start'),
                'date_end'         => $request->input('date_end'),
                'amount_type'      => $request->input('amount_type'),
                'array_type_dates' => json_encode($arrayTypeDates),
                'load_ids'         => json_encode($loadIds),
        ]);

        // Atualiza invoice_id baseado no ID criado
        $invoiceId = date('Y') . '-' . date('W') . '-' . $timeLineCharge->id;
        $timeLineCharge->update(['invoice_id' => $invoiceId]);

        return response()->json([
            'message' => 'Time Line Charge created successfully.',
            'id'      => $timeLineCharge->id,
            'invoice' => $invoiceId,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        // Obtém o registro
        $charge = TimeLineCharge::findOrFail($id);

        // Obtém os dados auxiliares
        $carriers = Carrier::with('user')->get();
        $dispatchers = Dispatcher::with('user')->get();

        // Inicialização
        $loads = collect();
        $totalAmount = 0;

        // Obtém filtros
        $dateStart = $charge->date_start;
        $dateEnd   = $charge->date_end;
        $amountType = $charge->amount_type;
        $filters = json_decode($charge->array_type_dates, true) ?? [];

        // Aplica os filtros
        if ($dateStart || $dateEnd) {
            $query = Load::query();

            foreach ($filters as $field) {
                if ($dateStart) {
                    $query->whereDate($field, '>=', $dateStart);
                }
                if ($dateEnd) {
                    $query->whereDate($field, '<=', $dateEnd);
                }
            }

            $loads = $query->get();

            $totalAmount = $amountType === 'paid_amount'
                ? $loads->sum('paid_amount')
                : $loads->sum('price');
        }

        $TimeLineCharge = TimeLineCharge::find($id);

        if (!$TimeLineCharge) {
            return response()->json(['error' => 'Invoice não encontrada.'], 404);
        }

        $amountType = $TimeLineCharge->amount_type;
        $loadIds = json_decode($TimeLineCharge->load_ids, true);

        if (!in_array($amountType, ['price', 'paid_amount'])) {
            return response()->json(['error' => 'Tipo de valor inválido.'], 400);
        }

        if (!is_array($loadIds)) {
            return response()->json(['error' => 'Load IDs inválidos.'], 400);
        }

        // Buscar os loads
        $loads = Load::whereIn('load_id', $loadIds)
                    ->select('load_id', 'employee_id', $amountType)
                    ->get();

        // Total
        $totalAmount = $loads->sum($amountType);

        // Buscar comissões
        $employeeIds = $loads->pluck('employee_id')->filter()->unique()->values();
        $comissions = Comission::whereIn('employee_id', $employeeIds)
                        ->pluck('value', 'employee_id'); // ex: [5 => 10, 3 => 5]

        // Enriquecer cada carga
        $loadsWithComission = $loads->map(function ($load) use ($amountType, $comissions) {
            $comissionPercent = $comissions[$load->employee_id] ?? 0;
            $amountValue = $load->{$amountType} ?? 0;
            $amountComission = ($comissionPercent / 100) * $amountValue;

            return [
                'load_id' => $load->load_id,
                'employee_id' => $load->employee_id,
                $amountType => $amountValue,
                'comission_value' => $comissionPercent,
                'amount_comission' => round($amountComission, 2),
            ];
        });

        $totalComission = $loadsWithComission->sum('amount_comission');

        return view('invoice.time_line_charge.show', compact(
            'charge',
            'carriers',
            'dispatchers',
            'loadsWithComission',
            'totalAmount',
            'totalComission',
            'amountType',
            'filters',
            'loads',
        ));
    }

    public function getLoadsFromInvoice($id)
    {
        $TimeLineCharge = TimeLineCharge::find($id);

        if (!$TimeLineCharge) {
            return response()->json(['error' => 'Invoice não encontrada.'], 404);
        }

        $amountType = $TimeLineCharge->amount_type;
        $loadIds = json_decode($TimeLineCharge->load_ids, true);

        if (!in_array($amountType, ['price', 'paid_amount'])) {
            return response()->json(['error' => 'Tipo de valor inválido.'], 400);
        }

        if (!is_array($loadIds)) {
            return response()->json(['error' => 'Load IDs inválidos.'], 400);
        }

        // Buscar os loads
        $loads = Load::whereIn('load_id', $loadIds)
                    ->select('load_id', 'employee_id', $amountType)
                    ->get();

        // Total
        $totalAmount = $loads->sum($amountType);

        // Buscar comissões
        $employeeIds = $loads->pluck('employee_id')->filter()->unique()->values();
        $comissions = Comission::whereIn('employee_id', $employeeIds)
                        ->pluck('value', 'employee_id'); // ex: [5 => 10, 3 => 5]

        // Enriquecer cada carga
        $loadsWithComission = $loads->map(function ($load) use ($amountType, $comissions) {
            $comissionPercent = $comissions[$load->employee_id] ?? 0;
            $amountValue = $load->{$amountType} ?? 0;
            $amountComission = ($comissionPercent / 100) * $amountValue;

            return [
                'load_id' => $load->load_id,
                'employee_id' => $load->employee_id,
                $amountType => $amountValue,
                'comission_value' => $comissionPercent,
                'amount_comission' => round($amountComission, 2),
            ];
        });

        $totalComission = $loadsWithComission->sum('amount_comission');

        return response()->json([
            'invoice_id' => $TimeLineCharge->id,
            'amount_type' => $amountType,
            'total_amount' => $totalAmount,
            'total_comission' => round($totalComission, 2),
            'loads' => $loadsWithComission,
        ]);
    }

    // public function edit($id)
    // {
    //     $carriers = Carrier::with('user')->get();
    //     $dispatchers = Dispatcher::with('user')->get();
    //     $timeLineCharge = TimeLineCharge::findOrFail($id);

    //     // Decodifica os campos JSON
    //     $loadIds = json_decode($timeLineCharge->load_ids, true);
    //     $arrayTypeDates = json_decode($timeLineCharge->array_type_dates, true);

    //     $filters = Load::whereIn('load_id', $loadIds)->get();
    //     $totalAmount = $timeLineCharge->price;

    //     return view('invoice.time_line_charge.edit', compact(
    //         'carriers', 'dispatchers', 'timeLineCharge',
    //         'filters', 'totalAmount', 'arrayTypeDates'
    //     ));
    // }

    public function update(Request $request, $id): JsonResponse
    {
        $timeLineCharge = TimeLineCharge::findOrFail($id);

        if ($request->has('status_payment')) {
            $validStatuses = ['Invoiced', 'paid', 'unpaid'];

            if (!in_array($request->status_payment, $validStatuses)) {
                return response()->json(['message' => 'Status inválido.'], 422);
            }

            $timeLineCharge->update([
                'status_payment' => $request->status_payment
            ]);

            return response()->json([
                'message' => 'Status atualizado com sucesso.',
                'status' => $request->status_payment
            ]);
        }

        return response()->json(['message' => 'Nada para atualizar.'], 400);
    }

    /**
     * Excluir um TimeLineCharge.
     */
    public function destroy($id)
    {
        $charge = TimeLineCharge::findOrFail($id);
        $charge->delete();

        return redirect()->route('time_line_charges.index')
            ->with('success', 'Registro excluído com sucesso.');
    }
}
