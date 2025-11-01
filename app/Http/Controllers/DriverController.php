<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use App\Models\Carrier;
use App\Models\Dispatcher;
use App\Repositories\UsageTrackingRepository;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewCarrierCredentialsMail;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Busca o dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();

        // Se não existir dispatcher, retorna vazio
        if (!$dispatcher) {
            $drivers = collect();
        } else {
            // Busca os carriers do dispatcher
            $carriers = Carrier::where('dispatcher_id', $dispatcher->id)->pluck('id');

            // Filtra os drivers pelos carriers do dispatcher
            $drivers = Driver::with(['user', 'carrier'])
                ->whereIn('carrier_id', $carriers)
                ->paginate(10);
        }

        return view('carrier.driver.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $billingService = app(BillingService::class);
        $usageCheck = $billingService->checkUsageLimits(Auth::user(), 'driver');

        $showUpgradeModal = !empty($usageCheck['suggest_upgrade']);

        // Busca o dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();

        // Busca apenas os carriers vinculados ao dispatcher do usuário logado
        $carriers = [];
        if ($dispatcher) {
            $carriers = Carrier::with('user')
                ->where('dispatcher_id', $dispatcher->id)
                ->get();
        }

        return view('carrier.driver.create', compact('carriers','showUpgradeModal','usageCheck'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'carrier_id'   => 'required|exists:carriers,id',
            'phone'        => 'required|string|max:20',
            'ssn_tax_id'   => 'required|string|max:50',
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
        ], [
            'email.unique' => 'This email already exists...',
        ]);

        $base = \Illuminate\Support\Str::of($request->input('name'))
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '');
        $plainPassword = (string) $base.'2025';

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Cria o usuário e obtém o ID
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($plainPassword),
            'must_change_password' => true,
            'email_verified_at' => now(),
        ]);

        // Cria o driver
        Driver::create([
            'carrier_id' => $validated['carrier_id'],
            'phone'      => $validated['phone'],
            'ssn_tax_id' => $validated['ssn_tax_id'],
            'user_id'    => $user->id,
        ]);

        app(UsageTrackingRepository::class)->incrementUsage(Auth::user(), 'driver');

        Mail::to($user->email)->queue(new NewCarrierCredentialsMail($user, $plainPassword));

        if ($request->register_type === "auth_register") {
            event(new Registered($user));
            Auth::login($user);
            return redirect(RouteServiceProvider::HOME);
        }

        return redirect()->route('drivers.index')
                         ->with('success', 'Driver criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $driver = Driver::with(['user', 'carrier'])->findOrFail($id);

        return view('carrier.driver.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $driver = Driver::with('user')->findOrFail($id);
        $carriers = Carrier::with('user')->get();

        return view('carrier.driver.edit', compact('driver', 'carriers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $driver = Driver::with('user')->findOrFail($id);

        // Validação dos dados
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => "required|email|unique:users,email,{$driver->user_id}",
            'password'     => 'nullable|string|min:8|confirmed',
            'carrier_id'   => 'required|exists:carriers,id',
            'phone'        => 'required|string|max:20',
            'ssn_tax_id'   => 'required|string|max:50',
        ]);

        // Atualiza dados do usuário
        $driver->user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            // Atualiza senha somente se preenchida
            'password' => $validated['password'] ? Hash::make($validated['password']) : $driver->user->password,
        ]);

        // Atualiza dados do driver
        $driver->update([
            'carrier_id' => $validated['carrier_id'],
            'phone'      => $validated['phone'],
            'ssn_tax_id' => $validated['ssn_tax_id'],
        ]);

        return redirect()->route('drivers.index')
                         ->with('success', 'Driver atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $driver = Driver::with('user')->findOrFail($id);

        // Remove o driver e o usuário associado
        $driver->delete();
        $driver->user->delete();

        return redirect()->route('drivers.index')
                         ->with('success', 'Driver removido com sucesso.');
    }
}
