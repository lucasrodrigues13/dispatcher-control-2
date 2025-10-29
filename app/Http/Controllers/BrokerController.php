<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\RolesUsers;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\BillingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BrokerController extends Controller
{
    public function index()
    {
        $brokers = Broker::with('user')->paginate(10);
        return view('broker.index', compact('brokers'));
    }

    public function create()
    {
        return view('broker.create');
    }

    public function store(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            // adicione outras validações se quiser
        ], [
            'email.unique' => 'This email already exists...',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validação dos dados
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'license_number' => 'nullable|string',
            'company_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'accounting_email' => 'nullable|email',
            'accounting_phone_number' => 'nullable|string',
            'fee_percent' => 'nullable|numeric|min:0|max:100',
            'payment_terms' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        // Criar o usuário
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Criar o broker com user_id
        Broker::create([
            'user_id' => $user->id,
            'license_number' => $validated['license_number'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'accounting_email' => $validated['accounting_email'] ?? null,
            'accounting_phone_number' => $validated['accounting_phone_number'] ?? null,
            'fee_percent' => $validated['fee_percent'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
        ]);


        // Criar assinatura trial automática
        $billingService = app(BillingService::class);
        $billingService->createTrialSubscription($user);

        $role = DB::table('roles')->where('name', 'Broker')->first();

        $roles = new RolesUsers();
        $roles->user_id = $user->id;
        $roles->role_id = $role->id;

        $roles->save();

        // Se veio do fluxo de registro via auth, dispara evento e faz login
        if ($request->register_type === "auth_register") {
            event(new Registered($user));
            Auth::login($user);
            return redirect(RouteServiceProvider::HOME);
        }

        return redirect()->route('brokers.index')->with('success', 'Broker created successfully.');
    }

    public function show($id)
    {
        $broker = Broker::with('user')->findOrFail($id);
        return view('broker.show', compact('broker'));
    }

    public function edit($id)
    {
        $broker = Broker::with('user')->findOrFail($id);
        return view('broker.edit', compact('broker'));
    }

    public function update(Request $request, $id)
    {
        $broker = Broker::findOrFail($id);
        $user = $broker->user;

        // Validação
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'password' => 'nullable|string|min:6|confirmed',
            'license_number' => 'nullable|string',
            'company_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'accounting_email' => 'nullable|email',
            'accounting_phone_number' => 'nullable|string',
            'fee_percent' => 'nullable|numeric|min:0|max:100',
            'payment_terms' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        // Atualiza o usuário
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => !empty($validated['password']) ? Hash::make($validated['password']) : $user->password,
        ]);

        // Atualiza o broker
        $broker->update([
            'license_number' => $validated['license_number'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'accounting_email' => $validated['accounting_email'] ?? null,
            'accounting_phone_number' => $validated['accounting_phone_number'] ?? null,
            'fee_percent' => $validated['fee_percent'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
        ]);

        return redirect()->route('brokers.index')->with('success', 'Broker updated successfully.');
    }

    public function destroy($id)
    {
        $broker = Broker::findOrFail($id);
        $user = $broker->user;

        $broker->delete();
        $user->delete();

        return redirect()->route('brokers.index')->with('success', 'Broker deleted successfully.');
    }
}
