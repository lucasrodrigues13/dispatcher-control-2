@extends('layouts.app2')

@section('conteudo')
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Dashboard Analytics</h3>
                <h6 class="op-7 mb-2">Comprehensive view of your business performance</h6>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Filters & Export Options</h4>
                    </div>
                    <div class="card-body">
                        <form id="dashboard-filters" method="GET">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Time Period</label>
                                        <select class="form-select" name="period" id="period">
                                            <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>All Time</option>
                                            <option value="last_week" {{ request('period') == 'last_week' ? 'selected' : '' }}>Last Week</option>
                                            <option value="last_15_days" {{ request('period') == 'last_15_days' ? 'selected' : '' }}>Last 15 Days</option>
                                            <option value="last_30_days" {{ request('period') == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                                            <option value="last_60_days" {{ request('period') == 'last_60_days' ? 'selected' : '' }}>Last 60 Days</option>
                                            <option value="last_90_days" {{ request('period') == 'last_90_days' ? 'selected' : '' }}>Last 90 Days</option>
                                            <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Period</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2" id="custom-dates" style="{{ request('period') == 'custom' ? '' : 'display: none;' }}">
                                    <div class="form-group">
                                        <label>Start Date</label>
                                        <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-2" id="custom-dates-end" style="{{ request('period') == 'custom' ? '' : 'display: none;' }}">
                                    <div class="form-group">
                                        <label>End Date</label>
                                        <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Customer</label>
                                        <select class="form-select" name="customer_id">
                                            <option value="">All Customers</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->company_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Carrier</label>
                                        <select class="form-select" name="carrier_id">
                                            <option value="">All Carriers</option>
                                            @foreach($carriers as $carrier)
                                                <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                                    {{ $carrier->company_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-round d-block">
                                            <i class="fa fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success" onclick="exportData('xlsx', 'all')">
                                        <i class="fa fa-file-excel"></i> Export XLS
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="exportData('csv', 'all')">
                                        <i class="fa fa-file-csv"></i> Export CSV
                                    </button>
                                </div>
                                <div class="form-check form-switch float-end">
                                    <input class="form-check-input" type="checkbox" id="realtime-toggle">
                                    <label class="form-check-label" for="realtime-toggle">
                                        Real-time Updates
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Statistics -->
        <div class="row mb-4">
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-primary card-round">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fa fa-users"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Total Customers</p>
                                    <h4 class="card-title" data-stat="customer-count">{{ number_format($management_stats['customer_count']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-info card-round">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fa fa-truck"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Total Drivers</p>
                                    <h4 class="card-title" data-stat="driver-count">{{ number_format($management_stats['driver_count']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-success card-round">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fa fa-user-tie"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Total Employees</p>
                                    <h4 class="card-title" data-stat="employee-count">{{ number_format($management_stats['employee_count']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-warning card-round">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fa fa-building"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Total Carriers</p>
                                    <h4 class="card-title" data-stat="carrier-count">{{ number_format($management_stats['carrier_count']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Load Statistics -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Load Statistics</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 class="text-primary" data-stat="total-loads">{{ number_format($load_stats['total_loads']) }}</h3>
                                    <p class="mb-0">Total Loads</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 class="text-info" data-stat="new-loads">{{ number_format($load_stats['new_loads_last_7_days']) }}</h3>
                                    <p class="mb-0">New Loads (7 days)</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 class="text-success" data-stat="picked-up">{{ number_format($load_stats['picked_up_last_week']) }}</h3>
                                    <p class="mb-0">Picked Up Last Week</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 class="text-warning" data-stat="transported">{{ number_format($load_stats['transported_loads']) }}</h3>
                                    <p class="mb-0">Transported (Period)</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 class="text-danger" data-stat="all-transported">{{ number_format($load_stats['all_transported_loads']) }}</h3>
                                    <p class="mb-0">All Transported</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 class="text-dark" data-stat="revenue-transported">${{ number_format($load_stats['revenue_from_transported'], 2) }}</h3>
                                    <p class="mb-0">Revenue Received</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">Dispatcher Fee Revenue</h4>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success btn-sm" onclick="exportData('xlsx', 'revenue')">
                                <i class="fa fa-download"></i> XLS
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="exportData('csv', 'revenue')">
                                <i class="fa fa-download"></i> CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center mb-3">
                                    <h4 class="text-success" data-stat="gross-commission">${{ number_format($dispatcher_fee_stats['gross_commission'], 2) }}</h4>
                                    <p class="mb-0 text-muted">Total Commission</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center mb-3">
                                    <h5 class="text-info" data-stat="commission-last-month">${{ number_format($dispatcher_fee_stats['commission_last_month'], 2) }}</h5>
                                    <p class="mb-0 text-muted">Last Month</p>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="text-center">
                                    <h6 class="text-secondary" data-stat="total-invoices">{{ number_format($dispatcher_fee_stats['total_invoices']) }}</h6>
                                    <p class="mb-0 text-muted">Total Invoices</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <canvas id="dispatcherFeeChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">Employee Commissions</h4>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success btn-sm" onclick="exportData('xlsx', 'commission')">
                                <i class="fa fa-download"></i> XLS
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="exportData('csv', 'commission')">
                                <i class="fa fa-download"></i> CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center mb-3">
                                    <h4 class="text-success" data-stat="gross-commission">${{ number_format($commission_stats['gross_commission'], 2) }}</h4>
                                    <p class="mb-0 text-muted">Total Commissions</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center mb-3">
                                    <h5 class="text-info" data-stat="commission-last-month">${{ number_format($commission_stats['commission_last_month'], 2) }}</h5>
                                    <p class="mb-0 text-muted">Last Month</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <canvas id="commissionChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carrier Revenue -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">Carrier/Customer Revenue</h4>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success btn-sm" onclick="exportData('xlsx', 'carrier_revenue')">
                                <i class="fa fa-download"></i> XLS
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="exportData('csv', 'carrier_revenue')">
                                <i class="fa fa-download"></i> CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-2">
                                <h4 class="text-success" data-stat="gross-revenue-price">${{ number_format($carrier_revenue_stats['gross_revenue_price'], 2) }}</h4>
                                <p class="text-muted">Total Price</p>
                            </div>
                            <div class="col-md-2">
                                <h4 class="text-info" data-stat="gross-revenue-paid">${{ number_format($carrier_revenue_stats['gross_revenue_paid'], 2) }}</h4>
                                <p class="text-muted">Total Paid</p>
                            </div>
                            <div class="col-md-2">
                                <h5 class="text-primary" data-stat="custom-revenue-price">${{ number_format($carrier_revenue_stats['custom_revenue_price'], 2) }}</h5>
                                <p class="text-muted">Period Price</p>
                            </div>
                            <div class="col-md-2">
                                <h5 class="text-warning" data-stat="custom-revenue-paid">${{ number_format($carrier_revenue_stats['custom_revenue_paid'], 2) }}</h5>
                                <p class="text-muted">Period Paid</p>
                            </div>
                            <div class="col-md-2">
                                <h5 class="text-secondary">${{ number_format($carrier_revenue_stats['revenue_last_month_price'], 2) }}</h5>
                                <p class="text-muted">Last Month Price</p>
                            </div>
                            <div class="col-md-2">
                                <h5 class="text-dark">${{ number_format($carrier_revenue_stats['revenue_this_month_price'], 2) }}</h5>
                                <p class="text-muted">This Month Price</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <canvas id="carrierRevenueChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Forecast & Future Payments -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">Revenue Forecast by User Type</h4>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success btn-sm" onclick="exportData('xlsx', 'forecast')">
                                <i class="fa fa-download"></i> XLS
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="exportData('csv', 'forecast')">
                                <i class="fa fa-download"></i> CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" id="forecastTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="total-tab" data-bs-toggle="tab" data-bs-target="#total" type="button" role="tab">Total</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="carrier-tab" data-bs-toggle="tab" data-bs-target="#carrier" type="button" role="tab">Carrier</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="dispatcher-tab" data-bs-toggle="tab" data-bs-target="#dispatcher" type="button" role="tab">Dispatcher</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee" type="button" role="tab">Employee</button>
                            </li>
                        </ul>
                        
                        <!-- Tab content -->
                        <div class="tab-content mt-3" id="forecastTabContent">
                            <!-- Total Tab -->
                            <div class="tab-pane fade show active" id="total" role="tabpanel">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-warning">${{ number_format($forecast_stats['total']['to_be_invoiced'], 2) }}</h4>
                                        <p class="text-muted">To Be Invoiced</p>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-info">${{ number_format($forecast_stats['total']['invoiced_not_paid'], 2) }}</h4>
                                        <p class="text-muted">Invoiced Not Paid</p>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <h3 class="text-success">${{ number_format($forecast_stats['total']['total_forecast'], 2) }}</h3>
                                    <p class="text-muted">Total Forecast</p>
                                </div>
                            </div>
                            
                            <!-- Carrier Tab -->
                            <div class="tab-pane fade" id="carrier" role="tabpanel">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-warning">${{ number_format($forecast_stats['carrier']['to_be_invoiced'], 2) }}</h4>
                                        <p class="text-muted">To Be Invoiced</p>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-info">${{ number_format($forecast_stats['carrier']['invoiced_not_paid'], 2) }}</h4>
                                        <p class="text-muted">Invoiced Not Paid</p>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <h3 class="text-success">${{ number_format($forecast_stats['carrier']['total_forecast'], 2) }}</h3>
                                    <p class="text-muted">Carrier Forecast</p>
                                </div>
                            </div>
                            
                            <!-- Dispatcher Tab -->
                            <div class="tab-pane fade" id="dispatcher" role="tabpanel">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-warning">${{ number_format($forecast_stats['dispatcher']['to_be_invoiced'], 2) }}</h4>
                                        <p class="text-muted">To Be Invoiced</p>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-info">${{ number_format($forecast_stats['dispatcher']['invoiced_not_paid'], 2) }}</h4>
                                        <p class="text-muted">Invoiced Not Paid</p>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <h3 class="text-success">${{ number_format($forecast_stats['dispatcher']['total_forecast'], 2) }}</h3>
                                    <p class="text-muted">Dispatcher Forecast</p>
                                </div>
                            </div>
                            
                            <!-- Employee Tab -->
                            <div class="tab-pane fade" id="employee" role="tabpanel">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-warning">${{ number_format($forecast_stats['employee']['to_be_invoiced'], 2) }}</h4>
                                        <p class="text-muted">To Be Invoiced</p>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-info">${{ number_format($forecast_stats['employee']['invoiced_not_paid'], 2) }}</h4>
                                        <p class="text-muted">Invoiced Not Paid</p>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <h3 class="text-success">${{ number_format($forecast_stats['employee']['total_forecast'], 2) }}</h3>
                                    <p class="text-muted">Employee Forecast</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">Upcoming Payments</h4>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success btn-sm" onclick="exportData('xlsx', 'upcoming_payments')">
                                <i class="fa fa-download"></i> XLS
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="exportData('csv', 'upcoming_payments')">
                                <i class="fa fa-download"></i> CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h3 class="text-primary">${{ number_format($upcoming_payments['total_amount'], 2) }}</h3>
                            <p class="text-muted">Total Expected</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Invoice ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcoming_payments['payments']->take(5) as $payment)
                                    <tr>
                                        <td>{{ $payment['invoice_id'] }}</td>
                                        <td>{{ $payment['customer'] }}</td>
                                        <td>${{ number_format($payment['amount'], 2) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($payment['due_date'])->format('M d, Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($upcoming_payments['payments']->count() > 5)
                            <p class="text-center text-muted">... and {{ $upcoming_payments['payments']->count() - 5 }} more</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Invoices -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title text-danger">
                            <i class="fa fa-exclamation-triangle"></i> Overdue Invoices
                        </h4>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success btn-sm" onclick="exportData('xlsx', 'overdue_invoices')">
                                <i class="fa fa-download"></i> XLS
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="exportData('csv', 'overdue_invoices')">
                                <i class="fa fa-download"></i> CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h3 class="text-danger">${{ number_format($overdue_invoices['total_amount'], 2) }}</h3>
                            <p class="text-muted">Total Overdue Amount</p>
                        </div>
                        @if($overdue_invoices['invoices']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Invoice ID</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Due Date</th>
                                            <th>Days Overdue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($overdue_invoices['invoices'] as $invoice)
                                        <tr>
                                            <td>{{ $invoice['invoice_id'] }}</td>
                                            <td>{{ $invoice['customer'] }}</td>
                                            <td class="text-danger font-weight-bold">${{ number_format($invoice['amount'], 2) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($invoice['due_date'])->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge badge-danger">{{ $invoice['days_overdue'] }} days</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-success text-center">
                                <i class="fa fa-check-circle"></i> No overdue invoices found!
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Indicators -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <span id="loads-today" class="h5 text-primary">-</span>
                                <p class="mb-0 small text-muted">Loads Today</p>
                            </div>
                            <div class="col-md-3">
                                <span id="revenue-today" class="h5 text-success">$-</span>
                                <p class="mb-0 small text-muted">Revenue Today</p>
                            </div>
                            <div class="col-md-3">
                                <span id="pending-invoices" class="h5 text-warning">-</span>
                                <p class="mb-0 small text-muted">Pending Invoices</p>
                            </div>
                            <div class="col-md-3">
                                <span id="overdue-count" class="h5 text-danger">-</span>
                                <p class="mb-0 small text-muted">Overdue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(config('app.debug') && isset($performance))
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>Debug Info:</strong>
                    Execution Time: {{ $performance['execution_time'] }}ms
                    | Memory Usage: {{ $performance['memory_usage'] }}MB
                    | Cached: {{ $performance['cached'] ? 'Yes' : 'No' }}
                    | Timestamp: {{ $performance['timestamp'] }}
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts with data from controller
    initializeCharts();

    // Set initial state for custom date fields
    const periodSelect = document.getElementById('period');
    if (periodSelect && periodSelect.value === 'custom') {
        document.getElementById('custom-dates').style.display = 'block';
        document.getElementById('custom-dates-end').style.display = 'block';
    }
});

function initializeCharts() {
    // Dispatcher Fee Chart
    const dispatcherFeeCtx = document.getElementById('dispatcherFeeChart');
    if (dispatcherFeeCtx) {
        new Chart(dispatcherFeeCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Commission ($)',
                    data: [{{ $dispatcher_fee_stats['commission_last_month'] }}, {{ $dispatcher_fee_stats['commission_this_month'] }}, 0, 0, 0, 0],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Commission Chart
    const commissionCtx = document.getElementById('commissionChart');
    if (commissionCtx) {
        new Chart(commissionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Last Month', 'This Month'],
                datasets: [{
                    data: [{{ $commission_stats['commission_last_month'] }}, {{ $commission_stats['commission_this_month'] }}],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Carrier Revenue Chart
    const carrierRevenueCtx = document.getElementById('carrierRevenueChart');
    if (carrierRevenueCtx) {
        new Chart(carrierRevenueCtx, {
            type: 'bar',
            data: {
                labels: ['Price', 'Paid'],
                datasets: [{
                    label: 'Total',
                    data: [{{ $carrier_revenue_stats['gross_revenue_price'] }}, {{ $carrier_revenue_stats['gross_revenue_paid'] }}],
                    backgroundColor: 'rgba(75, 192, 192, 0.8)'
                }, {
                    label: 'This Period',
                    data: [{{ $carrier_revenue_stats['custom_revenue_price'] }}, {{ $carrier_revenue_stats['custom_revenue_paid'] }}],
                    backgroundColor: 'rgba(255, 159, 64, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Forecast Chart
    const forecastCtx = document.getElementById('forecastChart');
    if (forecastCtx) {
        new Chart(forecastCtx, {
            type: 'pie',
            data: {
                labels: ['To Be Invoiced', 'Invoiced Not Paid'],
                datasets: [{
                    data: [{{ $forecast_stats['total']['to_be_invoiced'] }}, {{ $forecast_stats['total']['invoiced_not_paid'] }}],
                    backgroundColor: [
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(54, 162, 235, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
}

function exportData(format, type) {
    const params = new URLSearchParams(window.location.search);
    params.set('format', format);
    params.set('type', type);

    window.location.href = '{{ route("report.export") }}?' + params.toString();
}
</script>

<style>
/* Performance optimized styles */
.card {
    transform: translateZ(0);
    backface-visibility: hidden;
}

canvas {
    transform: translateZ(0);
}

[data-stat] {
    font-variant-numeric: tabular-nums;
}

.dashboard-loading {
    pointer-events: none;
    opacity: 0.7;
}
</style>

@endsection
