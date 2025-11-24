<?php

namespace App\Http\Controllers;

use App\Models\Comission;
use App\Models\Dispatcher;
use Illuminate\Support\Facades\DB;
use App\Models\Deal;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CommissionController extends Controller
{
    /**
     * Display a listing of the commissions.
     */
    public function index()
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            $commissions = Comission::whereRaw('1 = 0')->paginate(15);
        } else {
            // Listar apenas comissões do dispatcher logado
            $commissions = Comission::with(['dispatcher.user', 'deal.carrier.user', 'employee'])
                ->where('dispatcher_id', $dispatcher->id)
                ->paginate(15);
        }

        return view('commission.index', compact('commissions'));
    }

    public function commissions($id)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            abort(403, 'Dispatcher não encontrado para este usuário.');
        }

        // Validar se o deal pertence ao dispatcher logado
        $deal = Deal::where('id', $id)
            ->where('dispatcher_id', $dispatcher->id)
            ->firstOrFail();

        // Filtra as comissões apenas do deal e dispatcher logado
        $commissions = Comission::with(['dispatcher.user', 'deal.carrier.user', 'employee.dispatcher.user'])
            ->where('deal_id', $id)
            ->where('dispatcher_id', $dispatcher->id)
            ->paginate(15);

        return view('commission.commissions', compact('commissions'));
    }


    /**
     * Show the form for creating a new commission.
     */
    public function create()
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            abort(403, 'Dispatcher não encontrado para este usuário.');
        }

        // Mostrar apenas o dispatcher do usuário logado
        $dispatchers = collect([$dispatcher->load('user')]);
        
        // Mostrar apenas deals do dispatcher logado
        $deals = Deal::with(['dispatcher.user', 'carrier.user'])
            ->where('dispatcher_id', $dispatcher->id)
            ->get();
        
        // Mostrar apenas employees do dispatcher logado
        $employees = Employee::with('dispatcher.user')
            ->where('dispatcher_id', $dispatcher->id)
            ->get();

        return view('commission.create', compact('dispatchers', 'deals', 'employees'));
    }

    /**
     * Store a newly created commission in storage.
     */
    public function store(Request $request)
    {
        // Obter owner do tenant
        $authUser = Auth::user();
        $ownerId = $authUser->getOwnerId();
        
        // Validar permissão
        if (!$authUser->canManageTenant()) {
            return back()->withErrors(['error' => 'Você não tem permissão para criar este registro.'])->withInput();
        }
        
        // Obter dispatchers do tenant
        $tenantDispatchers = Dispatcher::where('owner_id', $ownerId)->pluck('id');

        $validated = $request->validate([
            'dispatcher_id' => 'required|exists:dispatchers,id',
            'deal_id' => 'required|exists:deals,id',
            'employee_id' => 'required|exists:employees,id',
            'value' => 'required|numeric|min:0',
        ]);

        // Validar se o dispatcher pertence ao tenant
        if (!in_array($validated['dispatcher_id'], $tenantDispatchers->toArray())) {
            return back()->withErrors(['dispatcher_id' => 'Dispatcher não pertence ao seu tenant.'])->withInput();
        }

        // Validar se o deal pertence ao dispatcher do tenant
        $deal = Deal::where('id', $validated['deal_id'])
            ->whereIn('dispatcher_id', $tenantDispatchers)
            ->first();

        if (!$deal) {
            return back()->withErrors(['deal_id' => 'Deal inválido ou não pertence ao seu tenant.'])->withInput();
        }

        // Validar se o employee pertence ao dispatcher do tenant
        $employee = Employee::where('id', $validated['employee_id'])
            ->whereIn('dispatcher_id', $tenantDispatchers)
            ->first();

        if (!$employee) {
            return back()->withErrors(['employee_id' => 'Employee inválido ou não pertence ao seu tenant.'])->withInput();
        }

        Comission::create($validated);

        return redirect()->route('commissions.index')
                         ->with('success', 'Commission created successfully.');
    }

    /**
     * Show the form for editing the specified commission.
     */
    public function edit($id)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            abort(403, 'Dispatcher não encontrado para este usuário.');
        }

        // Buscar apenas comissão do dispatcher logado
        $commission = Comission::where('id', $id)
            ->where('dispatcher_id', $dispatcher->id)
            ->firstOrFail();

        // Mostrar apenas o dispatcher do usuário logado
        $dispatchers = collect([$dispatcher->load('user')]);
        
        // Mostrar apenas deals do dispatcher logado
        $deals = Deal::with(['dispatcher.user', 'carrier.user'])
            ->where('dispatcher_id', $dispatcher->id)
            ->get();
        
        // Mostrar apenas employees do dispatcher logado
        $employees = Employee::with('dispatcher.user')
            ->where('dispatcher_id', $dispatcher->id)
            ->get();

        return view('commission.edit', compact('commission', 'dispatchers', 'deals', 'employees'));
    }

    /**
     * Update the specified commission in storage.
     */
    public function update(Request $request, $id)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            return back()->withErrors(['error' => 'Dispatcher não encontrado para este usuário.'])->withInput();
        }

        // Buscar apenas comissão do dispatcher logado
        $commission = Comission::where('id', $id)
            ->where('dispatcher_id', $dispatcher->id)
            ->firstOrFail();

        $validated = $request->validate([
            'dispatcher_id' => 'required|exists:dispatchers,id',
            'deal_id' => 'required|exists:deals,id',
            'employee_id' => 'required|exists:employees,id',
            'value' => 'required|numeric|min:0',
        ]);

        // Validar se o dispatcher pertence ao usuário logado
        if ($validated['dispatcher_id'] != $dispatcher->id) {
            return back()->withErrors(['dispatcher_id' => 'Dispatcher inválido.'])->withInput();
        }

        // Validar se o deal pertence ao dispatcher
        $deal = Deal::where('id', $validated['deal_id'])
            ->where('dispatcher_id', $dispatcher->id)
            ->first();

        if (!$deal) {
            return back()->withErrors(['deal_id' => 'Deal inválido ou não pertence a este dispatcher.'])->withInput();
        }

        // Validar se o employee pertence ao dispatcher
        $employee = Employee::where('id', $validated['employee_id'])
            ->where('dispatcher_id', $dispatcher->id)
            ->first();

        if (!$employee) {
            return back()->withErrors(['employee_id' => 'Employee inválido ou não pertence a este dispatcher.'])->withInput();
        }

        $commission->update($validated);

        return redirect()->route('commissions.index')
                        ->with('success', 'Commission updated successfully.');
    }

    /**
     * Remove the specified commission from storage.
     */
    public function destroy($id)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            abort(403, 'Dispatcher não encontrado para este usuário.');
        }

        // Buscar apenas comissão do dispatcher logado
        $commission = Comission::where('id', $id)
            ->where('dispatcher_id', $dispatcher->id)
            ->firstOrFail();

        $commission->delete();

        return redirect()->route('commissions.index')
                        ->with('success', 'Commission deleted successfully.');
    }










    public function commission()
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            $employees = collect();
        } else {
            // Mostrar apenas employees do dispatcher logado
            $employees = Employee::where('dispatcher_id', $dispatcher->id)
                ->get()
                ->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->name ?? 'N/A'
                    ];
                });
        }
        
        return view('relatorios.comissao', compact('employees'));
    }

    public function fetchData(Request $request)
    {
        // Buscar dispatcher do usuário logado
        $dispatcher = Dispatcher::where('user_id', Auth::id())->first();
        
        if (!$dispatcher) {
            return response()->json([]);
        }

        $employeeId = $request->input('employee_id');
        $period = $request->input('period');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('commissions')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(value) as total")
            ->where('dispatcher_id', $dispatcher->id) // Filtrar por dispatcher logado
            ->when($employeeId, function ($q) use ($employeeId, $dispatcher) {
                // Validar se o employee pertence ao dispatcher
                $employee = Employee::where('id', $employeeId)
                    ->where('dispatcher_id', $dispatcher->id)
                    ->first();
                
                if ($employee) {
                    return $q->where('employee_id', $employeeId);
                }
                return $q->whereRaw('1 = 0'); // Retorna vazio se employee não pertence ao dispatcher
            });

        // Período personalizado
        switch ($period) {
            case 'last_7_days':
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
                break;
            case 'last_15_days':
                $query->where('created_at', '>=', Carbon::now()->subDays(15));
                break;
            case 'last_30_days':
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
                break;
            case 'last_60_days':
                $query->where('created_at', '>=', Carbon::now()->subDays(60));
                break;
            case 'last_90_days':
                $query->where('created_at', '>=', Carbon::now()->subDays(90));
                break;
            case 'this_month':
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                break;
            case 'last_month':
                $query->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
                break;
            default:
                break;
        }

        $commissions = $query
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($commissions);
}

}
