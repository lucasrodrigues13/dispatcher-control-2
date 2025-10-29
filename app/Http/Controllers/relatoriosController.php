<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use Carbon\Carbon;

class relatoriosController extends Controller
{
    public function graficoReceita(Request $request)
    {

        $periodo = $request->input('periodo', 'this_month');
        $clienteId = $request->input('customer_id');

        $query = DB::table('loads')->where('payment_status', 'paid');

        if ($clienteId && $clienteId !== 'all') {
            $query->where('dispatcher_id', $clienteId);
        }

        switch ($periodo) {
            case 'last_week':
                $query->whereBetween('created_at', [now()->subWeek(), now()]);
                break;
            case 'last_15_days':
                $query->whereBetween('created_at', [now()->subDays(15), now()]);
                break;
            case 'last_30_days':
                $query->whereBetween('created_at', [now()->subDays(30), now()]);
                break;
            case 'last_60_days':
                $query->whereBetween('created_at', [now()->subDays(60), now()]);
                break;
            case 'last_90_days':
                $query->whereBetween('created_at', [now()->subDays(90), now()]);
                break;
            case 'custom':
                if ($request->input('start_date') && $request->input('end_date')) {
                    $start = Carbon::parse($request->input('start_date'))->startOfDay();
                    $end = Carbon::parse($request->input('end_date'))->endOfDay();
                    $query->whereBetween('created_at', [$start, $end]);
                } else {
                    // Se não há datas customizadas, usa o mês atual
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                }
                break;
            case 'last_month':
                $query->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                break;
            case 'this_month':
            default:
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
        }

        $receita = (float) ($query->sum('price') ?? 0);

        $customers = DB::table('dispatchers')
            ->select('id', 'company_name as name')
            ->get();
        return $receita;
        return view('relatorios.receita', compact('receita', 'customers', 'periodo', 'clienteId'));
    }


    public function graficoComissao(Request $request)
{
    $periodo = $request->input('periodo', 'this_month');
    $employeeId = $request->input('employee_id');

    $comissoes = DB::table('comissaos')
        ->select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw("SUM(value) as total_commission")
        )
        ->when($employeeId && $employeeId !== 'all', function ($query) use ($employeeId) {
            return $query->where('employee_id', $employeeId);
        })
        ->when($periodo !== 'all_time', function ($query) use ($periodo) {
            switch ($periodo) {
                case 'last_week':
                    $query->whereBetween('created_at', [now()->subWeek(), now()]);
                    break;
                case 'last_15_days':
                    $query->whereBetween('created_at', [now()->subDays(15), now()]);
                    break;
                case 'last_30_days':
                    $query->whereBetween('created_at', [now()->subDays(30), now()]);
                    break;
                case 'last_60_days':
                    $query->whereBetween('created_at', [now()->subDays(60), now()]);
                    break;
                case 'last_90_days':
                    $query->whereBetween('created_at', [now()->subDays(90), now()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', now()->subMonth()->month)
                          ->whereYear('created_at', now()->subMonth()->year);
                    break;
            }
        })
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return response()->json([
            'labels' => $comissoes->pluck('month'),
            'data' => $comissoes->pluck('total_commission')
        ]);
}

}
