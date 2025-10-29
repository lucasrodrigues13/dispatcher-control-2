<?php

namespace App\Http\Controllers;

use App\Models\Dispatcher;
use App\Models\RolesUsers;
// use App\Models\Carrier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Providers\RouteServiceProvider;
use App\Services\BillingService;
use Illuminate\Auth\Events\Registered;

class DispatcherController extends Controller
{
    // Lista todos os dispatchers com paginação
    public function index()
    {
        $dispatchers = Dispatcher::with('user')->paginate(10);
        return view('dispatcher.self.index', compact('dispatchers'));
    }


    // Mostra o formulário para criar um novo dispatcher
    public function create()
    {
        return view('dispatcher.self.create');
    }


   public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email|unique:users,email',
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

    // Cria o usuário
    $userName = $request->input('name') ?: $request->input('company_name');
    $user = User::create([
        'name'     => $userName,
        'email'    => $request->input('email'),
        'password' => Hash::make($request->input('password')),
    ]);

    // Cria o dispatcher e guarda na variável
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

      // Criar assinatura trial automática
        $billingService = app(BillingService::class);
        $billingService->createTrialSubscription($user);

        $role = DB::table('roles')->where('name', 'Dispatcher')->first();

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



    // Caso padrão, redireciona para a lista de dispatchers
    return redirect()
        ->route('dispatchers.index')
        ->with('success', 'Dispatcher criado com sucesso.');
}





    // Mostra os detalhes de um dispatcher
    public function show(string $id)
    {
        $dispatcher = Dispatcher::with('user')->findOrFail($id);
        return view('dispatcher.self.show', compact('dispatcher'));
    }

    public function edit(string $id)
    {
        $dispatcher = Dispatcher::with('user')->findOrFail($id);
        return view('dispatcher.self.edit', compact('dispatcher'));
    }

    // Atualiza um dispatcher
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

    // Remove um dispatcher
    public function destroy(string $id)
    {
        $dispatcher = Dispatcher::findOrFail($id);
        $dispatcher->delete();

        return redirect()->route('dispatchers.index')->with('success', 'Dispatcher removido com sucesso.');
    }
}
