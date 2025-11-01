<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RevenueReportExport;
use App\Exports\CommissionReportExport;
use App\Exports\CarrierRevenueReportExport;
use App\Exports\DashboardExport;
use App\Exports\ForecastReportExport;
use App\Exports\UpcomingPaymentsReportExport;
use App\Exports\PastDueReportExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use App\Models\Load;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Dispatcher;
use App\Models\Employee;
use App\Models\Comission;
use App\Models\Deal;
use App\Models\Subscription;
use App\Models\TimeLineCharge;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;

        // Middleware para verificar permissões (opcional)
        // $this->middleware('permission:view_reports')->only(['getChartData', 'getDashboardStats']);
        // $this->middleware('permission:export_reports')->only(['export']);
    }



    public function index(Request $request)
    {
        // Get filter parameters
        $period = $request->get('period', 'all');
        $customStartDate = $request->get('start_date');
        $customEndDate = $request->get('end_date');
        $customerId = $request->get('customer_id');
        $carrierId = $request->get('carrier_id');
        $employeeId = $request->get('employee_id');

        // Get all data for the dashboard
        $data = [
            'management_stats' => $this->getManagementStats($customerId, $carrierId),
            'load_stats' => $this->getLoadStats($period, $customStartDate, $customEndDate, $customerId, $carrierId),
            'revenue_stats' => $this->getRevenueStats($period, $customStartDate, $customEndDate, $customerId),
            'commission_stats' => $this->getCommissionStats($period, $customStartDate, $customEndDate, $employeeId, $customerId),
            'carrier_revenue_stats' => $this->getCarrierRevenueStats($period, $customStartDate, $customEndDate, $carrierId),
            'dispatcher_fee_stats' => $this->getDispatcherFeeStats($period, $customStartDate, $customEndDate, $carrierId),
            'forecast_stats' => $this->getForecastStats($period, $customStartDate, $customEndDate, $customerId),
            'upcoming_payments' => $this->getUpcomingPayments($period, $customStartDate, $customEndDate),
            'overdue_invoices' => $this->getOverdueInvoices($period, $customStartDate, $customEndDate),

            // For dropdowns
            'customers' => Customer::all(),
            'carriers' => Carrier::all(),
            'employees' => Employee::all(),
        ];

        return view('reports.index', $data);
    }

    private function getManagementStats($customerId = null, $carrierId = null)
    {
        $customerCount = Customer::when($customerId, function($query, $customerId) {
            return $query->where('id', $customerId);
        })->count();

        $driverCount = Driver::when($carrierId, function($query, $carrierId) {
            return $query->where('carrier_id', $carrierId);
        })->count();

        $employeeCount = Employee::count();

        $carrierCount = Carrier::when($carrierId, function($query, $carrierId) {
            return $query->where('id', $carrierId);
        })->count();

        return [
            'customer_count' => $customerCount,
            'driver_count' => $driverCount,
            'employee_count' => $employeeCount,
            'carrier_count' => $carrierCount,
        ];
    }

    private function getLoadStats($period, $startDate, $endDate, $customerId = null, $carrierId = null)
    {
        $baseQuery = Load::query();
        
        if ($carrierId) {
            $baseQuery->where('carrier_id', $carrierId);
        }

        // Total loads with period filter
        $query = clone $baseQuery;
        $query = $this->applyDateFilter($query, $period, $startDate, $endDate, 'creation_date');
        $totalLoads = $query->count();

        // New loads (last 7 days from creation_date)
        $newLoadsQuery = clone $baseQuery;
        $newLoads = $newLoadsQuery->where('creation_date', '>=', Carbon::now()->subDays(7))->count();

        // Loads picked up last week (actual_pickup_date)
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
        $pickedUpLastWeek = (clone $baseQuery)
            ->whereBetween('actual_pickup_date', [$lastWeekStart, $lastWeekEnd])
            ->count();

        // Transported loads (have actual_delivery_date)
        $transportedLoadsQuery = clone $baseQuery;
        $transportedLoadsQuery = $this->applyDateFilter($transportedLoadsQuery, $period, $startDate, $endDate, 'actual_delivery_date');
        $transportedLoads = $transportedLoadsQuery->whereNotNull('actual_delivery_date')->count();

        // All transported loads (regardless of period)
        $allTransportedLoads = (clone $baseQuery)->whereNotNull('actual_delivery_date')->count();

        // Revenue from transported loads (price of loads with actual_delivery_date)
        $revenueFromTransported = (clone $baseQuery)
            ->whereNotNull('actual_delivery_date')
            ->sum('price') ?? 0;

        // Revenue from transported loads in period
        $periodTransportedQuery = clone $baseQuery;
        $periodTransportedQuery = $this->applyDateFilter($periodTransportedQuery, $period, $startDate, $endDate, 'actual_delivery_date');
        $periodRevenueFromTransported = $periodTransportedQuery
            ->whereNotNull('actual_delivery_date')
            ->sum('price') ?? 0;

        // Load status breakdown for period
        $loads = $query->get();
        $statusBreakdown = $loads->groupBy('status_move')->map(function($group) {
            return $group->count();
        });

        // Completed vs pending loads in period
        $completedLoads = $loads->whereNotNull('actual_delivery_date')->count();
        $pendingLoads = $totalLoads - $completedLoads;

        return [
            'total_loads' => $totalLoads,
            'new_loads_last_7_days' => $newLoads,
            'picked_up_last_week' => $pickedUpLastWeek,
            'transported_loads' => $transportedLoads,
            'all_transported_loads' => $allTransportedLoads,
            'revenue_from_transported' => $revenueFromTransported,
            'period_revenue_from_transported' => $periodRevenueFromTransported,
            'completed_loads' => $completedLoads,
            'pending_loads' => $pendingLoads,
            'status_breakdown' => $statusBreakdown,
        ];
    }

    private function getDispatcherFeeStats($period, $startDate, $endDate, $carrierId = null)
    {
        // Use TimeLineCharge for invoice-based calculations
        $invoiceQuery = TimeLineCharge::query();
        
        // Apply date filters
        $invoiceQuery = $this->applyDateFilter($invoiceQuery, $period, $startDate, $endDate, 'date_end');

        if ($carrierId) {
            $invoiceQuery->where('carrier_id', $carrierId);
        }

        $invoices = $invoiceQuery->get();

        // Calculate dispatcher fees based on deals
        $totalDispatcherFees = 0;
        foreach ($invoices as $invoice) {
            // Get deal percentage for this carrier and dispatcher
            $dealPercent = Deal::where('carrier_id', $invoice->carrier_id)
                              ->where('dispatcher_id', $invoice->dispatcher_id)
                              ->value('value');
            
            if ($dealPercent !== null) {
                $dealAmount = ($dealPercent / 100) * ($invoice->price ?? 0);
                $totalDispatcherFees += $dealAmount;
            }
        }

        // Last month dispatcher fees
        $lastMonth = Carbon::now()->subMonth();
        $lastMonthQuery = TimeLineCharge::whereYear('date_end', $lastMonth->year)
                                       ->whereMonth('date_end', $lastMonth->month);
        
        if ($carrierId) {
            $lastMonthQuery->where('carrier_id', $carrierId);
        }

        $lastMonthFees = 0;
        foreach ($lastMonthQuery->get() as $invoice) {
            $dealPercent = Deal::where('carrier_id', $invoice->carrier_id)
                              ->where('dispatcher_id', $invoice->dispatcher_id)
                              ->value('value');
            
            if ($dealPercent !== null) {
                $lastMonthFees += ($dealPercent / 100) * ($invoice->price ?? 0);
            }
        }

        // This month dispatcher fees
        $thisMonth = Carbon::now();
        $thisMonthQuery = TimeLineCharge::whereYear('date_end', $thisMonth->year)
                                       ->whereMonth('date_end', $thisMonth->month);
        
        if ($carrierId) {
            $thisMonthQuery->where('carrier_id', $carrierId);
        }

        $thisMonthFees = 0;
        foreach ($thisMonthQuery->get() as $invoice) {
            $dealPercent = Deal::where('carrier_id', $invoice->carrier_id)
                              ->where('dispatcher_id', $invoice->dispatcher_id)
                              ->value('value');
            
            if ($dealPercent !== null) {
                $thisMonthFees += ($dealPercent / 100) * ($invoice->price ?? 0);
            }
        }

        // Calculate custom period fees based on the filtered invoices
        $customPeriodFees = 0;
        foreach ($invoices as $invoice) {
            $dealPercent = Deal::where('carrier_id', $invoice->carrier_id)
                              ->where('dispatcher_id', $invoice->dispatcher_id)
                              ->value('value');
            
            if ($dealPercent !== null) {
                $customPeriodFees += ($dealPercent / 100) * ($invoice->price ?? 0);
            }
        }

        return [
            'gross_commission' => $totalDispatcherFees, // Total commission from all invoices
            'custom_commission' => $customPeriodFees, // Commission for the selected period
            'commission_last_month' => $lastMonthFees,
            'commission_this_month' => $thisMonthFees,
            'total_invoices' => $invoices->count(),
            'avg_commission_per_invoice' => $invoices->count() > 0 ? round($totalDispatcherFees / $invoices->count(), 2) : 0,
        ];
    }

    private function getRevenueStats($period, $startDate, $endDate, $customerId = null)
    {
        // Base query for paid invoices
        $baseQuery = TimeLineCharge::where('status_payment', 'paid');

        // Apply customer filter using the correct field name 'costumer'
        if ($customerId) {
            $baseQuery->where('costumer', $customerId);
        }

        // Gross Revenue (All time paid invoices)
        $grossRevenue = (clone $baseQuery)->sum('price');

        // Revenue Last Month - using date_end for invoice completion date
        $lastMonth = Carbon::now()->subMonth();
        $revenueLastMonth = (clone $baseQuery)
            ->whereYear('date_end', $lastMonth->year)
            ->whereMonth('date_end', $lastMonth->month)
            ->sum('price');

        // Revenue This Month
        $thisMonth = Carbon::now();
        $revenueThisMonth = (clone $baseQuery)
            ->whereYear('date_end', $thisMonth->year)
            ->whereMonth('date_end', $thisMonth->month)
            ->sum('price');

        // Custom Revenue (based on selected period)
        $customQuery = clone $baseQuery;
        $customQuery = $this->applyDateFilter($customQuery, $period, $startDate, $endDate, 'date_end');
        $customRevenue = $customQuery->sum('price');

        // Additional stats for better reporting
        $totalInvoices = (clone $baseQuery)->count();
        $pendingRevenue = TimeLineCharge::where('status_payment', '!=', 'paid')
            ->when($customerId, function($query, $customerId) {
                return $query->where('costumer', $customerId);
            })
            ->sum('price');

        return [
            'gross_revenue' => $grossRevenue,
            'revenue_last_month' => $revenueLastMonth,
            'revenue_this_month' => $revenueThisMonth,
            'custom_revenue' => $customRevenue,
            'total_invoices' => $totalInvoices,
            'pending_revenue' => $pendingRevenue,
        ];
    }

    private function getCommissionStats($period, $startDate, $endDate, $employeeId = null, $customerId = null)
    {
        // Use Load table for commission calculations
        $baseQuery = Load::query();

        if ($employeeId) {
            $baseQuery->where('employee_id', $employeeId);
        }

        if ($customerId) {
            // Apply customer filter if needed
        }

        // Gross Commission - calculate from price and expenses
        $grossCommission = $baseQuery->selectRaw('SUM(COALESCE(price, 0) - COALESCE(expenses, 0) - COALESCE(driver_pay, 0)) as commission')
            ->value('commission') ?? 0;

        // Commission Last Month
        $lastMonth = Carbon::now()->subMonth();
        $commissionLastMonth = (clone $baseQuery)
            ->whereYear('creation_date', $lastMonth->year)
            ->whereMonth('creation_date', $lastMonth->month)
            ->selectRaw('SUM(COALESCE(price, 0) - COALESCE(expenses, 0) - COALESCE(driver_pay, 0)) as commission')
            ->value('commission') ?? 0;

        // Commission This Month
        $thisMonth = Carbon::now();
        $commissionThisMonth = (clone $baseQuery)
            ->whereYear('creation_date', $thisMonth->year)
            ->whereMonth('creation_date', $thisMonth->month)
            ->selectRaw('SUM(COALESCE(price, 0) - COALESCE(expenses, 0) - COALESCE(driver_pay, 0)) as commission')
            ->value('commission') ?? 0;

        // Custom Commission based on period
        $customQuery = clone $baseQuery;
        $customQuery = $this->applyDateFilter($customQuery, $period, $startDate, $endDate, 'creation_date');
        $customCommission = $customQuery->selectRaw('SUM(COALESCE(price, 0) - COALESCE(expenses, 0) - COALESCE(driver_pay, 0)) as commission')
            ->value('commission') ?? 0;

        return [
            'gross_commission' => $grossCommission,
            'commission_last_month' => $commissionLastMonth,
            'commission_this_month' => $commissionThisMonth,
            'custom_commission' => $customCommission,
        ];
    }

    private function getCarrierRevenueStats($period, $startDate, $endDate, $carrierId = null)
    {
        // Use TimeLineCharge for actual invoiced revenue
        $invoiceQuery = TimeLineCharge::query();
        
        if ($carrierId) {
            $invoiceQuery->where('carrier_id', $carrierId);
        }

        // Gross Revenue from invoices (Price - what was invoiced)
        $grossRevenuePrice = (clone $invoiceQuery)->sum('price');

        // Gross Revenue (Paid - what was actually paid)
        $grossRevenuePaid = (clone $invoiceQuery)->where('status_payment', 'paid')->sum('price');

        // Revenue Last Month from invoices
        $lastMonth = Carbon::now()->subMonth();
        $revenueLastMonthPrice = (clone $invoiceQuery)
            ->whereYear('date_end', $lastMonth->year)
            ->whereMonth('date_end', $lastMonth->month)
            ->sum('price');

        $revenueLastMonthPaid = (clone $invoiceQuery)
            ->where('status_payment', 'paid')
            ->whereYear('date_end', $lastMonth->year)
            ->whereMonth('date_end', $lastMonth->month)
            ->sum('price');

        // Revenue This Month from invoices
        $thisMonth = Carbon::now();
        $revenueThisMonthPrice = (clone $invoiceQuery)
            ->whereYear('date_end', $thisMonth->year)
            ->whereMonth('date_end', $thisMonth->month)
            ->sum('price');

        $revenueThisMonthPaid = (clone $invoiceQuery)
            ->where('status_payment', 'paid')
            ->whereYear('date_end', $thisMonth->year)
            ->whereMonth('date_end', $thisMonth->month)
            ->sum('price');

        // Custom Revenue based on period
        $customQueryPrice = clone $invoiceQuery;
        $customQueryPrice = $this->applyDateFilter($customQueryPrice, $period, $startDate, $endDate, 'date_end');
        $customRevenuePrice = $customQueryPrice->sum('price');
        
        $customQueryPaid = clone $invoiceQuery;
        $customQueryPaid = $this->applyDateFilter($customQueryPaid, $period, $startDate, $endDate, 'date_end');
        $customRevenuePaid = $customQueryPaid->where('status_payment', 'paid')->sum('price');

        return [
            'gross_revenue_price' => $grossRevenuePrice,
            'gross_revenue_paid' => $grossRevenuePaid,
            'revenue_last_month_price' => $revenueLastMonthPrice,
            'revenue_last_month_paid' => $revenueLastMonthPaid,
            'revenue_this_month_price' => $revenueThisMonthPrice,
            'revenue_this_month_paid' => $revenueThisMonthPaid,
            'custom_revenue_price' => $customRevenuePrice,
            'custom_revenue_paid' => $customRevenuePaid,
        ];
    }

    private function getForecastStats($period, $startDate, $endDate, $customerId = null)
    {
        // To Be Invoiced by User Type
        $toBeInvoicedCarrier = Load::where(function($query) {
            $query->whereNull('actual_delivery_date')
                  ->orWhereNull('actual_pickup_date');
        })->whereNotNull('carrier_id');
        
        $toBeInvoicedDispatcher = Load::where(function($query) {
            $query->whereNull('actual_delivery_date')
                  ->orWhereNull('actual_pickup_date');
        })->whereNotNull('dispatcher_id');
        
        $toBeInvoicedEmployee = Load::where(function($query) {
            $query->whereNull('actual_delivery_date')
                  ->orWhereNull('actual_pickup_date');
        })->whereNotNull('employee_id');

        if ($customerId) {
            $toBeInvoicedCarrier->where('carrier_id', $customerId);
            $toBeInvoicedDispatcher->where('dispatcher_id', $customerId);
            $toBeInvoicedEmployee->where('employee_id', $customerId);
        }

        $carrierToBeInvoiced = $this->applyDateFilter($toBeInvoicedCarrier, $period, $startDate, $endDate, 'created_at')->sum('price');
        $dispatcherToBeInvoiced = $this->applyDateFilter($toBeInvoicedDispatcher, $period, $startDate, $endDate, 'created_at')->sum('price');
        $employeeToBeInvoiced = $this->applyDateFilter($toBeInvoicedEmployee, $period, $startDate, $endDate, 'created_at')->sum('price');

        // Invoiced but not paid: TimeLineCharges that are not paid
        $invoicedNotPaidQuery = TimeLineCharge::where('status_payment', '!=', 'paid');

        if ($customerId) {
            $invoicedNotPaidQuery->where('costumer', $customerId);
        }

        $invoicedNotPaid = $this->applyDateFilter($invoicedNotPaidQuery, $period, $startDate, $endDate, 'date_start')
                               ->sum('price');

        $totalToBeInvoiced = $carrierToBeInvoiced + $dispatcherToBeInvoiced + $employeeToBeInvoiced;

        return [
            'carrier' => [
                'to_be_invoiced' => $carrierToBeInvoiced,
                'invoiced_not_paid' => $invoicedNotPaid * 0.33, // Distribute proportionally
                'total_forecast' => $carrierToBeInvoiced + ($invoicedNotPaid * 0.33),
            ],
            'dispatcher' => [
                'to_be_invoiced' => $dispatcherToBeInvoiced,
                'invoiced_not_paid' => $invoicedNotPaid * 0.33,
                'total_forecast' => $dispatcherToBeInvoiced + ($invoicedNotPaid * 0.33),
            ],
            'employee' => [
                'to_be_invoiced' => $employeeToBeInvoiced,
                'invoiced_not_paid' => $invoicedNotPaid * 0.34,
                'total_forecast' => $employeeToBeInvoiced + ($invoicedNotPaid * 0.34),
            ],
            'total' => [
                'to_be_invoiced' => $totalToBeInvoiced,
                'invoiced_not_paid' => $invoicedNotPaid,
                'total_forecast' => $totalToBeInvoiced + $invoicedNotPaid,
            ],
        ];
    }

    private function getUpcomingPayments($period, $startDate, $endDate)
    {
        $query = TimeLineCharge::where('status_payment', '!=', 'paid')
                               ->where('due_date', '>', Carbon::now())
                               ->orderBy('due_date', 'asc');

        $query = $this->applyDateFilter($query, $period, $startDate, $endDate, 'due_date');

        $upcomingPayments = $query->get()->map(function($charge) {
            return [
                'invoice_id' => $charge->invoice_id,
                'customer' => $charge->costumer,
                'amount' => $charge->price,
                'due_date' => $charge->due_date,
                'days_until_due' => Carbon::now()->diffInDays($charge->due_date, false),
            ];
        });

        return [
            'payments' => $upcomingPayments,
            'total_amount' => $upcomingPayments->sum('amount'),
        ];
    }

    private function getOverdueInvoices($period, $startDate, $endDate)
    {
        $query = TimeLineCharge::where('status_payment', '!=', 'paid')
                               ->where('due_date', '<', Carbon::now())
                               ->orderBy('due_date', 'asc');

        $query = $this->applyDateFilter($query, $period, $startDate, $endDate, 'due_date');

        $overdueInvoices = $query->get()->map(function($charge) {
            return [
                'invoice_id' => $charge->invoice_id,
                'customer' => $charge->costumer,
                'amount' => $charge->price,
                'due_date' => $charge->due_date,
                'days_overdue' => $charge->due_date->diffInDays(Carbon::now()),
            ];
        });

        return [
            'invoices' => $overdueInvoices,
            'total_amount' => $overdueInvoices->sum('amount'),
        ];
    }

    private function applyDateFilter($query, $period, $startDate, $endDate, $dateField)
    {
        switch ($period) {
            case 'last_week':
                return $query->where($dateField, '>=', Carbon::now()->subWeek());
            case 'last_15_days':
                return $query->where($dateField, '>=', Carbon::now()->subDays(15));
            case 'last_30_days':
                return $query->where($dateField, '>=', Carbon::now()->subDays(30));
            case 'last_60_days':
                return $query->where($dateField, '>=', Carbon::now()->subDays(60));
            case 'last_90_days':
                return $query->where($dateField, '>=', Carbon::now()->subDays(90));
            case 'custom':
                if ($startDate && $endDate) {
                    return $query->whereBetween($dateField, [$startDate, $endDate]);
                }
                break;
        }

        return $query;
    }

    private function getDaysInPeriod($period, $startDate, $endDate)
    {
        switch ($period) {
            case 'last_week':
                return 7;
            case 'last_15_days':
                return 15;
            case 'last_30_days':
                return 30;
            case 'last_60_days':
                return 60;
            case 'last_90_days':
                return 90;
            case 'custom':
                if ($startDate && $endDate) {
                    return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
                }
                break;
        }

        return 30; // Default to 30 days
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $type = $request->get('type', 'all');

        // Get filter parameters
        $period = $request->get('period', 'all');
        $customStartDate = $request->get('start_date');
        $customEndDate = $request->get('end_date');
        $customerId = $request->get('customer_id');
        $carrierId = $request->get('carrier_id');
        $employeeId = $request->get('employee_id');

        // Get all data for export
        $data = [
            'management_stats' => $this->getManagementStats($customerId, $carrierId),
            'load_stats' => $this->getLoadStats($period, $customStartDate, $customEndDate, $customerId, $carrierId),
            'revenue_stats' => $this->getRevenueStats($period, $customStartDate, $customEndDate, $customerId),
            'commission_stats' => $this->getCommissionStats($period, $customStartDate, $customEndDate, $employeeId, $customerId),
            'carrier_revenue_stats' => $this->getCarrierRevenueStats($period, $customStartDate, $customEndDate, $carrierId),
            'dispatcher_fee_stats' => $this->getDispatcherFeeStats($period, $customStartDate, $customEndDate, $carrierId),
            'forecast_stats' => $this->getForecastStats($period, $customStartDate, $customEndDate, $customerId),
            'upcoming_payments' => $this->getUpcomingPayments($period, $customStartDate, $customEndDate),
            'overdue_invoices' => $this->getOverdueInvoices($period, $customStartDate, $customEndDate),
        ];

        $filename = 'dashboard_report_' . date('Y-m-d_H-i-s') . '.' . $format;

        return Excel::download(new DashboardExport($data, $type), $filename);
    }

    public function getChartData(Request $request)
    {
        $chartType = $request->get('chart_type');
        $period = $request->get('period', 'last_30_days');
        $customerId = $request->get('customer_id');
        $carrierId = $request->get('carrier_id');
        $employeeId = $request->get('employee_id');

        switch ($chartType) {
            case 'revenue_trend':
                return response()->json($this->getRevenueTrendData($period, $customerId));
            case 'load_status':
                return response()->json($this->getLoadStatusData($period, $carrierId));
            case 'dispatcher_fees':
                return response()->json($this->getDispatcherFeesData($period, $carrierId));
            case 'top_customers':
                return response()->json($this->getTopCustomersData($period));
            case 'monthly_comparison':
                return response()->json($this->getMonthlyComparisonData());
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    private function getRevenueTrendData($period, $customerId = null)
    {
        $days = 30;
        if ($period === 'last_7_days') $days = 7;
        elseif ($period === 'last_90_days') $days = 90;

        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        $query = TimeLineCharge::where('status_payment', 'paid')
                               ->whereBetween('date_end', [$startDate, $endDate]);

        if ($customerId) {
            $query->where('costumer', $customerId);
        }

        $data = $query->selectRaw('DATE(date_end) as date, SUM(price) as revenue')
                      ->groupBy('date')
                      ->orderBy('date')
                      ->get();

        return [
            'labels' => $data->pluck('date'),
            'data' => $data->pluck('revenue'),
        ];
    }

    private function getLoadStatusData($period, $carrierId = null)
    {
        $query = Load::query();

        if ($carrierId) {
            $query->where('carrier_id', $carrierId);
        }

        // Apply period filter
        $this->applyDateFilter($query, $period, null, null, 'created_at');

        $statusCounts = $query->selectRaw('status_move, COUNT(*) as count')
                             ->groupBy('status_move')
                             ->get();

        return [
            'labels' => $statusCounts->pluck('status_move'),
            'data' => $statusCounts->pluck('count'),
        ];
    }

    private function getTopCustomersData($period)
    {
        $query = TimeLineCharge::where('status_payment', 'paid');
        $this->applyDateFilter($query, $period, null, null, 'date_end');

        $topCustomers = $query->selectRaw('costumer, SUM(price) as total_revenue')
                             ->groupBy('costumer')
                             ->orderBy('total_revenue', 'desc')
                             ->limit(10)
                             ->get();

        return [
            'labels' => $topCustomers->pluck('costumer'),
            'data' => $topCustomers->pluck('total_revenue'),
        ];
    }

    private function getMonthlyComparisonData()
    {
        $months = [];
        $revenue = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $monthRevenue = TimeLineCharge::where('status_payment', 'paid')
                                         ->whereYear('date_end', $date->year)
                                         ->whereMonth('date_end', $date->month)
                                         ->sum('price');
            $revenue[] = $monthRevenue;
        }

        return [
            'labels' => $months,
            'data' => $revenue,
        ];
    }
}
