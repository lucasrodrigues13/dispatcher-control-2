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
use App\Http\Controllers\Traits\ToggleUserStatus;

class DriverController extends Controller
{
    use ToggleUserStatus;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // ⭐ CORRIGIDO: Usar TenantScope automaticamente - buscar drivers do tenant
        // O TenantScope já filtra automaticamente pelos carriers do tenant
        // Carregar relacionamentos necessários
        $drivers = Driver::with(['carrier.dispatcher.user'])
            ->paginate(10);
        
        // ⭐ CORRIGIDO: Carregar users através do email para cada driver
        $driverEmails = $drivers->pluck('email')->filter()->unique();
        $users = User::whereIn('email', $driverEmails)->get()->keyBy('email');
        
        // Associar users aos drivers
        $drivers->getCollection()->transform(function ($driver) use ($users) {
            if ($driver->email && isset($users[$driver->email])) {
                $driver->setRelation('user', $users[$driver->email]);
            }
            return $driver;
        });

        return view('carrier.driver.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $authUser = Auth::user();
        $adminTenantService = app(\App\Services\AdminTenantService::class);
        
        // ⭐ NOVO: Se for admin, verificar se está visualizando um tenant específico
        if ($authUser->isAdmin()) {
            if ($adminTenantService->isViewingAll()) {
                return redirect()->back()
                    ->with('error', 'Por favor, selecione um tenant específico no dropdown acima antes de criar um novo usuário.');
            }
            
            // Obter o tenant selecionado para validação
            $viewingTenantId = $adminTenantService->getViewingTenantId();
            $viewingTenant = $viewingTenantId ? User::find($viewingTenantId) : null;
            
            if (!$viewingTenant) {
                return redirect()->back()
                    ->with('error', 'Tenant selecionado não encontrado. Por favor, selecione um tenant válido.');
            }
            
            // Usar o tenant selecionado para validação
            $userForValidation = $viewingTenant;
        } else {
            $userForValidation = $authUser;
        }
        
        // ⭐ VALIDAÇÃO PRIMEIRO: Verificar limite ANTES de qualquer coisa
        $billingService = app(BillingService::class);
        $userLimitCheck = $billingService->checkUserLimit($userForValidation, 'driver');

        // Se não tiver permissão, SEMPRE redirecionar ANTES de mostrar formulário
        if (!($userLimitCheck['allowed'] ?? false)) {
            // Se sugerir upgrade, redirecionar para tela de planos
            if ($userLimitCheck['suggest_upgrade'] ?? false) {
                return redirect()->route('subscription.build-plan')
                    ->with('error', $userLimitCheck['message'] ?? 'Limite atingido. Faça upgrade do seu plano.');
            }
            
            // Caso contrário, voltar com erro
            return redirect()->back()
                ->with('error', $userLimitCheck['message'] ?? 'Você não tem permissão para criar drivers.');
        }

        // Busca o dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();

        // Busca apenas os carriers vinculados ao dispatcher do usuário logado
        $carriers = [];
        if ($dispatcher) {
            $carriers = Carrier::with('user')
                ->where('dispatcher_id', $dispatcher->id)
                ->get();
        }

        // Verificar se deve mostrar modal de upgrade (caso ainda tenha permissão mas esteja próximo do limite)
        $usageCheck = $userLimitCheck; // Reutilizar o mesmo resultado
        $showUpgradeModal = false;
        
        // Se permitido mas com warning, mostrar modal
        if (($usageCheck['allowed'] ?? true) && ($usageCheck['warning'] ?? false)) {
            $showUpgradeModal = true;
        }

        return view('carrier.driver.create', compact('carriers', 'showUpgradeModal', 'usageCheck'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ⭐ NOVO: Se for admin, verificar tenant selecionado
        $authUser = Auth::user();
        $adminTenantService = app(\App\Services\AdminTenantService::class);
        
        if ($authUser->isAdmin()) {
            if ($adminTenantService->isViewingAll()) {
                return redirect()->back()
                    ->with('error', 'Por favor, selecione um tenant específico no dropdown acima antes de criar um novo usuário.');
            }
            
            $viewingTenantId = $adminTenantService->getViewingTenantId();
            $viewingTenant = $viewingTenantId ? User::find($viewingTenantId) : null;
            
            if (!$viewingTenant) {
                return redirect()->back()
                    ->with('error', 'Tenant selecionado não encontrado.');
            }
            
            // ⭐ CORRIGIDO: Sempre usar o tenant selecionado no topo da tela
            $targetOwnerId = $viewingTenant->id;
        } else {
            $targetOwnerId = $authUser->getOwnerId();
            
            // Validar permissão
            if (!$authUser->canManageTenant()) {
                return redirect()->back()
                    ->withErrors(['error' => 'Você não tem permissão para criar este registro.'])
                    ->withInput();
            }
        }
        
        $ownerId = $targetOwnerId;
        
        $validationRules = [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'carrier_id'   => 'required|exists:carriers,id',
            'phone'        => 'required|string|max:20',
            'ssn_tax_id'   => 'required|string|max:50',
        ];
        
        
        $validated = $request->validate($validationRules);

        // Validar que carrier pertence ao tenant
        $carrier = Carrier::find($validated['carrier_id']);
        if (!$carrier) {
            return redirect()->back()
                ->withErrors(['carrier_id' => 'Carrier não encontrado.'])
                ->withInput();
        }
        
        // Verificar se carrier pertence ao dispatcher do owner
        $ownerDispatcher = Dispatcher::where('owner_id', $ownerId)
            ->where('is_owner', true)
            ->first();
        
        if (!$ownerDispatcher || $carrier->dispatcher_id != $ownerDispatcher->id) {
            // Verificar se pertence a algum dispatcher do tenant
            $tenantDispatchers = Dispatcher::where('owner_id', $ownerId)->pluck('id');
            if (!in_array($carrier->dispatcher_id, $tenantDispatchers->toArray())) {
                return redirect()->back()
                    ->withErrors(['carrier_id' => 'Carrier não pertence ao seu tenant.'])
                    ->withInput();
            }
        }

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
            $userLimitCheck = $billingService->checkUserLimit($authUser, 'driver');

            if (!$userLimitCheck['allowed']) {
                // Se sugerir upgrade, redirecionar para montar plano
                if ($userLimitCheck['suggest_upgrade'] ?? false) {
                    // ⭐ NOVO: Armazenar URL de origem para retornar após pagamento
                    session(['return_url_after_payment' => route('drivers.create')]);
                    
                    return redirect()->route('subscription.build-plan')
                        ->with('error', $userLimitCheck['message']);
                }
                
                return redirect()->back()
                    ->withErrors(['error' => $userLimitCheck['message']])
                    ->withInput();
            }

            // Cria o usuário vinculado ao owner
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($plainPassword),
                'must_change_password' => true,
                'email_verified_at' => now(),
                'owner_id' => $ownerId, // Vincular ao owner do tenant
                'is_owner' => false,
                'is_subowner' => false,
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
        // ⭐ CORRIGIDO: Buscar driver com relacionamentos corretos
        $driver = Driver::with(['carrier.dispatcher.user'])->findOrFail($id);
        
        // ⭐ CORRIGIDO: Buscar user através do email se não estiver carregado
        if (!$driver->relationLoaded('user') || !$driver->user) {
            $driver->user = User::where('email', $driver->email)->first();
        }

        return view('carrier.driver.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // ⭐ CORRIGIDO: Buscar driver com relacionamentos e filtrar pelo tenant
        $driver = Driver::with(['carrier.dispatcher.user'])
            ->findOrFail($id);
        
        // ⭐ CORRIGIDO: Buscar user através do email se não estiver carregado
        if (!$driver->relationLoaded('user') || !$driver->user) {
            $driver->user = User::where('email', $driver->email)->first();
        }
        
        // ⭐ CORRIGIDO: Buscar apenas carriers do tenant do usuário logado
        $authUser = Auth::user();
        $ownerId = $authUser->getOwnerId();
        
        // Buscar dispatchers do tenant
        $tenantDispatchers = Dispatcher::where('owner_id', $ownerId)->pluck('id');
        
        // Buscar carriers vinculados aos dispatchers do tenant
        $carriers = Carrier::with('user')
            ->whereIn('dispatcher_id', $tenantDispatchers)
            ->get();

        return view('carrier.driver.edit', compact('driver', 'carriers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // ⭐ CORRIGIDO: Buscar driver com relacionamentos corretos
        $driver = Driver::with(['carrier.dispatcher.user'])->findOrFail($id);
        
        // ⭐ CORRIGIDO: Buscar user através do email (Driver não tem user_id direto)
        $user = User::where('email', $driver->email)->first();
        
        if (!$user) {
            return redirect()->back()
                ->withErrors(['error' => 'Usuário associado ao driver não encontrado.'])
                ->withInput();
        }

        // Validação dos dados
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => "required|email|unique:users,email,{$user->id}",
            'password'     => 'nullable|string|min:8|confirmed',
            'carrier_id'   => 'required|exists:carriers,id',
            'phone'        => 'required|string|max:20',
            'ssn_tax_id'   => 'required|string|max:50',
        ]);
        
        // ⭐ CORRIGIDO: Validar que carrier pertence ao tenant
        $authUser = Auth::user();
        $ownerId = $authUser->getOwnerId();
        $carrier = Carrier::find($validated['carrier_id']);
        
        if (!$carrier) {
            return redirect()->back()
                ->withErrors(['carrier_id' => 'Carrier não encontrado.'])
                ->withInput();
        }
        
        // Verificar se carrier pertence ao tenant
        $tenantDispatchers = Dispatcher::where('owner_id', $ownerId)->pluck('id');
        if (!in_array($carrier->dispatcher_id, $tenantDispatchers->toArray())) {
            return redirect()->back()
                ->withErrors(['carrier_id' => 'Carrier não pertence ao seu tenant.'])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Atualiza dados do usuário
            $user->update([
                'name'  => $validated['name'],
                'email' => $validated['email'],
                // Atualiza senha somente se preenchida
                'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
            ]);

            // Atualiza dados do driver
            $driver->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
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
        // ⭐ CORRIGIDO: Buscar driver e user através do email
        $driver = Driver::findOrFail($id);
        $user = User::where('email', $driver->email)->first();

        try {
            DB::beginTransaction();

            // Remove o driver e o usuário associado (se existir)
            $driver->delete();
            if ($user) {
                $user->delete();
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
