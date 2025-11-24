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

        return view('dispatcher.employee.index', compact('employeers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // ⭐ VALIDAÇÃO PRIMEIRO: Verificar limite ANTES de qualquer coisa
        $billingService = app(BillingService::class);
        $userLimitCheck = $billingService->checkUserLimit(Auth::user(), 'employee');

        // Se não tiver permissão, SEMPRE redirecionar ANTES de mostrar formulário
        if (!($userLimitCheck['allowed'] ?? false)) {
            // Se sugerir upgrade, redirecionar para tela de planos
            if ($userLimitCheck['suggest_upgrade'] ?? false) {
                return redirect()->route('subscription.build-plan')
                    ->with('error', $userLimitCheck['message'] ?? 'Limite atingido. Faça upgrade do seu plano.');
            }
            
            // Caso contrário, voltar para lista com erro
            return redirect()->route('employees.index')
                ->with('error', $userLimitCheck['message'] ?? 'Você não tem permissão para criar employees.');
        }

        // Se chegou aqui, tem permissão - buscar dados para o formulário
        $dispatchers = Dispatcher::with('user')
            ->where('user_id', auth()->id())
            ->first();

        // ⭐ NOVO: Se for admin, passar lista de owners disponíveis
        $owners = Auth::user()->isAdmin() ? User::getAvailableOwners() : collect();

        // Verificar se deve mostrar modal de upgrade (caso ainda tenha permissão mas esteja próximo do limite)
        $usageCheck = $userLimitCheck; // Reutilizar o mesmo resultado
        $showUpgradeModal = false;
        
        // Se permitido mas com warning, mostrar modal
        if (($usageCheck['allowed'] ?? true) && ($usageCheck['warning'] ?? false)) {
            $showUpgradeModal = true;
        }

        return view('dispatcher.employee.create', compact('dispatchers', 'showUpgradeModal', 'usageCheck', 'owners'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1) Validação dos dados
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'dispatcher_id' => 'required|exists:dispatchers,id',
            'phone' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'ssn_tax_id' => 'nullable|string|max:255',
        ];
        
        // ⭐ NOVO: Se for admin, adicionar validação de owner_id
        if (Auth::user()->isAdmin()) {
            $validationRules['owner_id'] = 'required|exists:users,id';
        }
        
        $validator = Validator::make($request->all(), $validationRules, [
            'email.unique' => 'This email already exists...',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // ⭐ NOVO: Se for admin, usar owner_id do request; senão, usar getOwnerId()
        $authUser = Auth::user();
        $targetOwnerId = $authUser->getOwnerId(); // Default para tenant atual
        
        if ($authUser->isAdmin() && $request->filled('owner_id')) {
            // Se admin e owner_id fornecido, validar e usar
            $selectedOwner = User::find($request->owner_id);
            if ($selectedOwner && $selectedOwner->isOwner() && !$selectedOwner->isAdmin()) {
                $targetOwnerId = $selectedOwner->id;
            } else {
                return redirect()->back()
                    ->withErrors(['owner_id' => 'Owner selecionado é inválido.'])
                    ->withInput();
            }
        }
        
        // Validar permissão
        if (!$authUser->canManageTenant()) {
            return redirect()->back()
                ->withErrors(['error' => 'Você não tem permissão para criar este registro.'])
                ->withInput();
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
        
        // Validar que dispatcher_id do request pertence ao owner (se fornecido)
        if ($request->dispatcher_id && $request->dispatcher_id != $ownerDispatcher->id) {
            // Verificar se é um dispatcher não-owner do mesmo tenant
            $requestDispatcher = Dispatcher::find($request->dispatcher_id);
            if (!$requestDispatcher || $requestDispatcher->owner_id != $ownerId) {
                return redirect()->back()
                    ->withErrors(['dispatcher_id' => 'Dispatcher inválido ou não pertence ao seu tenant.'])
                    ->withInput();
            }
        }
        
        // ⭐ NOVO: Verificar limite de usuários antes de criar employee
        $billingService = app(BillingService::class);
        $userLimitCheck = $billingService->checkUserLimit($authUser, 'employee');

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
            'dispatcher_id' => $request->dispatcher_id ?? $ownerDispatcher->id, // Usar dispatcher do request ou owner
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
        return view('dispatcher.employee.edit', compact('employee', 'dispatchers'));
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

