<?php

namespace App\Http\Controllers;

use App\Models\Dispatcher;
use App\Models\RolesUsers;
use App\Models\User;
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
        $dispatchers = Dispatcher::with('user')
            ->where('user_id', Auth::id()) // Filtra pelo usuário logado
            ->paginate(10);
        return view('dispatcher.self.index', compact('dispatchers'));
    }

    // Form de criação
    public function create()
    {
        return view('dispatcher.self.create');
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
        $user = User::create([
            'name'     => $userName,
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
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

        $role = DB::table('roles')->where('name', 'Dispatcher')->first();

        $roles = new RolesUsers();
        $roles->user_id = $user->id;
        $roles->role_id = $role->id;
        $roles->save();

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
            return response()->json(['success' => true, 'message' => 'User created successfully.']);
        }

        return redirect()
            ->route('dispatchers.create')
            ->with('success', "Dispatcher created successfully.")
            ->with('created_user_id', $user->id);
    }

    public function storeFromDashboard(Request $request)
    {
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
        ]);

        // Cria o dispatcher
        $dispatcher = Dispatcher::create([
            'user_id'      => $user->id,
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

        // Atribui role
        $role = DB::table('roles')->where('name', 'Dispatcher')->first();
        $roles = new RolesUsers();
        $roles->user_id = $user->id;
        $roles->role_id = $role->id;
        $roles->save();

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
