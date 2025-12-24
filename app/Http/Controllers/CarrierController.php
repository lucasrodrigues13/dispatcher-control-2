<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\User;
use App\Models\Dispatcher;
use App\Models\RolesUsers;
use App\Repositories\UsageTrackingRepository;
use App\Providers\RouteServiceProvider;
use App\Services\BillingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\NewCarrierCredentialsMail;
use App\Http\Controllers\Traits\ToggleUserStatus;

class CarrierController extends Controller
{
    use ToggleUserStatus;
    public function index()
    {
        // Busca o dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();

        // Se não existir dispatcher, retorna paginação vazia
        if (!$dispatcher) {
            $carriers = Carrier::whereRaw('1 = 0')->paginate(10);
        } else {
            // Filtra os carriers pelo dispatcher_id
            $carriers = Carrier::with(['dispatchers.user', 'user'])
                ->where('dispatcher_id', $dispatcher->id)
                ->paginate(10);
        }

        return view('carrier.self.index', compact('carriers'));
    }

    // Mostra o formulário para criar um novo carrier
    public function create()
    {
        $authUser = Auth::user();
        $adminTenantService = app(\App\Services\AdminTenantService::class);
        
        // ⭐ NOVO: Se for admin, verificar se está visualizando um tenant específico
        if ($authUser->isAdmin()) {
            if ($adminTenantService->isViewingAll()) {
                return redirect()->back()
                    ->with('error', 'Please select a specific tenant from the dropdown above before creating a new user.');
            }
            
            // Get selected tenant for validation
            $viewingTenantId = $adminTenantService->getViewingTenantId();
            $viewingTenant = $viewingTenantId ? User::find($viewingTenantId) : null;
            
            if (!$viewingTenant) {
                return redirect()->back()
                    ->with('error', 'Selected tenant not found. Please select a valid tenant.');
            }
            
            // Usar o tenant selecionado para validação
            $userForValidation = $viewingTenant;
        } else {
            $userForValidation = $authUser;
        }
        
        // ⭐ VALIDAÇÃO PRIMEIRO: Verificar limite ANTES de qualquer coisa
        $billingService = app(BillingService::class);
        $userLimitCheck = $billingService->checkUserLimit($userForValidation, 'carrier');

        // Se não tiver permissão, SEMPRE redirecionar ANTES de mostrar formulário
        if (!($userLimitCheck['allowed'] ?? false)) {
            // Se sugerir upgrade, redirecionar para tela de planos
            if ($userLimitCheck['suggest_upgrade'] ?? false) {
                // ⭐ NOVO: Armazenar URL de origem para retornar após pagamento
                session(['return_url_after_payment' => route('carriers.create')]);
                
                return redirect()->route('subscription.build-plan')
                    ->with('error', $userLimitCheck['message'] ?? 'Limite atingido. Faça upgrade do seu plano.');
            }
            
            // Caso contrário, voltar para lista com erro
            return redirect()->route('carriers.index')
                ->with('error', $userLimitCheck['message'] ?? 'Você não tem permissão para criar carriers.');
        }

        $dispatchers = Dispatcher::with('user')
            ->where('user_id', auth()->id())
            ->first();

        // Verificar se deve mostrar modal de upgrade (caso ainda tenha permissão mas esteja próximo do limite)
        $usageCheck = $userLimitCheck; // Reutilizar o mesmo resultado
        $showUpgradeModal = false;
        
        // Se permitido mas com warning, mostrar modal
        if (($usageCheck['allowed'] ?? true) && ($usageCheck['warning'] ?? false)) {
            $showUpgradeModal = true;
        }

        return view('carrier.self.create', compact('dispatchers', 'showUpgradeModal', 'usageCheck'));
    }

    public function store(Request $request)
    {
        // 1) Validação (só roda se passou no usage)
        $validationRules = [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'contact_phone'=> 'nullable|string|max:20',
            'address'      => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'zip'          => 'nullable|string|max:20',
            'country'      => 'nullable|string|max:100',
            'mc'           => 'nullable|string|max:50',
            'dot'          => 'nullable|string|max:50',
            'ein'          => 'nullable|string|max:50',
            'about'        => 'nullable|string',
            'website'      => 'nullable|string|max:255',
            'trailer_capacity' => 'nullable|integer',
            'is_auto_hauler'   => 'nullable|boolean',
            'is_towing'        => 'nullable|boolean',
            'is_driveaway'     => 'nullable|boolean',
            'dispatcher_id' => 'nullable|exists:dispatchers,id',
        ];
        
        
        $validated = $request->validate($validationRules);

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
        
        // Obter dispatcher do owner
        $ownerDispatcher = Dispatcher::where('owner_id', $ownerId)
            ->where('is_owner', true)
            ->first();
        
        if (!$ownerDispatcher) {
            return redirect()->back()
                ->withErrors(['error' => 'Dispatcher owner não encontrado.'])
                ->withInput();
        }
        
        // Sempre usar dispatcher do owner (ignorar dispatcher_id do request se fornecido)
        $validated['dispatcher_id'] = $ownerDispatcher->id;

        $base = \Illuminate\Support\Str::of($validated['name'])->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '');
        $plainPassword = (string) $base.'2025';

        try {
            DB::beginTransaction();

            // Cria usuário
            // ⭐ NOVO: Verificar limite de usuários antes de criar carrier
            $billingService = app(BillingService::class);
            $userLimitCheck = $billingService->checkUserLimit(Auth::user(), 'carrier');

            if (!$userLimitCheck['allowed']) {
                // Se sugerir upgrade, redirecionar para montar plano
                if ($userLimitCheck['suggest_upgrade'] ?? false) {
                    // ⭐ NOVO: Armazenar URL de origem para retornar após pagamento
                    session(['return_url_after_payment' => route('carriers.create')]);
                    
                    return redirect()->route('subscription.build-plan')
                        ->with('error', $userLimitCheck['message']);
                }
                
                return redirect()->back()
                    ->withErrors(['error' => $userLimitCheck['message']])
                    ->withInput();
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($plainPassword),
                'must_change_password' => true,
                'email_verified_at' => now(),
                'owner_id' => $ownerId, // Vincular ao owner do tenant
                'is_owner' => false,
                'is_subowner' => false,
            ]);

            // Cria carrier
            $carrier = Carrier::create([
                'user_id'          => $user->id,
                'company_name'     => $validated['company_name'],
                'phone'            => $validated['phone'],
                'contact_name'     => $validated['contact_name'] ?? null,
                'about'            => $validated['about'] ?? null,
                'website'          => $validated['website'] ?? null,
                'trailer_capacity' => $validated['trailer_capacity'] ?? null,
                'is_auto_hauler'   => (bool) ($validated['is_auto_hauler'] ?? false),
                'is_towing'        => (bool) ($validated['is_towing'] ?? false),
                'is_driveaway'     => (bool) ($validated['is_driveaway'] ?? false),
                'contact_phone'    => $validated['contact_phone'] ?? null,
                'address'          => $validated['address'],
                'city'             => $validated['city'] ?? null,
                'state'            => $validated['state'] ?? null,
                'zip'              => $validated['zip'] ?? null,
                'country'          => $validated['country'] ?? null,
                'mc'               => $validated['mc'] ?? null,
                'dot'              => $validated['dot'] ?? null,
                'ein'              => $validated['ein'] ?? null,
                'dispatcher_id' => $validated['dispatcher_id'],
            ]);

            // Contabiliza uso
            app(UsageTrackingRepository::class)->incrementUsage(Auth::user(), 'carrier');

            // Role "Carrier"
            $role = DB::table('roles')->where('name', 'Carrier')->first();
            if ($role) {
                $roles = new RolesUsers();
                $roles->user_id = $user->id;
                $roles->role_id = $role->id;
                $roles->save();
            }

            // ⭐ CORRIGIDO: Carriers são sub-usuários do Dispatcher
            // A subscription está no Dispatcher principal, não no Carrier
            // Não criar subscription para Carrier

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao criar carrier', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar carrier: ' . $e->getMessage()])
                ->withInput();
        }

        // 9) E-mail (com tratamento de erro para não quebrar o fluxo)
        try {
            Mail::to($user->email)->queue(new NewCarrierCredentialsMail($user, $plainPassword));
        } catch (\Exception $e) {
            Log::warning('Falha ao enviar email de credenciais', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            // Não interrompe o fluxo - o carrier foi criado com sucesso
        }

        // 10) Fluxo opcional
        if ($request->register_type === "auth_register") {
            event(new Registered($user));
            Auth::login($user);
            return redirect(RouteServiceProvider::HOME);
        }

        return redirect()
            ->route('carriers.index')
            ->with('success', 'Carrier e usuário criados com sucesso; credenciais enviadas por e-mail.');
    }

    // Resto dos métodos permanecem iguais...
    public function show(string $id)
    {
        $carrier = Carrier::with(['user', 'dispatcher'])->findOrFail($id);
        return view('carrier.self.show', compact('carrier'));
    }

    public function edit(string $id)
    {
        // Carrega o carrier + empresa de dispatcher + usuário do dispatcher
        $carrier = Carrier::with('dispatchers.user')->findOrFail($id);

        // Lista todas as empresas de dispatcher com o usuário carregado
        $dispatchers = Dispatcher::with('user')->get();

        return view('carrier.self.edit', compact('carrier', 'dispatchers'));
    }

    public function update(Request $request, $id)
    {
        $carrier = Carrier::with('user')->findOrFail($id);

        $validatedData = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'about' => 'nullable|string|max:1000',
            'trailer_capacity' => 'nullable|integer|min:0',
            'is_auto_hauler' => 'nullable|boolean',
            'is_towing' => 'nullable|boolean',
            'is_driveaway' => 'nullable|boolean',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'mc' => 'nullable|string|max:50',
            'dot' => 'nullable|string|max:50',
            'ein' => 'nullable|string|max:50',
            'dispatcher_id' => 'required|exists:dispatchers,id',

            // Dados do usuário
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (!$carrier->user) {
            return back()->withErrors(['user' => 'Usuário associado não encontrado.']);
        }

        try {
            DB::beginTransaction();

            // Atualiza dados do usuário
            $user = $carrier->user;
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];

            if (!empty($validatedData['password'])) {
                $user->password = Hash::make($validatedData['password']);
            }

            $user->save();

            // Atualiza o carrier
            $carrier->update([
                'company_name' => $validatedData['company_name'] ?? $carrier->company_name,
                'phone' => $validatedData['phone'],
                'contact_name' => $validatedData['contact_name'] ?? $carrier->contact_name,
                'about' => $validatedData['about'] ?? $carrier->about,
                'website' => $validatedData['website'] ?? $carrier->website,
                'trailer_capacity' => $validatedData['trailer_capacity'] ?? $carrier->trailer_capacity,
                'is_auto_hauler' => $validatedData['is_auto_hauler'] ?? $carrier->is_auto_hauler,
                'is_towing' => $validatedData['is_towing'] ?? $carrier->is_towing,
                'is_driveaway' => $validatedData['is_driveaway'] ?? $carrier->is_driveaway,
                'contact_phone' => $validatedData['contact_phone'] ?? $carrier->contact_phone,
                'address' => $validatedData['address'],
                'city' => $validatedData['city'] ?? $carrier->city,
                'state' => $validatedData['state'] ?? $carrier->state,
                'zip' => $validatedData['zip'] ?? $carrier->zip,
                'country' => $validatedData['country'] ?? $carrier->country,
                'mc' => $validatedData['mc'] ?? $carrier->mc,
                'dot' => $validatedData['dot'] ?? $carrier->dot,
                'ein' => $validatedData['ein'] ?? $carrier->ein,
                'dispatcher_id' => $validatedData['dispatcher_id'],
            ]);

            // Verificar se tem assinatura unlimited, se não tiver, criar
            // $billingService = app(BillingService::class);
            // if (!$user->subscription || $user->subscription->plan->slug !== 'carrier-unlimited') {
            //     $billingService->createCarrierUnlimitedSubscription($user);
            // }

            DB::commit();

            return redirect()->route('carriers.index')->with('success', 'Carrier and user updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Erro ao atualizar carrier: ' . $e->getMessage()]);
        }
    }

    // Remove um carrier
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $carrier = Carrier::findOrFail($id);

            // Opcional: também remover o usuário associado
            if ($carrier->user) {
                $carrier->user->delete();
            }

            $carrier->delete();

            DB::commit();

            return redirect()->route('carriers.index')->with('success', 'Carrier removido com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('carriers.index')->with('error', 'Erro ao remover carrier: ' . $e->getMessage());
        }
    }
}
