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
use App\Models\Deal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TimeLineChargeController extends Controller
{
    /**
     * Exibir a lista de todos os TimeLineCharges.
     */
    public function index()
    {
        $timeLineCharges = TimeLineCharge::with(['carrier.user', 'dispatcher.user'])
                                    ->orderBy('created_at', 'desc')
                                    ->paginate(10);

        // Para cada invoice, calcula o total dos loads
        // Isso pode ser otimizado se necessário, mas para 10 registros por página está ok
        $timeLineCharges->getCollection()->transform(function($charge) {
            $charge->calculated_total = $charge->getTotalLoadsAmount();
            return $charge;
        });

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
    // Buscar dispatcher do usuário logado
    $dispatcher = Dispatcher::with('user')
        ->where('user_id', auth()->id())
        ->first();

    // Carregar carriers e dispatchers filtrados pelo dispatcher do usuário logado
    if (!$dispatcher) {
        $carriers = collect();
        $dispatchers = collect();
    } else {
        // Criar coleção com o dispatcher encontrado
        $dispatchers = collect([$dispatcher]);
        
        // Filtra os carriers pelo dispatcher_id
        $carriers = Carrier::with('user')
            ->where('dispatcher_id', $dispatcher->id)
            ->get();
    }

    $totalAmount = 0;
    $loads = collect(); // Inicialmente vazio

    // ⭐ CORRIGIDO: Verifica se filtros foram aplicados - PERMITE "all" como carrier válido
    $hasFilters = $request->filled('carrier_id') &&
                  $request->filled(['date_start', 'date_end']);

    if ($hasFilters) {
        $query = Load::query();

        // Carrega os relacionamentos necessários para exibir carrier e dispatcher
        $query->with(['carrier.user', 'dispatcher.user']);

        // ⭐ CORRIGIDO: Filtro por carrier - só aplica filtro se não for "all"
        if ($request->filled('carrier_id') && $request->carrier_id !== 'all') {
            $query->where('carrier_id', $request->carrier_id);
        }
        // Se carrier_id for "all", não aplica nenhum filtro de carrier (busca todos)

        // ⭐ FILTROS DE DATAS DINÂMICOS - TOTALMENTE REFORMULADO
        $dateStart = $request->date_start;
        $dateEnd = $request->date_end;

        if ($dateStart && $dateEnd) {
            // Lista de colunas de data válidas no modelo Load
            $validDateColumns = [
                'actual_delivery_date',
                'actual_pickup_date',
                'creation_date',
                'invoice_date',
                'receipt_date',
                'scheduled_pickup_date',
                'scheduled_delivery_date'
            ];

            // Verifica se há filtros específicos selecionados
            $selectedFilters = [];
            if ($request->has('filters') && is_array($request->filters)) {
                foreach ($request->filters as $column => $value) {
                    if ($value === "1" && in_array($column, $validDateColumns)) {
                        $selectedFilters[] = $column;
                    }
                }
            }

            // ⭐ NOVA LÓGICA: Aplicar filtros dinamicamente
            if (!empty($selectedFilters)) {
                // Se há filtros específicos selecionados, usar apenas esses
                $query->where(function($q) use ($selectedFilters, $dateStart, $dateEnd) {
                    foreach ($selectedFilters as $index => $column) {
                        if ($index === 0) {
                            // Primeira condição usa where
                            $q->whereBetween($column, [$dateStart, $dateEnd]);
                        } else {
                            // Demais condições usam orWhereBetween
                            $q->orWhereBetween($column, [$dateStart, $dateEnd]);
                        }
                    }
                });

                // Log dos filtros aplicados
                Log::info('Filtros específicos aplicados:', [
                    'selected_filters' => $selectedFilters,
                    'date_range' => [$dateStart, $dateEnd]
                ]);

            } else {
                // Se não há filtros específicos selecionados, usar creation_date como padrão
                $query->whereBetween('creation_date', [$dateStart, $dateEnd]);

                Log::info('Filtro padrão aplicado (creation_date):', [
                    'date_range' => [$dateStart, $dateEnd],
                    'reason' => 'Nenhum filtro específico selecionado'
                ]);
            }
        }

        // Executa a consulta
        $loads = $query->orderByDesc('id')->get();

        // Verificar quais cargas já foram cobradas
        $this->markAlreadyChargedLoads($loads);

        // Calcular total baseado no amount_type (se especificado)
        $amountType = $request->input('amount_type', 'price');
        if (in_array($amountType, ['price', 'paid_amount'])) {
            $totalAmount = $loads->sum(function($load) use ($amountType) {
                return (float) ($load->{$amountType} ?? 0);
            });
        } else {
            // Fallback para price se amount_type inválido
            $totalAmount = $loads->sum(function($load) {
                return (float) ($load->price ?? 0);
            });
        }
    }

    // ⭐ DEBUG MELHORADO - Informações mais detalhadas
    if ($hasFilters) {
        $appliedFilters = [];
        if ($request->has('filters') && is_array($request->filters)) {
            foreach ($request->filters as $column => $value) {
                if ($value === "1") {
                    $appliedFilters[] = $column;
                }
            }
        }

        Log::info('Filtros aplicados na consulta:', [
            'carrier_id' => $request->carrier_id,
            'carrier_scope' => $request->carrier_id === 'all' ? 'ALL CARRIERS' : 'SPECIFIC CARRIER',
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'amount_type' => $request->amount_type,
            'applied_filters' => $appliedFilters,
            'loads_count' => $loads->count(),
            'total_amount' => $totalAmount,
            'sql_query' => isset($query) ? $query->toSql() : 'N/A',
            'sql_bindings' => isset($query) ? $query->getBindings() : []
        ]);
    }

    // Retorna a view com os dados
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
    /**
     * ⭐ MÉTODO STORE ATUALIZADO - Adicionar após linha que cria $timeLineCharge
     */
    public function store(Request $request): JsonResponse
    {
        $arrayTypeDates = array_keys($request->input('filters', []));
        $loadIds = array_map('strval', $request->input('load_ids', []));

        if ($request->carrier_id === 'all') {
            // [... código existente para carrier_id = 'all' ...]
            $carriers = \App\Models\Load::whereIn('load_id', $loadIds)
                ->select('carrier_id')
                ->distinct()
                ->pluck('carrier_id');

            $criados = [];
            $existentes = [];

            foreach ($carriers as $carrierId) {
                $carrierLoadIds = \App\Models\Load::whereIn('load_id', $loadIds)
                    ->where('carrier_id', $carrierId)
                    ->pluck('load_id')
                    ->map('strval')
                    ->toArray();

                if (empty($carrierLoadIds)) {
                    continue;
                }

                $existing = TimeLineCharge::where('carrier_id', $carrierId)
                    ->get()
                    ->filter(function ($charge) use ($carrierLoadIds) {
                        $existingLoadIds = $charge->load_ids;
                        if (is_string($existingLoadIds)) {
                            $existingLoadIds = json_decode($existingLoadIds, true);
                        }
                        $existingLoadIds = array_map('strval', $existingLoadIds ?? []);
                        return count($existingLoadIds) === count($carrierLoadIds) &&
                            empty(array_diff($existingLoadIds, $carrierLoadIds)) &&
                            empty(array_diff($carrierLoadIds, $existingLoadIds));
                    });

                if ($existing->isNotEmpty()) {
                    $existentes[] = $carrierId;
                    continue;
                }

                $timeLineCharge = TimeLineCharge::create([
                    'invoice_id'       => null,
                    'price'            => $request->input('total_amount'),
                    'status_payment'   => "Invoiced",
                    'carrier_id'       => $carrierId,
                    'dispatcher_id'    => $request->input('dispatcher_id'),
                    'date_start'       => $request->input('date_start'),
                    'date_end'         => $request->input('date_end'),
                    'amount_type'      => $request->input('amount_type'),
                    'array_type_dates' => json_encode($arrayTypeDates),
                    'load_ids'         => json_encode($carrierLoadIds),
                ]);

                $invoiceId = date('Y') . '-' . date('W') . '-' . $timeLineCharge->id;
                $timeLineCharge->update(['invoice_id' => $invoiceId]);

                // ⭐ NOVA LINHA: Salvar snapshot dos loads
                $timeLineCharge->saveLoadSnapshot();

                \App\Models\Load::whereIn('load_id', $carrierLoadIds)->update(['payment_status' => 'paid']);

                $criados[] = [
                    'carrier_id' => $carrierId,
                    'invoice'    => $invoiceId,
                ];
            }

            return response()->json([
                'message'    => 'Processamento concluído.',
                'criadas'    => $criados,
                'existentes' => $existentes,
            ], 201);
        }

        // Se for um carrier_id específico
        if (!is_numeric($request->carrier_id)) {
            return response()->json([
                'message' => 'Carrier ID inválido.',
            ], 422);
        }

        $existing = TimeLineCharge::where('carrier_id', $request->carrier_id)
            ->get()
            ->filter(function ($charge) use ($loadIds) {
                $existingLoadIds = $charge->load_ids;
                if (is_string($existingLoadIds)) {
                    $existingLoadIds = json_decode($existingLoadIds, true);
                }
                $existingLoadIds = array_map('strval', $existingLoadIds ?? []);
                return count($existingLoadIds) === count($loadIds) &&
                    empty(array_diff($existingLoadIds, $loadIds)) &&
                    empty(array_diff($loadIds, $existingLoadIds));
            });

        if ($existing->isNotEmpty()) {
            return response()->json([
                'message' => 'Esta Invoice já foi salva.',
            ], 409);
        }

        $timeLineCharge = TimeLineCharge::create([
            'invoice_id'       => null,
            'price'            => $request->input('total_amount'),
            'status_payment'   => "Invoiced",
            'carrier_id'       => $request->input('carrier_id'),
            'dispatcher_id'    => $request->input('dispatcher_id'),
            'date_start'       => $request->input('date_start'),
            'date_end'         => $request->input('date_end'),
            'amount_type'      => $request->input('amount_type'),
            'array_type_dates' => json_encode($arrayTypeDates),
            'load_ids'         => json_encode($loadIds),
        ]);

        $invoiceId = date('Y') . '-' . date('W') . '-' . $timeLineCharge->id;
        $timeLineCharge->update(['invoice_id' => $invoiceId]);

        // ⭐ NOVA LINHA: Salvar snapshot dos loads
        $timeLineCharge->saveLoadSnapshot();

        \App\Models\Load::whereIn('load_id', $loadIds)->update(['payment_status' => 'paid']);

        return response()->json([
            'message' => 'Time Line Charge created successfully.',
            'id'      => $timeLineCharge->id,
            'invoice' => $invoiceId,
        ], 201);
    }

    public function show_antigo(Request $request, $id)
    {
        // Obtém o registro
        $charge = TimeLineCharge::findOrFail($id);

        // Dados auxiliares
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::with('user')
            ->where('user_id', auth()->id())
            ->first();

        // Carregar carriers e dispatchers filtrados pelo dispatcher do usuário logado
        if (!$dispatcher) {
            $carriers = collect();
            $dispatchers = collect();
        } else {
            // Criar coleção com o dispatcher encontrado
            $dispatchers = collect([$dispatcher]);
            
            // Filtra os carriers pelo dispatcher_id
            $carriers = Carrier::with('user')
                ->where('dispatcher_id', $dispatcher->id)
                ->get();
        }

        // Carregamento de filtros
        $dateStart   = $charge->date_start;
        $dateEnd     = $charge->date_end;
        $amountType  = $charge->amount_type;
        $filters     = json_decode($charge->array_type_dates, true) ?? [];
        $loadIds     = json_decode($charge->load_ids, true);

        // Validações básicas
        if (!in_array($amountType, ['price', 'paid_amount'])) {
            return redirect()->back()->with('error', 'Tipo de valor inválido.');
        }

        if (!is_array($loadIds)) {
            return redirect()->back()->with('error', 'Load IDs inválidos.');
        }

        // Inicia a query com os load_ids
        $query = Load::whereIn('load_id', $loadIds);

        // Aplica os filtros de data nos campos selecionados
        if ($dateStart && $dateEnd && count($filters)) {
            $query->where(function ($q) use ($filters, $dateStart, $dateEnd) {
                foreach ($filters as $field) {
                    $q->orWhereBetween($field, [$dateStart, $dateEnd]);
                }
            });
        }

        $loads = $query
        ->with(['employee.user']) // ← isso carrega o employee e o user relacionado
        ->select('load_id', 'employee_id', $amountType)
        ->get();

        // Calcula total do valor
        $totalAmount = $loads->sum($amountType);

        // Obtém as comissões dos funcionários
        $employeeIds = $loads->pluck('employee_id')->filter()->unique()->values();
        $comissions = Comission::whereIn('employee_id', $employeeIds)
                        ->pluck('value', 'employee_id'); // Ex: [5 => 10, 3 => 5]

        // Enriquecer os dados com a comissão calculada
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

        // Soma total das comissões
        $totalComission = $loadsWithComission->sum('amount_comission');

        //return $totalComission;

        // Retorna a view com os dados completos
        return view('invoice.time_line_charge.show', compact(
            'charge',
            'carriers',
            'dispatchers',
            'loadsWithComission',
            'totalAmount',
            'totalComission',
            'amountType',
            'filters',
            'loads'
        ));
    }

    /**
     * ⭐ MÉTODO SHOW ATUALIZADO - Substituir método show() existente
     */
    public function show(Request $request, $id)
    {
        // Obtém o registro com relacionamentos necessários
        $charge = TimeLineCharge::with(['carrier.user', 'dispatcher.user'])->findOrFail($id);

        // Dados auxiliares
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::with('user')
            ->where('user_id', auth()->id())
            ->first();

        // Carregar carriers e dispatchers filtrados pelo dispatcher do usuário logado
        if (!$dispatcher) {
            $carriers = collect();
            $dispatchers = collect();
        } else {
            // Criar coleção com o dispatcher encontrado
            $dispatchers = collect([$dispatcher]);
            
            // Filtra os carriers pelo dispatcher_id
            $carriers = Carrier::with('user')
                ->where('dispatcher_id', $dispatcher->id)
                ->get();
        }

        // Carregamento de filtros
        $dateStart = $charge->date_start;
        $dateEnd = $charge->date_end;
        $amountType = $charge->amount_type;

        // Decodificar arrays com segurança
        $filters = $charge->array_type_dates;
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?? [];
        } elseif (!is_array($filters)) {
            $filters = [];
        }

        // Validações básicas
        if (!in_array($amountType, ['price', 'paid_amount'])) {
            return redirect()->back()->with('error', 'Invalid amount type.');
        }

        // ⭐ NOVA LÓGICA: Usar método que combina loads existentes + histórico
        $loads = $charge->getLoadsWithHistory();



        // Calcula total do valor baseado no amount_type
        $totalAmount = $loads->sum($amountType);

        // Busca o valor do percentual no deals
        $dealPercent = Deal::where('carrier_id', $charge->carrier_id)
                           ->where('dispatcher_id', $charge->dispatcher_id)
                           ->value('value');

        // Calcula o valor da comissão
        $dealAmount = 0;
        if ($dealPercent !== null) {
            $dealAmount = ($dealPercent / 100) * $totalAmount;
        }

        // Informações de vencimento e status
        $dueDate = $charge->due_date;
        $isOverdue = false;
        $daysUntilDue = null;

        if ($dueDate) {
            $dueDateCarbon = \Carbon\Carbon::parse($dueDate);
            $isOverdue = $dueDateCarbon->isPast() && $charge->status_payment !== 'paid';
            $daysUntilDue = now()->diffInDays($dueDateCarbon, false);
        }

        // ⭐ NOVA LÓGICA: Separar loads por status (existentes vs históricos)
        $existingLoads = $loads->where('is_historical', false);
        $historicalLoads = $loads->where('is_historical', true);

        // Log para debug
        Log::info('Invoice Show Details', [
            'invoice_id' => $charge->invoice_id,
            'total_amount' => $totalAmount,
            'total_loads' => $loads->count(),
            'existing_loads' => $existingLoads->count(),
            'historical_loads' => $historicalLoads->count()
        ]);

        // Retorna a view com os dados completos
        return view('invoice.time_line_charge.show', compact(
            'charge',
            'carriers',
            'dispatchers',
            'loads',
            'existingLoads',        // ⭐ NOVA VARIÁVEL
            'historicalLoads',      // ⭐ NOVA VARIÁVEL
            'totalAmount',
            'amountType',
            'filters',
            'dealPercent',
            'dealAmount',
            'dueDate',
            'isOverdue',
            'daysUntilDue'
        ));
    }



    public function checkDuplicateLoads(Request $request)
{
    $loadIds = $request->input('load_ids', []);

    if (empty($loadIds)) {
        return response()->json(['duplicates' => []]);
    }

    // Buscar todas as time_line_charges existentes
    $existingCharges = TimeLineCharge::all();
    $duplicates = [];

    foreach ($existingCharges as $charge) {
        $chargeLoadIds = is_string($charge->load_ids)
            ? json_decode($charge->load_ids, true)
            : $charge->load_ids;

        if (!is_array($chargeLoadIds)) continue;

        // Verificar interseção entre as cargas
        $commonLoads = array_intersect($loadIds, $chargeLoadIds);

        if (!empty($commonLoads)) {
            foreach ($commonLoads as $loadId) {
                $duplicates[] = [
                    'load_id' => $loadId,
                    'invoice_id' => $charge->invoice_id,
                    'invoice_internal_id' => $charge->id,
                    'charge_date' => $charge->created_at->format('d/m/Y'),
                    'amount' => $charge->price,
                    'carrier' => $charge->carrier->company_name ?? 'Unknown',
                ];
            }
        }
    }

    return response()->json(['duplicates' => $duplicates]);
}

private function markAlreadyChargedLoads($loads)
{
    $loadIds = $loads->pluck('load_id')->toArray();

    // Buscar todas as time_line_charges
    $existingCharges = TimeLineCharge::all();
    $chargedLoads = [];

    foreach ($existingCharges as $charge) {
        $chargeLoadIds = is_string($charge->load_ids)
            ? json_decode($charge->load_ids, true)
            : $charge->load_ids;

        if (!is_array($chargeLoadIds)) continue;

        foreach ($chargeLoadIds as $chargeLoadId) {
            if (in_array($chargeLoadId, $loadIds)) {
                $chargedLoads[$chargeLoadId] = [
                    'invoice_id' => $charge->invoice_id,
                    'internal_id' => $charge->id,
                    'charge_date' => $charge->created_at->format('d/m/Y'),
                    'amount' => $charge->price
                ];
            }
        }
    }

    // Adicionar informação de cobrança às cargas
    foreach ($loads as $load) {
        if (isset($chargedLoads[$load->load_id])) {
            $load->already_charged = true;
            $load->charge_info = $chargedLoads[$load->load_id];
        } else {
            $load->already_charged = false;
            $load->charge_info = null;
        }
    }
}


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

        return $totalComission;

        return response()->json([
            'invoice_id' => $TimeLineCharge->id,
            'amount_type' => $amountType,
            'total_amount' => $totalAmount,
            'total_comission' => round($totalComission, 2),
            'loads' => $loadsWithComission,
        ]);
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

    public function load_invoice_destroy($load_id, $time_line_charge_id)
    {
        // Busca o TimeLineCharge pelo ID
        $charge = TimeLineCharge::find($time_line_charge_id);

        if (!$charge) {
            return redirect()->back()->with('error', 'TimeLineCharge não encontrado.');
        }

        // Decodifica os load_ids com segurança
        $loadIds = $charge->load_ids;

        if (is_string($loadIds)) {
            $loadIds = json_decode($loadIds, true);

            if (is_string($loadIds)) {
                $loadIds = json_decode($loadIds, true);
            }
        }

        $loadIds = $loadIds ?? [];

        // Verifica se o ID está presente
        if (!in_array($load_id, $loadIds)) {
            return redirect()->back()->with('error', 'Este Load ID não está associado a este TimeLineCharge.');
        }

        // Remove o load_id e salva de volta
        $updatedLoadIds = array_filter($loadIds, fn($id) => $id !== $load_id);
        $charge->load_ids = array_values($updatedLoadIds); // reindexa
        $charge->save();

        // Atualiza o payment_status do load para "no_charge"
        \App\Models\Load::where('load_id', $load_id)->update(['payment_status' => 'no_charge']);

        return redirect()->back()->with('success', 'Load ID removido com sucesso do TimeLineCharge.');
    }

    public function getChargeDetails($id)
{
    try {
        $charge = TimeLineCharge::with(['carrier.user', 'dispatcher.user'])
                                ->findOrFail($id);

        // Decodificar load_ids se necessário
        $loadIds = $charge->load_ids;
        if (is_string($loadIds)) {
            $loadIds = json_decode($loadIds, true);
        }

        return response()->json([
            'id' => $charge->id,
            'invoice_id' => $charge->invoice_id,
            'price' => $charge->price,
            'date_start' => $charge->date_start,
            'date_end' => $charge->date_end,
            'created_at' => $charge->created_at,
            'carrier' => $charge->carrier,
            'dispatcher' => $charge->dispatcher,
            'load_ids' => $loadIds,
            'payment_terms' => $charge->payment_terms,
            'due_date' => $charge->due_date,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Charge not found or error loading details'
        ], 404);
    }
}




}
