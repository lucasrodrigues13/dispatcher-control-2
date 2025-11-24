<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChargeSetup;
use App\Models\Carrier;
use App\Models\Dispatcher;

class ChargeSetupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', auth()->id())->first();

        if (!$dispatcher) {
            // Retornar paginador vazio quando não há dispatcher
            $charges_setup = ChargeSetup::with('carrier.user')
                ->whereRaw('1 = 0') // Query que sempre retorna vazio
                ->paginate(10);
        } else {
            // Filtrar charge setups pelo dispatcher do usuário logado
            $charges_setup = ChargeSetup::with('carrier.user')
                ->where('dispatcher_id', $dispatcher->id)
                ->paginate(10);
        }
        
        return view('invoice.charge_setup.index', compact('charges_setup'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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

        return view("invoice.charge_setup.create", compact("carriers", "dispatchers"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Obter owner do tenant
        $authUser = auth()->user();
        $ownerId = $authUser->getOwnerId();
        
        // Validar permissão
        if (!$authUser->canManageTenant()) {
            return redirect()->back()->withErrors(['error' => 'Você não tem permissão para criar este registro.'])->withInput();
        }
        
        // Obter dispatchers do tenant
        $tenantDispatchers = Dispatcher::where('owner_id', $ownerId)->pluck('id');

        // Validar se o dispatcher_id do request pertence ao tenant
        if ($request['dispatcher_id'] && !in_array($request['dispatcher_id'], $tenantDispatchers->toArray())) {
            return redirect()->back()->withErrors(['dispatcher_id' => 'Dispatcher não pertence ao seu tenant.'])->withInput();
        }

        // Validar se o carrier pertence ao dispatcher do tenant
        $carrier = Carrier::where('id', $request['carrier_id'])
            ->whereIn('dispatcher_id', $tenantDispatchers)
            ->first();

        if (!$carrier) {
            return redirect()->back()->withErrors(['carrier_id' => 'Carrier inválido ou não pertence ao seu tenant.'])->withInput();
        }
        
        // Usar dispatcher do request ou dispatcher do carrier
        $dispatcherId = $request['dispatcher_id'] ?? $carrier->dispatcher_id;

        $selectedFilters = collect($request->input('filters', []))
                            ->filter(fn($v) => $v == '1')
                            ->keys()
                            ->toArray();

        $chargeSetup = ChargeSetup::create([
            'carrier_id'           => $request['carrier_id'],
            'dispatcher_id'        => $dispatcherId,
            'price'                => $request['amount_type'], // ou amount_type se já ajustou
            'charges_setup_array'  => $selectedFilters,
        ]);

        return redirect(route("charges_setups.index"));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    // Controller: ChargeSetupController.php
    public function edit($id)
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

        $chargeSetup = ChargeSetup::findOrFail($id);

        // Certifique-se de que o campo charges_setup_array está como array
        $selectedFilters = is_array($chargeSetup->charges_setup_array)
            ? $chargeSetup->charges_setup_array
            : json_decode($chargeSetup->charges_setup_array, true);

        return view('invoice.charge_setup.edit', compact('chargeSetup', 'carriers', 'dispatchers', 'selectedFilters'));
    }

    public function update(Request $request, $id)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', auth()->id())->first();

        if (!$dispatcher) {
            return redirect()->back()->withErrors(['dispatcher_id' => 'Dispatcher não encontrado para este usuário.']);
        }

        // Buscar o charge setup e validar se pertence ao dispatcher logado
        $chargeSetup = ChargeSetup::where('id', $id)
            ->where('dispatcher_id', $dispatcher->id)
            ->firstOrFail();

        // Validar se o dispatcher_id do request pertence ao usuário logado
        if ($request['dispatcher_id'] != $dispatcher->id) {
            return redirect()->back()->withErrors(['dispatcher_id' => 'Dispatcher inválido.'])->withInput();
        }

        // Validar se o carrier pertence ao dispatcher logado
        $carrier = Carrier::where('id', $request['carrier_id'])
            ->where('dispatcher_id', $dispatcher->id)
            ->first();

        if (!$carrier) {
            return redirect()->back()->withErrors(['carrier_id' => 'Carrier inválido ou não pertence a este dispatcher.'])->withInput();
        }

        $selectedFilters = collect($request->input('filters', []))
                            ->filter(fn($v) => $v == '1')
                            ->keys()
                            ->toArray();

        $chargeSetup->update([
            'carrier_id'          => $request['carrier_id'],
            'dispatcher_id'       => $dispatcher->id,
            'price'               => $request['amount_type'],
            'charges_setup_array' => $selectedFilters,
        ]);

        return redirect()->route('charges_setups.index')->with('success', 'Charge Setup updated successfully.');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', auth()->id())->first();

        if (!$dispatcher) {
            abort(403, 'Dispatcher não encontrado para este usuário.');
        }

        // Buscar o charge setup e validar se pertence ao dispatcher logado
        $chargeSetup = ChargeSetup::where('id', $id)
            ->where('dispatcher_id', $dispatcher->id)
            ->firstOrFail();

        $chargeSetup->delete();

        return redirect()->route('charges_setups.index')->with('success', 'Charge Setup deletado com sucesso.');
    }

// SUBSTITUIR o método getSetupByCarrier por esta versão mais segura:

public function getSetupByCarrier($carrierId)
{
    try {
        // Buscar o charge setup mais recente para este carrier
        $setup = ChargeSetup::with(['carrier.user', 'dispatcher.user'])
                           ->where('carrier_id', $carrierId)
                           ->orderBy('created_at', 'desc')
                           ->first();

        if (!$setup) {
            return response()->json([
                'success' => false,
                'message' => 'No charge setup found for this carrier'
            ]);
        }

        // Processar os filtros salvos
        $filters = [];
        if ($setup->charges_setup_array) {
            if (is_array($setup->charges_setup_array)) {
                $filters = $setup->charges_setup_array;
            } else {
                $filters = json_decode($setup->charges_setup_array, true) ?? [];
            }
        }

        // Criar resumo para exibição
        $summary = $this->createSetupSummary($setup, $filters);

        return response()->json([
            'success' => true,
            'setup' => [
                'id' => $setup->id,
                'carrier_id' => $setup->carrier_id,
                'dispatcher_id' => $setup->dispatcher_id,
                'price' => $setup->price,
                'filters' => $filters,
                'summary' => $summary,
                'carrier_name' => $setup->carrier ? ($setup->carrier->company_name ?? $setup->carrier->user->name ?? 'Unknown Carrier') : 'Unknown Carrier',
                'dispatcher_name' => $setup->dispatcher ? ($setup->dispatcher->user->name ?? 'Unknown Dispatcher') : 'Unknown Dispatcher',
                'created_at' => $setup->created_at->format('Y-m-d H:i:s')
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error fetching charge setup by carrier: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error loading charge setup for this carrier'
        ], 500);
    }
}

/**
 * Criar resumo do setup para exibição (versão mais segura)
 */
private function createSetupSummary($setup, $filters)
{
    $summary = [];

    // Adicionar dispatcher
    if ($setup->dispatcher && $setup->dispatcher->user) {
        $summary[] = "Dispatcher: " . $setup->dispatcher->user->name;
    } elseif ($setup->dispatcher_id) {
        $summary[] = "Dispatcher ID: " . $setup->dispatcher_id;
    }

    // Adicionar tipo de valor
    if ($setup->price) {
        $summary[] = "Amount: " . ucfirst(str_replace('_', ' ', $setup->price));
    }

    // Adicionar filtros de data
    if (!empty($filters)) {
        $filterNames = array_map(function($filter) {
            return ucfirst(str_replace('_', ' ', $filter));
        }, $filters);
        $summary[] = "Filters: " . implode(', ', $filterNames);
    }

    return empty($summary) ? 'Basic setup' : implode(' | ', $summary);
}

/**
 * Buscar charge setups de todos os carriers para 'All Carriers'
 */
public function getAllCarriersSetup()
{
    try {
        // Buscar todos os charge setups existentes
        $setups = ChargeSetup::with(['carrier.user', 'dispatcher.user'])
                           ->orderBy('carrier_id')
                           ->orderBy('created_at', 'desc')
                           ->get()
                           ->groupBy('carrier_id')
                           ->map(function($carrierSetups) {
                               // Pegar o setup mais recente de cada carrier
                               return $carrierSetups->first();
                           });

        if ($setups->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No charge setups found for any carrier'
            ]);
        }

        // Coletar todos os filtros únicos de todos os carriers
        $allFilters = [];
        $carrierSummaries = [];

        foreach ($setups as $setup) {
            // Processar filtros de cada setup
            $filters = [];
            if ($setup->charges_setup_array) {
                if (is_array($setup->charges_setup_array)) {
                    $filters = $setup->charges_setup_array;
                } else {
                    $filters = json_decode($setup->charges_setup_array, true) ?? [];
                }
            }

            // Adicionar filtros únicos à lista geral
            $allFilters = array_unique(array_merge($allFilters, $filters));

            // Criar resumo do carrier
            $carrierName = $setup->carrier ? ($setup->carrier->company_name ?? $setup->carrier->user->name ?? 'Unknown') : 'Unknown';
            $carrierSummaries[] = [
                'carrier_id' => $setup->carrier_id,
                'carrier_name' => $carrierName,
                'filters' => $filters,
                'dispatcher_id' => $setup->dispatcher_id,
                'price' => $setup->price
            ];
        }

        return response()->json([
            'success' => true,
            'all_carriers_setup' => [
                'combined_filters' => array_values($allFilters), // Filtros únicos de todos os carriers
                'carrier_summaries' => $carrierSummaries,
                'total_carriers' => count($setups),
                'summary' => $this->createAllCarriersSummary($allFilters, count($setups))
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error fetching all carriers setup: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error loading charge setups for all carriers'
        ], 500);
    }
}

/**
 * Criar resumo para 'All Carriers'
 */
private function createAllCarriersSummary($filters, $carrierCount)
{
    $summary = [];

    $summary[] = "Carriers: {$carrierCount} carriers";

    if (!empty($filters)) {
        $filterNames = array_map(function($filter) {
            return ucfirst(str_replace('_', ' ', $filter));
        }, $filters);
        $summary[] = "Combined Filters: " . implode(', ', $filterNames);
    } else {
        $summary[] = "No common filters found";
    }

    return implode(' | ', $summary);
}

}
