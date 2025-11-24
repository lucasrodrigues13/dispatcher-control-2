<?php

namespace App\Http\Controllers;

use App\Models\Dispatcher;
use App\Models\RolesUsers;
use App\Models\User;
use App\Models\Role;
use App\Models\UsageTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Providers\RouteServiceProvider;
use App\Services\BillingService;
use Illuminate\Auth\Events\Registered;
use App\Mail\NewCarrierCredentialsMail;

class DispatcherController extends Controller
{
    // Lista com paginação
    public function index()
    {
        // ⭐ CORRIGIDO: Removido filtro manual - TenantScope já filtra automaticamente
        // Se admin estiver visualizando um tenant específico, o scope filtra por owner_id
        // Se admin estiver visualizando "All", o scope não aplica filtro
        // Se usuário normal, o scope filtra pelo owner_id dele
        $dispatchers = Dispatcher::with('user')
            ->paginate(10);
        return view('dispatcher.self.index', compact('dispatchers'));
    }

    // Form de criação
    public function create()
    {
        // ⭐ NOVO: Se for admin, passar lista de owners disponíveis
        $owners = Auth::user()->isAdmin() ? User::getAvailableOwners() : collect();
        
        return view('dispatcher.self.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:Individual,Company',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6',
            'ssn_itin' => 'nullable|string|max:20',
            'ein_tax_id' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ], [
            'email.unique' => 'This email already exists...',
            'email.required' => 'The email field cannot be empty...',
            'email.email' => 'The email must be a valid email address...',
            'name.required' => 'The name field cannot be empty...',
        ]);

        $userName = $request->input('name') ?: $request->input('company_name');
        
        // Marcar usuário como owner se for registro público (auth_register)
        $isOwner = $request->register_type === "auth_register";
        
        $user = User::create([
            'name'     => $userName,
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'is_owner' => $isOwner,
            'owner_id' => null, // Owner não tem owner
        ]);

        // Desativado por enquanto
        // $usageLimit = UsageTracking::create([
        //     'user_id'         => $user->id,
        //     'year'            => now()->year,
        //     'month'           => now()->month,
        //     'week'            => now()->weekOfYear,
        //     'loads_count'     => 0,
        //     'carriers_count'  => 0,
        //     'employees_count' => 0,
        //     'drivers_count'   => 0,
        //     'created_at'      => now(),
        //     'updated_at'      => now(),
        // ]);

        $dispatcher = Dispatcher::create([
            'user_id'      => $user->id,
            'owner_id'     => $isOwner ? $user->id : null, // Owner aponta para si mesmo
            'is_owner'     => $isOwner,
            'type'         => $request->input('type'),
            'company_name' => $request->input('company_name'),
            'ssn_itin'     => $request->input('ssn_itin'),
            'ein_tax_id'   => $request->input('ein_tax_id'),
            'address'      => $request->input('address'),
            'city'         => $request->input('city'),
            'state'        => $request->input('state'),
            'zip_code'     => $request->input('zip_code'),
            'country'      => $request->input('country'),
            'notes'        => $request->input('notes'),
            'phone'        => $request->input('phone'),
            'departament'  => $request->input('departament'),
        ]);

        $billingService = app(BillingService::class);
        $billingService->createTrialSubscription($user);
        // $usageCheck = $billingService->checkUsageLimits(Auth::user());

        // if (!$usageCheck['allowed']) {
        //     if (!empty($usageCheck['extra_charge'])) {
        //         // Lógica para cobrar $10 (exemplo fictício)
        //         // $stripeService->charge(Auth::user(), 10.00, 'Adicional Carrier');
        //         // Ou exibe mensagem para pagamento
        //         return back()->with('error', 'Limite atingido! Pague $10 para adicionar um novo Carrier ou faça upgrade para o plano premium.');
        //     }
        //     return back()->with('error', $usageCheck['message']);
        // }

        // ⭐ CORRIGIDO: Buscar ou criar role Dispatcher e atribuir ao usuário
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'Dispatcher'],
            ['description' => 'Despachante que gerencia cargas e transportadoras']
        );

        // Verificar se o usuário já tem essa role antes de atribuir
        if (!$user->roles()->where('roles.id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
        }

        if ($request->register_type === "auth_register") {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                Log::warning('Falha ao enviar email de verificação', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
            Auth::login($user);
            
            // Regenerar sessão após login para segurança
            $request->session()->regenerate();
            
            return response()->json([
                'success' => true, 
                'message' => 'User created successfully.',
                'redirect_url' => url('/verify-email')
            ]);
        }

        return redirect()
            ->route('dispatchers.create')
            ->with('success', "Dispatcher created successfully.")
            ->with('created_user_id', $user->id);
    }

    public function storeFromDashboard(Request $request)
    {
        $authUser = Auth::user();
        
        // ⭐ NOVO: Se for admin, usar owner_id do request; senão, usar getOwnerId()
        if ($authUser->isAdmin()) {
            $rules = [
                'owner_id' => 'required|exists:users,id',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            $ownerId = $request->owner_id;
            
            // Validar que o owner_id selecionado é realmente um owner (não admin)
            $selectedOwner = User::find($ownerId);
            if (!$selectedOwner || !$selectedOwner->is_owner || $selectedOwner->is_admin) {
                return redirect()->back()
                    ->withErrors(['owner_id' => 'Owner selecionado é inválido.'])
                    ->withInput();
            }
        } else {
            // Obter owner do tenant (comportamento normal)
            $ownerId = $authUser->getOwnerId();
            
            // Validar permissão
            if (!$authUser->canManageTenant()) {
                return redirect()->back()
                    ->withErrors(['error' => 'Você não tem permissão para criar este registro.'])
                    ->withInput();
            }
        }
        
        // Validação condicional baseada no tipo
        $rules = [
            'type' => 'required|in:Individual,Company',
            'email' => 'required|email|unique:users,email',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ];

        // Validação condicional baseada no tipo
        if ($request->type === 'Individual') {
            $rules['name'] = 'required|string|max:255';
            $rules['ssn_itin'] = 'required|string|max:20';
            // Company fields são nullable para Individual
            $rules['company_name'] = 'nullable|string|max:255';
            $rules['ein_tax_id'] = 'nullable|string|max:20';
            $rules['departament'] = 'nullable|string|max:255';
        } elseif ($request->type === 'Company') {
            $rules['company_name'] = 'required|string|max:255';
            $rules['ein_tax_id'] = 'required|string|max:20';
            $rules['departament'] = 'required|string|max:255';
            // Individual fields são nullable para Company
            $rules['name'] = 'nullable|string|max:255';
            $rules['ssn_itin'] = 'nullable|string|max:20';
        }

        $validator = Validator::make($request->all(), $rules, [
            'email.unique' => 'This email already exists...',
            'type.required' => 'Please select a dispatcher type.',
            'name.required' => 'Name is required for Individual type.',
            'company_name.required' => 'Company name is required for Company type.',
            'ssn_itin.required' => 'SSN/ITIN is required for Individual type.',
            'ein_tax_id.required' => 'EIN/Tax ID is required for Company type.',
            'departament.required' => 'Department is required for Company type.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // ⭐ NOVO: Verificar limite de dispatchers antes de criar
        // Se for admin, verificar limite do owner selecionado; senão, do usuário logado
        $billingService = app(BillingService::class);
        $userToCheck = $authUser->isAdmin() ? User::find($ownerId) : $authUser;
        $userLimitCheck = $billingService->checkUserLimit($userToCheck, 'dispatcher');

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

        // Gera senha automática baseada no nome ou company_name
        $nameForPassword = $request->type === 'Individual'
            ? $request->input('name')
            : $request->input('company_name');

        $base = \Illuminate\Support\Str::of($nameForPassword)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '');
        $plainPassword = (string) $base.'2025';

        // Cria o usuário
        $userName = $request->type === 'Individual'
            ? $request->input('name')
            : $request->input('company_name');

        $user = User::create([
            'name'     => $userName,
            'email'    => $request->input('email'),
            'password' => Hash::make($plainPassword),
            'must_change_password' => true,
            'email_verified_at' => now(),
            'owner_id' => $ownerId, // Vincular ao owner do tenant
            'is_owner' => false, // Não é owner, é dispatcher dentro do tenant
                'is_subowner' => false,
        ]);

        // Cria o dispatcher (não-owner dentro do tenant)
        $dispatcher = Dispatcher::create([
            'user_id'      => $user->id,
            'owner_id'     => $ownerId, // Vincular ao owner
            'is_owner'     => false, // Não é owner
            'type'         => $request->input('type'),
            'company_name' => $request->input('company_name'),
            'ssn_itin'     => $request->input('ssn_itin'),
            'ein_tax_id'   => $request->input('ein_tax_id'),
            'address'      => $request->input('address'),
            'city'         => $request->input('city'),
            'state'        => $request->input('state'),
            'zip_code'     => $request->input('zip_code'),
            'country'      => $request->input('country'),
            'notes'        => $request->input('notes'),
            'phone'        => $request->input('phone'),
            'departament'  => $request->input('departament'),
        ]);

        // ⭐ CORRIGIDO: Buscar ou criar role Dispatcher e atribuir ao usuário
        $role = Role::firstOrCreate(
            ['name' => 'Dispatcher'],
            ['description' => 'Despachante que gerencia cargas e transportadoras']
        );

        // Verificar se o usuário já tem essa role antes de atribuir
        if (!$user->roles()->where('roles.id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
        }

        // Cria subscription de trial
        $billingService = app(BillingService::class);
        $billingService->createTrialSubscription($user);

        // Envia email com credenciais (com tratamento de erro para não quebrar o fluxo)
        try {
            Mail::to($user->email)->queue(new NewCarrierCredentialsMail($user, $plainPassword));
        } catch (\Exception $e) {
            Log::warning('Falha ao enviar email de credenciais', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            // Não interrompe o fluxo - o dispatcher foi criado com sucesso
        }

        return redirect()
            ->route('dispatchers.index')
            ->with('success', 'Dispatcher criado com sucesso; credenciais enviadas por e-mail.');
    }
    // Detalhes
    public function show(string $id)
    {
        $dispatcher = Dispatcher::with('user')->findOrFail($id);
        return view('dispatcher.self.show', compact('dispatcher'));
    }

    // Form de edição
    public function edit(string $id)
    {
        $dispatcher = Dispatcher::with('user')->findOrFail($id);
        return view('dispatcher.self.edit', compact('dispatcher'));
    }

    // Atualiza dispatcher + user
    public function update(Request $request, $id)
    {
        $dispatcher = Dispatcher::findOrFail($id);
        $user = $dispatcher->user;

        // Atualiza o nome do usuário com base no tipo
        $userName = $request->input('name') ?: $request->input('company_name');

        // Atualiza os dados do usuário
        $user->name = $userName;
        $user->email = $request->input('email');
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $user->save();

        // Atualiza os dados do dispatcher
        $dispatcher->update([
            'type'         => $request->input('type'),
            'company_name' => $request->input('company_name'),
            'ssn_itin'     => $request->input('ssn_itin'),
            'ein_tax_id'   => $request->input('ein_tax_id'),
            'address'      => $request->input('address'),
            'city'         => $request->input('city'),
            'state'        => $request->input('state'),
            'zip_code'     => $request->input('zip_code'),
            'country'      => $request->input('country'),
            'notes'        => $request->input('notes'),
            'phone'        => $request->input('phone'),
            'departament'  => $request->input('departament'),
        ]);

        return redirect()->route('dispatchers.index')->with('success', 'Dispatcher updated successfully.');
    }

    public function destroy(string $id)
    {
        $dispatcher = Dispatcher::findOrFail($id);
        $dispatcher->delete();

        return redirect()->route('dispatchers.index')->with('success', 'Dispatcher removido com sucesso.');
    }
}
