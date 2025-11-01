<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Dispatcher;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Buscar apenas deals do dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            $deals = collect();
        } else {
            $deals = Deal::with(['dispatcher.user', 'carrier.user'])
                ->where('dispatcher_id', $dispatcher->id)
                ->paginate(10);
        }

        return view('deal.index', compact('deals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Buscar apenas o dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            $dispatchers = collect();
            $carriers = collect();
        } else {
            $dispatchers = collect([$dispatcher->load('user')]);
            
            // Buscar apenas carriers do dispatcher logado
            $carriers = Carrier::with('user')
                ->where('dispatcher_id', $dispatcher->id)
                ->get();
        }
        
        return view('deal.create', compact('dispatchers', 'carriers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            return back()->withErrors(['error' => 'Dispatcher não encontrado para este usuário.'])->withInput();
        }

        $validated = $request->validate([
            'dispatcher_id' => 'required|exists:dispatchers,id',
            'carrier_id' => 'required|exists:carriers,id',
            'value' => 'required|numeric|min:0',
        ]);

        // Validar se o dispatcher pertence ao usuário logado
        if ($validated['dispatcher_id'] != $dispatcher->id) {
            return back()->withErrors(['dispatcher_id' => 'Dispatcher inválido.'])->withInput();
        }

        // Validar se o carrier pertence ao dispatcher
        $carrier = Carrier::where('id', $validated['carrier_id'])
            ->where('dispatcher_id', $dispatcher->id)
            ->first();

        if (!$carrier) {
            return back()->withErrors(['carrier_id' => 'Carrier inválido ou não pertence a este dispatcher.'])->withInput();
        }

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
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            abort(403, 'Dispatcher não encontrado para este usuário.');
        }

        // Buscar apenas deal do dispatcher logado
        $deal = Deal::with(['dispatcher', 'carrier'])
            ->where('id', $id)
            ->where('dispatcher_id', $dispatcher->id)
            ->firstOrFail();
            
        return view('deal.show', compact('deal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            abort(403, 'Dispatcher não encontrado para este usuário.');
        }

        // Buscar apenas deal do dispatcher logado
        $deal = Deal::where('id', $id)
            ->where('dispatcher_id', $dispatcher->id)
            ->firstOrFail();
        
        // Buscar apenas o dispatcher do usuário logado
        $dispatchers = collect([$dispatcher->load('user')]);
        
        // Buscar apenas carriers do dispatcher logado
        $carriers = Carrier::with('user')
            ->where('dispatcher_id', $dispatcher->id)
            ->get();
        
        return view('deal.edit', compact('deal', 'dispatchers', 'carriers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            return back()->withErrors(['error' => 'Dispatcher não encontrado para este usuário.'])->withInput();
        }

        $validated = $request->validate([
            'dispatcher_id' => 'required|exists:dispatchers,id',
            'carrier_id' => 'required|exists:carriers,id',
            'value' => 'required|numeric|min:0',
        ]);

        // Buscar apenas deal do dispatcher logado
        $deal = Deal::where('id', $id)
            ->where('dispatcher_id', $dispatcher->id)
            ->firstOrFail();

        // Validar se o dispatcher pertence ao usuário logado
        if ($validated['dispatcher_id'] != $dispatcher->id) {
            return back()->withErrors(['dispatcher_id' => 'Dispatcher inválido.'])->withInput();
        }

        // Validar se o carrier pertence ao dispatcher
        $carrier = Carrier::where('id', $validated['carrier_id'])
            ->where('dispatcher_id', $dispatcher->id)
            ->first();

        if (!$carrier) {
            return back()->withErrors(['carrier_id' => 'Carrier inválido ou não pertence a este dispatcher.'])->withInput();
        }

        $deal->update($validated);

        return redirect()->route('deals.index')->with('success', 'Negociação atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            abort(403, 'Dispatcher não encontrado para este usuário.');
        }

        // Buscar apenas deal do dispatcher logado
        $deal = Deal::where('id', $id)
            ->where('dispatcher_id', $dispatcher->id)
            ->firstOrFail();
            
        $deal->delete();

        return redirect()->route('deals.index')->with('success', 'Negociação removida com sucesso!');
    }
}
