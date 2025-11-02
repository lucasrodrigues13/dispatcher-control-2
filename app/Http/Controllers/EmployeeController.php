<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Dispatcher;
use App\Models\User;
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

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Busca o dispatcher do usuário logado
        $dispatchers = Dispatcher::where('user_id', Auth::id())->first();

        // Se não existir dispatcher, retorna paginação vazia
        if (!$dispatchers) {
            $employeers = Employee::whereRaw('1 = 0')->paginate(10);
        } else {
            // Filtra os employees pelo dispatcher_id
            $employeers = Employee::with('user', 'dispatcher.user')
                ->where('dispatcher_id', $dispatchers->id)
                ->paginate(10);
        }

        return view('dispatcher.employeer.index', compact('employeers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $billingService = app(BillingService::class);
        $usageCheck = $billingService->checkUsageLimits(Auth::user(), 'employee');

        $showUpgradeModal = !empty($usageCheck['suggest_upgrade']);

        $dispatchers = Dispatcher::with('user')
            ->where('user_id', auth()->id())
            ->first();

        return view('dispatcher.employeer.create', compact('dispatchers', 'showUpgradeModal', 'usageCheck'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1) Validação dos dados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'dispatcher_id' => 'required|exists:dispatchers,id',
            'phone' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'ssn_tax_id' => 'nullable|string|max:255',
        ], [
            'email.unique' => 'This email already exists...',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $base = \Illuminate\Support\Str::of($request->input('name'))
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '');
        $plainPassword = (string) $base.'2025';

        // 3) Cria o usuário
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($plainPassword), // Usa a senha gerada automaticamente
            'must_change_password' => true,
            'email_verified_at' => now(),
        ]);

        Employee::create([
            'user_id'       => $user->id,
            'dispatcher_id' => $request->dispatcher_id,
            'phone'         => $request->phone ?? null,
            'position'      => $request->position ?? null,
            'ssn_tax_id'    => $request->ssn_tax_id ?? null,
        ]);

        app(UsageTrackingRepository::class)->incrementUsage(Auth::user(), 'employee');

        Mail::to($user->email)->queue(new NewCarrierCredentialsMail($user, $plainPassword));

        if ($request->register_type === "auth_register") {
            event(new Registered($user));
            Auth::login($user);
            return redirect(RouteServiceProvider::HOME);
        }

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee e usuário criados com sucesso; credenciais enviadas por e-mail.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $employee    = Employee::with('user')->findOrFail($id);
        $dispatchers = Dispatcher::with('user')->get();
        return view('dispatcher.employeer.edit', compact('employee', 'dispatchers'));
    }

    public function getEmployee($id) {
        $employees    = Employee::with('user')->where("dispatcher_id", $id)->get();

        return response()->json($employees);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $user     = $employee->user;

        // Validação
        $data = $request->validate([
            // usuário
            'name'                  => 'required|string|max:255',
            'email'                 => "required|email|unique:users,email,{$user->id}",
            'password'              => 'nullable|string|min:8|confirmed',
            // employee
            'dispatcher_id'         => 'required|exists:dispatchers,id',
            'phone'                 => 'nullable|string|max:255',
            'position'              => 'nullable|string|max:255',
            'ssn_tax_id'            => 'nullable|string|max:255',
        ]);

        // Atualiza usuário
        $user->update([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password']
                ? Hash::make($data['password'])
                : $user->password,
        ]);

        // Atualiza employee
        $employee->update([
            'dispatcher_id' => $data['dispatcher_id'],
            'phone'         => $data['phone'] ?? null,
            'position'      => $data['position'] ?? null,
            'ssn_tax_id'    => $data['ssn_tax_id'] ?? null,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        // opcional: você pode querer deletar também o usuário associado:
        // $employee->user()->delete();
        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee removido com sucesso.');
    }
}

