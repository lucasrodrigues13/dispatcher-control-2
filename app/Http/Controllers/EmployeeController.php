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
            $employeers = Employee::with('dispatcher.user')
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
            'email' => 'required|email|unique:employees,email',
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

        // ⭐ NOVO: Verificar limite de usuários antes de criar employee
        $billingService = app(BillingService::class);
        $userLimitCheck = $billingService->checkUserLimit(Auth::user(), 'employee');

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

        Employee::create([
            'dispatcher_id' => $request->dispatcher_id,
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone ?? null,
            'position'      => $request->position ?? null,
            'ssn_tax_id'    => $request->ssn_tax_id ?? null,
        ]);

        app(UsageTrackingRepository::class)->incrementUsage(Auth::user(), 'employee');

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $employee    = Employee::findOrFail($id);
        $dispatchers = Dispatcher::with('user')->get();
        return view('dispatcher.employeer.edit', compact('employee', 'dispatchers'));
    }

    public function getEmployee($id) {
        $employees = Employee::where("dispatcher_id", $id)->get();

        return response()->json($employees);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        // Validação
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => "required|email|unique:employees,email,{$employee->id}",
            'dispatcher_id'         => 'required|exists:dispatchers,id',
            'phone'                 => 'nullable|string|max:255',
            'position'              => 'nullable|string|max:255',
            'ssn_tax_id'            => 'nullable|string|max:255',
        ]);

        // Atualiza employee
        $employee->update([
            'name'          => $data['name'],
            'email'         => $data['email'],
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
        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee removido com sucesso.');
    }
}

