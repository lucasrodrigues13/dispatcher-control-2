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
use Illuminate\Support\Facades\Log;
use App\Mail\NewCarrierCredentialsMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Busca o dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();

        // Se não existir dispatcher, retorna paginação vazia
        if (!$dispatcher) {
            $drivers = Driver::whereRaw('1 = 0')->paginate(10);
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

        try {
            DB::beginTransaction();

            // ⭐ NOVO: Verificar limite de usuários antes de criar driver
            $billingService = app(BillingService::class);
            $userLimitCheck = $billingService->checkUserLimit(Auth::user(), 'driver');

            if (!$userLimitCheck['allowed']) {
                // Se sugerir upgrade, redirecionar para montar plano
                if ($userLimitCheck['suggest_upgrade'] ?? false) {
                    return redirect()->route('subscription.build-plan')
                        ->with('error', $userLimitCheck['message']);
                }
                
                return redirect()->back()
                    ->withErrors(['error' => $userLimitCheck['message']])
                    ->withInput();
            }

            // Cria o usuário
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($plainPassword),
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]);

            // Cria o driver (vinculado ao user_id)
            Driver::create([
                'carrier_id' => $validated['carrier_id'],
                'name' => $validated['name'],
                'phone'      => $validated['phone'],
                'ssn_tax_id' => $validated['ssn_tax_id'],
                'email' => $validated['email'],
            ]);

            // Contabiliza uso
            app(UsageTrackingRepository::class)->incrementUsage(Auth::user(), 'driver');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao criar driver', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar driver: ' . $e->getMessage()])
                ->withInput();
        }

        // Envia email com credenciais (com tratamento de erro para não quebrar o fluxo)
        try {
            Mail::to($user->email)->queue(new NewCarrierCredentialsMail($user, $plainPassword));
        } catch (\Exception $e) {
            Log::warning('Falha ao enviar email de credenciais', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            // Não interrompe o fluxo - o driver foi criado com sucesso
        }

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

        try {
            DB::beginTransaction();

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

            DB::commit();

            return redirect()->route('drivers.index')
                             ->with('success', 'Driver atualizado com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao atualizar driver', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'driver_id' => $id
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar driver: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $driver = Driver::with('user')->findOrFail($id);

        try {
            DB::beginTransaction();

            // Remove o driver e o usuário associado
            $driver->delete();
            if ($driver->user) {
                $driver->user->delete();
            }

            DB::commit();

            return redirect()->route('drivers.index')
                             ->with('success', 'Driver removido com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao remover driver', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'driver_id' => $id
            ]);

            return redirect()->route('drivers.index')
                             ->withErrors(['error' => 'Erro ao remover driver: ' . $e->getMessage()]);
        }
    }
}
