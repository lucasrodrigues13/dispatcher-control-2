<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DashboardReportController extends Controller
{
    public function getData(string $tipo, Request $request)
    {
        return match ($tipo) {
            'management'        => $this->dataManagement(),
            'loads-averages'    => $this->dataLoadsAverages($request),
            'revenue'           => $this->dataRevenue($request),
            'commission'        => $this->dataCommission($request),
            'carrier-revenue'   => $this->dataCarrierRevenue($request),
            'forecast'          => $this->dataForecast($request),
            'upcoming-payments' => $this->dataUpcomingPayments(),
            'past-due'          => $this->dataPastDue(),
            default             => response()->json([], 404),
        };
    }

    private function dataManagement()
    {
        $clientes = DB::table('customers')->count();
        $motoristas = DB::table('drivers')->count();
        $funcionarios = DB::table('employees')->count();
        $transportadoras = DB::table('carriers')->count();

        return response()->json([
            'labels' => ['Cosutmers', 'Drivers', 'Employes', 'Carriers'],
            'data'   => [$clientes, $motoristas, $funcionarios, $transportadoras],
        ]);
    }



            private function dataLoadsAverages(Request $request)
        {
            $query = DB::table('loads');

            // Verifica se a tabela tem as colunas
            $hasCarrierId = Schema::hasColumn('loads', 'carrier_id');
            $hasDriverId = Schema::hasColumn('loads', 'driver_id');

            // Contagem total de registros
            $totalLoads = $query->count();

            // Se não houver dados, retorna zeros para evitar divisão por zero
            if ($totalLoads === 0) {
                return response()->json([
                    'averages' => [
                        'daily' => 0,
                        'weekly' => 0,
                        'per_carrier' => 0,
                        'per_driver' => 0,
                    ],
                    'period_info' => [
                        'total_loads' => 0,
                        'days_in_period' => 0,
                        'date_range' => null,
                    ],
                ]);
            }

            // Obtém a primeira e última data disponíveis
            $dateRange = $query->clone()
                ->selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')
                ->first();

            // Converte para Carbon (se existirem datas)
            $minDate = $dateRange->min_date ? Carbon::parse($dateRange->min_date) : null;
            $maxDate = $dateRange->max_date ? Carbon::parse($dateRange->max_date) : null;

            // Calcula dias do período (mínimo 1 dia para evitar divisão por zero)
            $daysInPeriod = ($minDate && $maxDate) 
                ? max(1, $minDate->diffInDays($maxDate)) 
                : 1; // Fallback: 1 dia se não houver datas

            // Calcula semanas (mínimo 1 semana)
            $weeksInPeriod = max(1, ceil($daysInPeriod / 7));

            // Calcula médias
            $averages = [
                'daily' => round($totalLoads / $daysInPeriod, 2),
                'weekly' => round($totalLoads / $weeksInPeriod, 2),
                'per_carrier' => $hasCarrierId 
                    ? round($query->clone()
                        ->groupBy('carrier_id')
                        ->selectRaw('COUNT(*) as count')
                        ->get()
                        ->avg('count'), 2)
                    : 0,
                'per_driver' => $hasDriverId
                    ? round($query->clone()
                        ->groupBy('driver_id')
                        ->selectRaw('COUNT(*) as count')
                        ->get()
                        ->avg('count'), 2)
                    : 0,
            ];

            // Retorna o resultado
            return response()->json([
                'averages' => $averages,
                'period_info' => [
                    'total_loads' => $totalLoads,
                    'days_in_period' => $daysInPeriod,
                    'date_range' => [
                        'min' => $minDate?->toDateString(),
                        'max' => $maxDate?->toDateString(),
                    ],
                ],
            ]);
        }

    private function dataRevenue(Request $request)
    {

        
        $query = DB::table('invoices')->where('payment_status', 'paid');

        $this->applyCustomFilters($query, $request);
        $this->applyPeriodFilters($query, $request, 'paid_date');

        $gross = $query->sum('amount');
        
        $lastMonth = $query->clone()
            ->whereBetween('paid_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('amount');

        $thisMonth = $query->clone()
            ->whereBetween('paid_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
        
        return response()->json([
            'gross' => $gross,
            'lastMonth' => $lastMonth,
            'thisMonth' => $thisMonth,
            'custom' => $query->sum('amount'),
        ]);
    }

    private function dataCommission(Request $request)
    {
        $query = DB::table('commissions');

        $this->applyCustomFilters($query, $request);
        $this->applyPeriodFilters($query, $request, 'created_at');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $commissions = $query
            ->select('employee_id', DB::raw('SUM(value) as total'))
            ->groupBy('employee_id')
            ->get();

        $labels = [];
        $data = [];

        foreach ($commissions as $c) {
            $employee = DB::table('employees')->where('id', $c->employee_id)->value('user_id');
            $labels[] = $employee ?? 'Desconhecido';
            $data[] = $c->total;
        }

        return response()->json([
            'labels' => $labels,
            'data'   => $data,
        ]);
    }

    private function dataCarrierRevenue(Request $request)
    {
        $type = $request->get('type', 'price');
        $field = ($type == 'paid') ? 'amount_paid' : 'amount';

        $query = DB::table('invoices')
            ->select('carrier_id', DB::raw("SUM($field) as total"))
            ->groupBy('carrier_id');

        $this->applyCustomFilters($query, $request);
        $this->applyPeriodFilters($query, $request, 'invoice_date');

        $data = $query->get();

        $labels = [];
        $values = [];

        foreach ($data as $item) {
            $carrier = DB::table('carriers')->where('id', $item->carrier_id)->first();
            $labels[] = $carrier->company_name ?? 'N/A';
            $values[] = $item->total;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $values,
        ]);
    }

    private function dataForecast(Request $request)
    {
        // Invoiced (faturado mas não pago)
        $invoiced = DB::table('invoices')
            ->where('payment_status', 'pending')
            ->sum('amount');

        // To Be Invoiced (a faturar - cargas sem invoice)
        $toBeInvoiced = DB::table('loads')
            ->whereNull('invoice_id')
            ->where(function($q) {
                $q->whereNull('actual_delivered_date')
                  ->orWhereNull('actual_picked_up_date');
            })
            ->sum('price');

        return response()->json([
            'invoiced' => $invoiced,
            'toBeInvoiced' => $toBeInvoiced,
            'total' => $invoiced + $toBeInvoiced
        ]);
    }

    private function dataUpcomingPayments()
    {
        $upcoming = DB::table('invoices')
            ->where('payment_status', 'pending')
            ->where('due_date', '>', now())
            ->where('due_date', '<=', now()->addDays(30))
            ->get(['id', 'invoice_number', 'amount', 'due_date']);

        return response()->json([
            'upcoming' => $upcoming
        ]);
    }

    private function dataPastDue()
    {
        $pastDue = DB::table('invoices')
            ->where('payment_status', 'pending')
            ->where('due_date', '<', now())
            ->get(['id', 'invoice_number', 'amount', 'due_date']);

        return response()->json([
            'past_due' => $pastDue
        ]);
    }

    private function applyCustomFilters(&$query, $request)
    {
        if ($request->filled('customer_id') && $request->customer_id !== 'all') {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('carrier_id') && $request->carrier_id !== 'all') {
            $query->where('carrier_id', $request->carrier_id);
        }
    }

    private function applyPeriodFilters(&$query, $request, $dateField)
    {
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'last_week':
                    $query->whereBetween($dateField, [now()->subWeek(), now()]);
                    break;
                case 'last_15_days':
                    $query->whereBetween($dateField, [now()->subDays(15), now()]);
                    break;
                case 'last_30_days':
                    $query->whereBetween($dateField, [now()->subDays(30), now()]);
                    break;
                case 'last_60_days':
                    $query->whereBetween($dateField, [now()->subDays(60), now()]);
                    break;
                case 'last_90_days':
                    $query->whereBetween($dateField, [now()->subDays(90), now()]);
                    break;
                case 'this_month':
                    $query->whereBetween($dateField, [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'last_month':
                    $query->whereBetween($dateField, [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                    break;
            }
        }

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween($dateField, [$request->start, $request->end]);
        }
    }
}
