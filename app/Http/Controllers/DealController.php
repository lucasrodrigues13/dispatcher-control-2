<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Dispatcher;
use App\Models\Carrier;
use Illuminate\Http\Request;

class DealController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $deals = Deal::with(['dispatcher.user', 'carrier.user'])->paginate(10);

        return view('deal.index', compact('deals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();
        return view('deal.create', compact('dispatchers', 'carriers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dispatcher_id' => 'required|exists:dispatchers,id',
            'carrier_id' => 'required|exists:carriers,id',
            'value' => 'required|numeric|min:0',
        ]);

        // Verificar se já existe o vínculo
        $exists = Deal::where('dispatcher_id', $validated['dispatcher_id'])
                    ->where('carrier_id', $validated['carrier_id'])
                    ->exists();

        if ($exists) {
            return back()->withErrors(['carrier_id' => 'There is already a Deal for this Carrier.'])->withInput();
        }

        // Criar o Deal
        Deal::create($validated);

        // Redirecionar com sucesso (opcional)
        return redirect()->back()->with('success', 'Deal criado com sucesso.');

        // return redirect()->route('deals.index')->with('success', 'Negociação criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $deal = Deal::with(['dispatcher', 'carrier'])->findOrFail($id);
        return view('deal.show', compact('deal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $deal = Deal::findOrFail($id);
        $dispatchers = Dispatcher::with("user")->get();
        $carriers = Carrier::with("user")->get();
        return view('deal.edit', compact('deal', 'dispatchers', 'carriers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'dispatcher_id' => 'required|exists:dispatchers,id',
            'carrier_id' => 'required|exists:carriers,id',
            'value' => 'required|numeric|min:0',
        ]);

        $deal = Deal::findOrFail($id);
        $deal->update($validated);

        return redirect()->route('deals.index')->with('success', 'Negociação atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deal = Deal::findOrFail($id);
        $deal->delete();

        return redirect()->route('deals.index')->with('success', 'Negociação removida com sucesso!');
    }
}
