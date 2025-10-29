<?php

namespace App\Http\Controllers;

use App\Models\Comission;
use App\Models\Dispatcher;
use Illuminate\Support\Facades\DB;
use App\Models\Deal;
use App\Models\Employeer;
use Illuminate\Http\Request;

class ComissionController extends Controller
{
    /**
     * Display a listing of the commissions.
     */
    public function index()
    {

        // Listar todas as comissões com os relacionamentos para facilitar exibição
        $commissions = Comission::with(['dispatcher.user', 'deal.carrier.user', 'employee.user'])->paginate(15);

        return view('commission.index', compact('commissions'));
    }

    public function commissions($id)
    {
        // Filtra as comissões apenas do dispatcher informado
        $commissions = Comission::with(['dispatcher.user', 'deal.carrier.user', 'employee.user'])
            ->where('deal_id', $id)
            ->paginate(15);

        return view('commission.commissions', compact('commissions'));
    }


    /**
     * Show the form for creating a new commission.
     */
    public function create()
    {
        $dispatchers = Dispatcher::with('user')->get();
        $deals = Deal::with(['dispatcher.user', 'carrier.user'])->get();
        $employees = Employeer::with('user')->get();

        return view('commission.create', compact('dispatchers', 'deals', 'employees'));
    }

    /**
     * Store a newly created commission in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dispatcher_id' => 'required|exists:dispatchers,id',
            'deal_id' => 'required|exists:deals,id',
            'employee_id' => 'required|exists:employees,id',
            'value' => 'required|numeric|min:0',
        ]);

        Comission::create($validated);

        return redirect()->route('commissions.index')
                         ->with('success', 'Commission created successfully.');
    }

    /**
     * Show the form for editing the specified commission.
     */
    public function edit($id)
    {
        // Buscar a comissão pelo id ou falhar
        $commission = Comission::findOrFail($id);

        $dispatchers = Dispatcher::with('user')->get();
        $deals = Deal::with(['dispatcher.user', 'carrier.user'])->get();
        $employees = Employeer::with('user')->get();

        return view('commission.edit', compact('commission', 'dispatchers', 'deals', 'employees'));
    }

    /**
     * Update the specified commission in storage.
     */
    public function update(Request $request, $id)
    {
        $commission = Comission::findOrFail($id);

        $validated = $request->validate([
            'dispatcher_id' => 'required|exists:dispatchers,id',
            'deal_id' => 'required|exists:deals,id',
            'employee_id' => 'required|exists:employees,id',
            'value' => 'required|numeric|min:0',
        ]);

        $commission->update($validated);

        return redirect()->route('commissions.index')
                        ->with('success', 'Commission updated successfully.');
    }

    /**
     * Remove the specified commission from storage.
     */
    public function destroy($id)
    {
        $commission = Comission::findOrFail($id);

        $commission->delete();

        return redirect()->route('commissions.index')
                        ->with('success', 'Commission deleted successfully.');
    }










    public function commission()
    {

        $employees = DB::table('users')->select('id', 'name')->get();
        return view('relatorios.comissao', compact('employees'));
    }

    public function fetchData(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $period = $request->input('period');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('commissions')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(value) as total")
            ->when($employeeId, function ($q) use ($employeeId) {
                return $q->where('employee_id', $employeeId);
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
