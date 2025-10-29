@extends("layouts.app2")

@section('conteudo')


/* 4. ADICIONAR/ATUALIZAR o CSS para as novas animações */

<style>
/* Animações para campos preenchidos automaticamente */
@keyframes fillHighlight {
    0% {
        background-color: #ffffff;
        transform: scale(1);
    }
    50% {
        background-color: #d4edda;
        transform: scale(1.02);
    }
    100% {
        background-color: #e8f5e8;
        transform: scale(1);
    }
}

@keyframes filterHighlight {
    0% {
        background-color: transparent;
        border-left: none;
    }
    50% {
        background-color: rgba(40, 167, 69, 0.3);
        border-left: 3px solid #28a745;
    }
    100% {
        background-color: rgba(40, 167, 69, 0.1);
        border-left: 3px solid #28a745;
    }
}

/* Estilo para campos auto-preenchidos */
.auto-filled {
    background-color: #e8f5e8 !important;
    border-left: 3px solid #28a745 !important;
    transition: all 0.3s ease;
}

/* Estilo para filtros auto-selecionados */
.auto-selected-filter {
    background-color: rgba(40, 167, 69, 0.1) !important;
    border-radius: 4px;
    padding: 4px;
    border-left: 3px solid #28a745;
    transition: all 0.3s ease;
    margin: 2px 0;
}

/* Indicador de campo obrigatório preenchido */
.required-filled::after {
    content: " ✓";
    color: #28a745;
    font-weight: bold;
    font-size: 1.1em;
}

/* Loading indicator para carrier */
#carrier-loading {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Melhorar o select do carrier para mostrar que tem funcionalidade especial */
#carrier-select {
    position: relative;
    background-image: linear-gradient(45deg, transparent 40%, rgba(13, 110, 253, 0.1) 50%, transparent 60%);
    background-size: 20px 20px;
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { background-position: -20px 0; }
    100% { background-position: 20px 0; }
}

#carrier-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Notificações específicas do carrier */
.carrier-setup-notification {
    animation: slideInRight 0.4s ease-out;
    border-left: 4px solid #0d6efd;
}

.carrier-setup-notification.alert-success {
    border-left-color: #28a745;
}

.carrier-setup-notification.alert-warning {
    border-left-color: #ffc107;
}

/* Tooltip personalizado para o carrier select */
#carrier-select::after {
    content: "Selecting a carrier will auto-load its charge setup";
    position: absolute;
    bottom: -25px;
    left: 0;
    font-size: 11px;
    color: #6c757d;
    opacity: 0;
    transition: opacity 0.3s ease;
}

#carrier-select:focus::after {
    opacity: 1;
}

/* Responsivo */
@media (max-width: 768px) {
    .carrier-setup-notification {
        min-width: 300px;
        max-width: 350px;
        right: 10px;
    }

    #carrier-select::after {
        font-size: 10px;
        bottom: -20px;
    }
}
</style>

<div class="container">
    <div class="page-inner">

        {{-- Header --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add Time Line Charge</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Time Line Charges</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Add New</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header d-flex align-items-center">
                        <div class="seta-voltar">
                            <a href="{{ route('time_line_charges.index') }}"><i class="fas fa-arrow-left"></i></a>
                        </div>
                        <h4 class="card-title ms-2">Time Line Charge Information</h4>
                    </div>

                    <div class="card-body">

                        {{-- Form para FILTRAR --}}
                        <form id="filter-form" method="GET" action="{{ route('time_line_charges.create') }}" class="mb-4">
                            {{-- Filtros de data --}}
                            <div class="row mb-3 border p-3 rounded">

                                <div class="col-md-4 mb-3">
    <label class="form-label">Date Start</label>
    <div class="d-flex align-items-center">
        <input type="date" name="date_start" class="form-control me-2"
            value="{{ request('date_start') ? \Carbon\Carbon::parse(request('date_start'))->format('Y-m-d') : '' }}">

        @if(request('date_start'))
            <span class="badge bg-light text-dark">
                {{ \Carbon\Carbon::parse(request('date_start'))->format('m/d/Y') }}
            </span>
        @else
            <span class="text-muted">-</span>
        @endif
    </div>
</div>

<div class="col-md-4 mb-3">
    <label class="form-label">Date End</label>
    <div class="d-flex align-items-center">
        <input type="date" name="date_end" class="form-control me-2"
            value="{{ request('date_end') ? \Carbon\Carbon::parse(request('date_end'))->format('Y-m-d') : '' }}">

        @if(request('date_end'))
            <span class="badge bg-light text-dark">
                {{ \Carbon\Carbon::parse(request('date_end'))->format('m/d/Y') }}
            </span>
        @else
            <span class="text-muted">-</span>
        @endif
    </div>
</div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        Carrier
                                        <span class="text-danger">*</span>
                                        <span class="badge bg-info ms-2" title="Selecting a carrier will automatically load its charge setup">
                                            <i class="fas fa-magic"></i> Auto Setup
                                        </span>
                                    </label>
                                    <select id="carrier-select" name="carrier_id" class="form-select" required>
                                        <option value="" selected>Select Carrier</option>
                                        <option value="all" @selected(old('carrier_id', request('carrier_id')) == 'all')>-- All Carriers</option>
                                        @foreach ($carriers as $carrier)
                                            <option value="{{ $carrier->id }}" @selected(old('carrier_id', request('carrier_id')) == $carrier->id)>
                                                {{ $carrier->company_name }} 1
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Charge setup will be loaded automatically for the selected carrier
                                    </div>
                                </div>
                            </div>

                            <div id="charge-setup" class="row mb-3 border p-3 rounded bg-light d-none">
                              <div>
                                  <div class="col-md-3 mb-3">
                                      <label class="form-label">Charge Setup</label>
                                      <select name="amount_type" class="form-select readonly-select">
                                          <option value="" disabled {{ !request('amount_type') ? 'selected' : '' }}>Select...</option>
                                          <option value="price" @selected(request('amount_type')==='price')>Price</option>
                                          <option value="paid_amount" @selected(request('amount_type')==='paid_amount')>Paid Amount</option>
                                      </select>
                                  </div>
                              </div>

                              @foreach ([
                                  'actual_delivery_date' => 'Actual Delivery Date',
                                  'actual_pickup_date' => 'Actual Pickup Date',
                                  'creation_date' => 'Creation Date',
                                  'invoice_date' => 'Invoice Date',
                                  'receipt_date' => 'Receipt Date',
                                  'scheduled_pickup_date' => 'Scheduled Pickup Date',
                                  'scheduled_delivery_date' => 'Scheduled Delivery Date'
                              ] as $field => $label)
                                  <div class="col-md-3 col-6 mb-2 readonly-wrapper">
                                      <input type="checkbox"
                                            id="filter_{{ $field }}"
                                            name="filters[{{ $field }}]"
                                            value="1"
                                            @checked(request()->input("filters.$field"))
                                            class="readonly-checkbox-check">
                                      <label for="filter_{{ $field }}" class="ms-1">{{ $label }}</label>
                                  </div>
                              @endforeach
                          </div>

                          <div>
                            <div class="col-md-4 d-flex align-items-end">
                              <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                          </div>
                        </form>

                        {{-- Tabela de loads filtrados --}}


                        {{-- Tabela de loads filtrados --}}
@if(!empty($loads) && $loads->count() > 0)
    <div class="table-responsive mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Filtered Loads ({{ $loads->count() }} records)</h5>
            <div class="text-muted">
                Total: ${{ number_format($totalAmount ?? 0, 2) }}
            </div>
        </div>

        {{-- ⭐ NOVA TABELA COM TODAS AS COLUNAS DE FILTROS --}}
        <table class="table table-striped table-bordered table-hover table-sm">
            <thead class="table-dark">
                <tr>
                    <th class="text-center" style="width: 50px;">
                        <input type="checkbox" id="select-all-loads" title="Select/Deselect All">
                    </th>
                    <th style="min-width: 100px;">LOAD ID</th>
                    <th style="min-width: 120px;">CARRIER</th>
                    <th style="min-width: 100px;">DRIVER</th>
                    <th style="min-width: 120px;">DISPATCHER</th>
                    <th class="text-end" style="min-width: 100px;">PRICE</th>
                    <th class="text-center" style="min-width: 120px;">CHARGE STATUS</th>

                    {{-- ⭐ COLUNAS DOS FILTROS DE DATAS --}}
                    <th class="text-center bg-info text-white" style="min-width: 120px;">
                        <small>CREATION DATE</small>
                    </th>
                    <th class="text-center bg-info text-white" style="min-width: 120px;">
                        <small>ACTUAL PICKUP</small>
                    </th>
                    <th class="text-center bg-info text-white" style="min-width: 120px;">
                        <small>ACTUAL DELIVERY</small>
                    </th>
                    <th class="text-center bg-info text-white" style="min-width: 120px;">
                        <small>SCHEDULED PICKUP</small>
                    </th>
                    <th class="text-center bg-info text-white" style="min-width: 120px;">
                        <small>SCHEDULED DELIVERY</small>
                    </th>
                    <th class="text-center bg-info text-white" style="min-width: 120px;">
                        <small>INVOICE DATE</small>
                    </th>
                    <th class="text-center bg-info text-white" style="min-width: 120px;">
                        <small>RECEIPT DATE</small>
                    </th>

                    {{-- ⭐ CAMPO AMOUNT_TYPE (PRICE vs PAID_AMOUNT) --}}
                    <th class="text-end bg-warning text-dark" style="min-width: 100px;">
                        <small>PAID AMOUNT</small>
                    </th>

                    <th class="text-center" style="width: 100px;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loads as $load)
                    <tr id="load-row-{{ $load->id }}" class="{{ $load->already_charged ? 'table-warning' : '' }}">
                        {{-- Checkbox --}}
                        <td class="text-center">
                            <input type="checkbox"
                                   class="load-checkbox"
                                   data-load-id="{{ $load->load_id }}"
                                   {{ $load->already_charged ? '' : 'checked' }}>
                        </td>

                        {{-- Load ID --}}
                        <td>
                            <strong>{{ $load->load_id }}</strong>
                            @if($load->already_charged)
                                <br>
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Already charged
                                </small>
                            @endif
                        </td>

                        {{-- Carrier --}}
                        <td>
                            @if($load->carrier)
                                <strong>{{ $load->carrier->company_name ?? $load->carrier->user->name ?? '-' }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Driver --}}
                        <td>
                            {{ $load->driver ?? '-' }}
                        </td>

                        {{-- Dispatcher --}}
                        <td>
                            @if($load->dispatcher)
                                {{ $load->dispatcher->user->name ?? '-' }}
                            @else
                                <span class="text-muted">{{ $load->dispatcher ?? '-' }}</span>
                            @endif
                        </td>

                        {{-- Price --}}
                        <td class="text-end">
                            @php
                                $price = $load->price ?? 0;
                            @endphp
                            <strong class="{{ $price > 0 ? 'text-success' : 'text-muted' }}">
                                ${{ number_format($price, 2) }}
                            </strong>
                        </td>


                        {{-- Charge Status --}}
                        <td class="text-center">
                            @if($load->already_charged)
                                <span class="badge bg-warning text-dark"
                                      data-bs-toggle="tooltip"
                                      title="Charged in Invoice: {{ $load->charge_info['invoice_id'] }} on {{ $load->charge_info['charge_date'] }}">
                                    <i class="fas fa-file-invoice"></i>
                                    Already Charged
                                </span>
                                <br>
                                <small class="text-muted">
                                    Invoice: {{ $load->charge_info['invoice_id'] }}
                                </small>
                            @else
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i>
                                    Available
                                </span>
                            @endif
                        </td>

                        {{-- ⭐ COLUNAS DOS FILTROS DE DATAS --}}

                        {{-- Creation Date --}}
                        <td class="text-center">
                            @if($load->creation_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->creation_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Actual Pickup Date --}}
                        <td class="text-center">
                            @if($load->actual_pickup_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->actual_pickup_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Actual Delivery Date --}}
                        <td class="text-center">
                            @if($load->actual_delivery_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->actual_delivery_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Scheduled Pickup Date --}}
                        <td class="text-center">
                            @if($load->scheduled_pickup_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->scheduled_pickup_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Scheduled Delivery Date --}}
                        <td class="text-center">
                            @if($load->scheduled_delivery_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->scheduled_delivery_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Invoice Date --}}
                        <td class="text-center">
                            @if($load->invoice_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->invoice_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Receipt Date --}}
                        <td class="text-center">
                            @if($load->receipt_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->receipt_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- ⭐ PAID AMOUNT (para comparar com PRICE) --}}
                        <td class="text-end">
                            @php
                                $paidAmount = $load->paid_amount ?? 0;
                            @endphp
                            @if($paidAmount > 0)
                                <strong class="text-info">
                                    ${{ number_format($paidAmount, 2) }}
                                </strong>
                            @else
                                <span class="text-muted">$0.00</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger delete-load-btn"
                                    data-load-id="{{ $load->id }}"
                                    data-load-number="{{ $load->load_id }}"
                                    title="Remove this load from invoice">
                                <i class="fas fa-trash"></i>
                            </button>
                            @if($load->already_charged)
                                <button type="button"
                                        class="btn btn-sm btn-outline-info view-previous-charge-btn ms-1"
                                        data-invoice-id="{{ $load->charge_info['invoice_id'] }}"
                                        data-internal-id="{{ $load->charge_info['internal_id'] }}"
                                        title="View previous charge details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th></th>
                    <th colspan="7" class="text-end">
                        TOTAL:
                        <small class="text-muted">(Selected loads only)</small>
                    </th>
                    {{-- Colunas de datas vazias --}}
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    {{-- Total na coluna do Paid Amount --}}
                    <th class="text-end text-info">
                        <strong>
                            @php
                                $totalPaidAmount = $loads->sum('paid_amount') ?? 0;
                            @endphp
                            ${{ number_format($totalPaidAmount, 2) }}
                        </strong>
                    </th>
                    <th></th>
                </tr>
                <tr>
                    <th></th>
                    <th colspan="5" class="text-end">
                        <small class="text-primary">TOTAL PRICE ({{ request('amount_type', 'price') === 'price' ? 'Selected' : 'Reference' }}):</small>
                    </th>
                    <th class="text-end text-primary">
                        <strong id="table-total">${{ number_format($totalAmount ?? 0, 2) }}</strong>
                    </th>
                    {{-- Colunas vazias --}}
                    <th colspan="8"></th>
                </tr>
            </tfoot>
        </table>

        {{-- ⭐ LEGENDA PARA EXPLICAR AS CORES --}}
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-light border">
                    <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Table Legend:</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <small>
                                <span class="badge bg-info me-2">Blue Headers</span> = Filter Date Columns<br>
                                <span class="badge bg-warning text-dark me-2">Yellow Header</span> = Paid Amount Column<br>
                                <span class="badge bg-light text-dark me-2">Date Badges</span> = Actual Date Values
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small>
                                <span class="badge bg-success me-2">Green Price</span> = Has Value<br>
                                <span class="badge bg-info me-2">Blue Paid Amount</span> = Has Payment<br>
                                <span class="text-muted">Gray Text</span> = No Value/Empty
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ⭐ INFORMAÇÃO DOS FILTROS APLICADOS --}}
        @if(request()->hasAny(['filters']))
            <div class="alert alert-info">
                <h6><i class="fas fa-filter me-2"></i>Applied Filters:</h6>
                <div class="row">
                    @php
                        $filterLabels = [
                            'actual_delivery_date' => 'Actual Delivery Date',
                            'actual_pickup_date' => 'Actual Pickup Date',
                            'creation_date' => 'Creation Date',
                            'invoice_date' => 'Invoice Date',
                            'receipt_date' => 'Receipt Date',
                            'scheduled_pickup_date' => 'Scheduled Pickup Date',
                            'scheduled_delivery_date' => 'Scheduled Delivery Date'
                        ];
                        $activeFilters = [];
                        if (request('filters')) {
                            foreach (request('filters') as $filter => $value) {
                                if ($value === "1") {
                                    $activeFilters[] = $filterLabels[$filter] ?? $filter;
                                }
                            }
                        }
                    @endphp

                    @if(!empty($activeFilters))
                        <div class="col-md-8">
                            <strong>Date Filters:</strong>
                            @foreach($activeFilters as $filter)
                                <span class="badge bg-primary me-1">{{ $filter }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="col-md-8">
                            <span class="text-muted">Using default filter: <strong>Creation Date</strong></span>
                        </div>
                    @endif

                    <div class="col-md-4">
                        <strong>Date Range:</strong>
                        @if(request('date_start'))
                            {{ \Carbon\Carbon::parse(request('date_start'))->format('m/d/Y') }}
                        @else
                            -
                        @endif
                        to
                        @if(request('date_end'))
                            {{ \Carbon\Carbon::parse(request('date_end'))->format('m/d/Y') }}
                        @else
                            -
                        @endif
                    </div>

                </div>

                <div class="mt-2">
                    <strong>Amount Type:</strong>
                    <span class="badge bg-{{ request('amount_type') === 'paid_amount' ? 'warning' : 'success' }}">
                        {{ request('amount_type') === 'paid_amount' ? 'Paid Amount' : 'Price' }}
                    </span>
                </div>
            </div>
        @endif

        {{-- Resumo de cargas duplicadas --}}
        @php
            $duplicateCount = $loads->where('already_charged', true)->count();
        @endphp
        @if($duplicateCount > 0)
            <div class="alert alert-warning">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Warning:</strong> {{ $duplicateCount }} load(s) have already been charged in other invoices.
                        <br>
                        <small>You can still include them by checking the corresponding checkboxes if you want to charge them again.</small>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-warning" id="select-duplicates">
                        Select Duplicate Loads
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" id="select-available-only">
                        Select Available Only
                    </button>
                </div>
            </div>
        @endif
    </div>
@elseif(request()->hasAny(['carrier_id', 'date_start', 'date_end']))
    <div class="alert alert-warning text-center">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>No loads found</strong> with the selected filters.
        <div class="mt-2 small text-muted">
            Try adjusting your date range or selecting different filter options.
        </div>
    </div>
@endif



                        {{-- Form para SALVAR --}}
                        <form id="save-form">
                            @csrf

                            {{-- Summary Information --}}
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Total Loads</label>
                                    <input type="text" class="form-control" readonly value="{{ $loads->count() }} loads">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Amount Type</label>
                                    <input type="text" class="form-control" readonly value="{{ ucfirst(str_replace('_', ' ', request('amount_type', 'price'))) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Date Range</label>
                                    <input type="text" class="form-control" readonly value="{{ request('date_start') }} to {{ request('date_end') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Total Amount</label>
                                    <input type="number"
                                           name="total_amount"
                                           id="total_amount"
                                           class="form-control fw-bold text-success"
                                           readonly
                                           value="{{ $totalAmount ?? 0 }}">
                                </div>
                            </div>

                            {{-- Hidden fields for form data --}}
                            <input type="hidden" name="carrier_id" value="{{ request('carrier_id') }}">
                            <input type="hidden" name="date_start" value="{{ request('date_start') }}">
                            <input type="hidden" name="date_end" value="{{ request('date_end') }}">
                            <input type="hidden" name="amount_type" value="{{ request('amount_type', 'price') }}">

                            {{-- Dispatcher, Carrier and Due Date Selection --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Dispatcher <span class="text-danger">*</span></label>
                                    <select name="dispatcher_id" class="form-select form-select-lg" required>
                                        <option value="">Select Dispatcher</option>
                                        @foreach ($dispatchers as $dispatcher)
                                            <option value="{{ $dispatcher->id }}">
                                                {{ $dispatcher->user->name ?? $dispatcher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Carrier</label>
                                    @if(request('carrier_id') === 'all')
                                        <input type="text" class="form-control form-select-lg" readonly value="All Carriers">
                                    @else
                                        @php
                                            $selectedCarrier = $carriers->firstWhere('id', request('carrier_id'));
                                        @endphp
                                        <input type="text"
                                            id="carrier-display-field"
                                            class="form-control form-select-lg"
                                            readonly
                                            value="Select a Carrier">
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Due Date <span class="text-danger">*</span></label>
                                    <input type="date"
                                           name="due_date"
                                           id="due_date"
                                           class="form-control form-control-lg"
                                           required
                                           min="{{ date('Y-m-d') }}"
                                           value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                                    <div class="form-text">Default: 30 days from today</div>
                                </div>
                            </div>

                            {{-- Payment Terms (Optional) --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Payment Terms</label>
                                    <select name="payment_terms" class="form-select form-select-lg">
                                        <option value="">Select Payment Terms</option>
                                        <option value="net_15">Net 15 days</option>
                                        <option value="net_30" selected>Net 30 days</option>
                                        <option value="net_45">Net 45 days</option>
                                        <option value="net_60">Net 60 days</option>
                                        <option value="due_on_receipt">Due on Receipt</option>
                                        <option value="custom">Custom Terms</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Invoice Notes (Optional)</label>
                                    <textarea name="invoice_notes"
                                              class="form-control"
                                              rows="3"
                                              placeholder="Add any special instructions or notes for this invoice..."></textarea>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="row">
                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>
                                        Save Time Line Charge
                                    </button>
                                    <button id="open-additional-service" type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#additionalService">
                                        <i class="fas fa-plus me-2"></i>
                                        Add Additional Service
                                    </button>
                                    <a href="{{ route('time_line_charges.index') }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times me-2"></i>
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>

                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="additionalService" tabindex="-1" aria-labelledby="additionalServiceLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="additional-service-form" action="{{ route('additional_services.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5 text-dark" id="additionalServiceLabel">Add Additional Service</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          {{-- Form fields --}}
          <div class="mb-3">
            <label for="describe" class="form-label">Description service</label>
            <input type="text" class="form-control" id="describe" name="describe" required>
          </div>

          <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" step="any" class="form-control" id="quantity" name="quantity" required>
          </div>

          <div class="mb-3">
            <label for="value" class="form-label">Unit Value</label>
            <input type="number" step="any" class="form-control" id="value" name="value" required>
          </div>

          <div class="mb-3">
            <label for="total" class="form-label">Total</label>
            <input type="number" step="any" class="form-control" id="total" name="total" readonly>
          </div>

          {{-- Campos de Parcelamento --}}
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="is_installment" name="is_installment" value="1">
              <label class="form-check-label" for="is_installment">
                Enable Installment Payment
              </label>
            </div>
          </div>

          <div id="installment-fields" class="d-none">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="installment_type" class="form-label">Period Type</label>
                  <select class="form-select" id="installment_type" name="installment_type">
                    <option value="">Select period</option>
                    <option value="weeks">Weeks</option>
                    <option value="months">Months</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="installment_count" class="form-label">Number of Installments</label>
                  <input type="number" class="form-control" id="installment_count" name="installment_count" min="2" max="12">
                </div>
              </div>
            </div>
          </div>

          <!-- <div class="mb-3">
            <label for="carrier_id" class="form-label">Carrier</label>
            <select class="form-select" id="carrier_id" name="carrier_id" required>
              <option value="" disabled selected>Select Carrier</option>
              @foreach($carriers as $carrier)
                <option value="{{ $carrier->id }}">{{ $carrier->user ? $carrier->user->name : $carrier->company_name }}</option>
              @endforeach
            </select>
          </div> -->

          {{-- Tabela PENDING --}}
          <h5 class="mt-4">Pending Services</h5>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Description</th>
                  <th>Quantity</th>
                  <th>Value</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Carrier</th>
                  <th>Installment</th>
                  <th>Created At</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="additional-services-table-body">
                <tr>
                  <td><span id="p_describe"></span></td>
                  <td><span id="p_quantity"></span></td>
                  <td><span id="p_value"></span></td>
                  <td><span id="p_total"></span></td>
                  <td><span id="p_status"></span></td>
                  <td><span id="p_carrier_id"></span></td>
                  <td><span id="p_created_at"></span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="charge-now">Charge Now</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>


{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Captura os filtros marcados -->
 <script>

   function getFilterCheckboxes() {
     const filterInputs = document.querySelectorAll('#filter-form input[type="checkbox"][name^="filters["]');
    const filters = {};

    filterInputs.forEach((checkbox) => {
        const name = checkbox.name.match(/filters\[(.*?)\]/)?.[1];
        if (checkbox.checked && name) {
            filters[name] = true;
          }
        });

        return filters;
      }
</script>


<script>
document.getElementById('save-form')?.addEventListener('submit', function (e) {
    e.preventDefault();

    // Verifica se há uma tabela com dados
    const table = document.querySelector('table tbody');
    if (!table || table.rows.length === 0) {
        alert('No loads found to create invoice. Please apply filters first.');
        return;
    }

    // Verifica se há alguma linha válida (não mensagens de erro)
    const validRows = Array.from(table.rows).filter(row => {
        const firstCell = row.cells[0];
        return firstCell && !firstCell.textContent.includes('No loads') && !firstCell.textContent.includes('remaining');
    });

    if (validRows.length === 0) {
        alert('No valid loads available to create invoice.');
        return;
    }

    // ⭐ CORRIGIDO: Captura carrier_id do select correto
    const urlParams = new URLSearchParams(window.location.search);
    const carrierId = urlParams.get('carrier_id') ||
                     document.querySelector('#carrier-select')?.value ||
                     document.querySelector('input[name="carrier_id"]')?.value;

    console.log('Carrier ID encontrado:', carrierId); // Debug

    if (!carrierId || carrierId === '') {
        alert('Please select a Carrier.');
        document.querySelector('#carrier-select')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        document.querySelector('#carrier-select')?.focus();
        return;
    }

    // Verifica se dispatcher foi selecionado
    const dispatcherId = document.querySelector('select[name="dispatcher_id"]')?.value;
    if (!dispatcherId) {
        alert('Please select a Dispatcher.');
        document.querySelector('select[name="dispatcher_id"]')?.focus();
        return;
    }

    // Verifica se a data de vencimento foi preenchida
    const dueDate = document.querySelector('input[name="due_date"]')?.value;
    if (!dueDate) {
        alert('Please select a Due Date.');
        document.querySelector('input[name="due_date"]')?.focus();
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ⭐ DEBUG: Verificar quais checkboxes existem na página
    console.log('=== CHECKBOX DEBUG ===');

    // Tentar diferentes seletores para encontrar os checkboxes
    const possibleSelectors = [
        '.load-checkbox',
        'input[type="checkbox"]',
        'input[data-load-id]',
        'tbody input[type="checkbox"]',
        '.load-checkbox:checked',
        'input[type="checkbox"]:checked'
    ];

    possibleSelectors.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        console.log(`${selector}: encontrados ${elements.length} elementos`);
        if (elements.length > 0) {
            console.log('Primeiro elemento:', elements[0]);
            console.log('Classes:', elements[0].className);
            console.log('Atributos data-load-id:', elements[0].getAttribute('data-load-id'));
        }
    });

    // ⭐ BUSCA INTELIGENTE: Tentar diferentes formas de encontrar checkboxes selecionados
    let loadIds = [];
    let selectedCheckboxes = [];

    // Método 1: Tentar .load-checkbox primeiro
    selectedCheckboxes = document.querySelectorAll('.load-checkbox:checked');
    console.log('Método 1 (.load-checkbox:checked):', selectedCheckboxes.length);

    // Método 2: Se não encontrar, tentar todos os checkboxes na tabela
    if (selectedCheckboxes.length === 0) {
        selectedCheckboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');
        console.log('Método 2 (tbody input[type="checkbox"]:checked):', selectedCheckboxes.length);
    }

    // Método 3: Se ainda não encontrar, tentar checkboxes com data-load-id
    if (selectedCheckboxes.length === 0) {
        selectedCheckboxes = document.querySelectorAll('input[data-load-id]:checked');
        console.log('Método 3 (input[data-load-id]:checked):', selectedCheckboxes.length);
    }

    // Método 4: Se ainda não encontrar, mostrar todos os checkboxes marcados
    if (selectedCheckboxes.length === 0) {
        selectedCheckboxes = document.querySelectorAll('input[type="checkbox"]:checked');
        console.log('Método 4 (todos os checkboxes marcados):', selectedCheckboxes.length);
    }

    // Extrair load_ids dos checkboxes encontrados
    selectedCheckboxes.forEach((checkbox, index) => {
        console.log(`Checkbox ${index}:`, checkbox);

        // Tentar diferentes atributos para obter o load_id
        let loadId = checkbox.getAttribute('data-load-id') ||
                    checkbox.value ||
                    checkbox.getAttribute('id')?.replace('load-', '') ||
                    checkbox.closest('tr')?.getAttribute('data-load-id');

        console.log(`Load ID extraído do checkbox ${index}:`, loadId);

        if (loadId) {
            loadIds.push(loadId);
        }
    });

    console.log('Load IDs finais coletados:', loadIds);
    console.log('=== FIM DEBUG ===');

    if (loadIds.length === 0) {
        alert('Please select at least one load to create the invoice.\n\nDEBUG INFO:\n- Checkboxes encontrados: ' + selectedCheckboxes.length + '\n- Verifique o console para mais detalhes');
        console.error('ERRO: Nenhum load ID foi coletado dos checkboxes');
        return;
    }

    // ⭐ CORRIGIDO: Capturar os valores dos parâmetros da URL e formulário
    const dateStart = urlParams.get('date_start') || document.querySelector('input[name="date_start"]')?.value;
    const dateEnd = urlParams.get('date_end') || document.querySelector('input[name="date_end"]')?.value;

    // ⭐ CORRIGIDO: Buscar amount_type do select correto
    const amountType = urlParams.get('amount_type') ||
                      document.querySelector('select[name="amount_type"]')?.value ||
                      document.querySelector('input[name="amount_type"]')?.value ||
                      'price';

    console.log('Amount Type encontrado:', amountType); // Debug

    // Capturar os campos adicionais do formulário
    const paymentTerms = document.querySelector('select[name="payment_terms"]')?.value || '';
    const invoiceNotes = document.querySelector('textarea[name="invoice_notes"]')?.value || '';

    const payload = {
        _token: token,
        total_amount: document.querySelector('#total_amount')?.value || '0',
        carrier_id: carrierId,
        dispatcher_id: dispatcherId,
        date_start: dateStart,
        date_end: dateEnd,
        amount_type: amountType,
        due_date: dueDate,
        payment_terms: paymentTerms,
        invoice_notes: invoiceNotes,
        filters: getFilterCheckboxes(),
        load_ids: loadIds
    };

    // ⭐ Debug melhorado - pode remover depois
    console.log('Payload being sent:', {
        carrier_id: payload.carrier_id,
        dispatcher_id: payload.dispatcher_id,
        amount_type: payload.amount_type,
        date_start: payload.date_start,
        date_end: payload.date_end,
        load_count: loadIds.length,
        sample_loads: loadIds.slice(0, 3),
        filters: payload.filters
    });

    // Desabilita o botão durante o envio
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

    fetch('{{ route('time_line_charges.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify(payload)
    })
    .then(async res => {
        const data = await res.json();

        if (!res.ok) {
            if (data.message) {
                alert(data.message);
            } else {
                alert('Error saving Time Line Charge.');
            }
            throw new Error(data.message || 'Error saving');
        }

        return data;
    })
    .then(data => {
        // Exibe mensagem de sucesso mais detalhada
        let message = data.message || 'Time Line Charge created successfully.';

        if (data.invoice) {
            message += `\nInvoice ID: ${data.invoice}`;
        }

        if (data.criadas && data.criadas.length > 0) {
            message += `\nCreated invoices: ${data.criadas.length}`;
            data.criadas.forEach(invoice => {
                message += `\n- Carrier ${invoice.carrier_id}: ${invoice.invoice}`;
            });
        }

        alert(message);
        window.location.href = '{{ route('time_line_charges.index') }}';
    })
    .catch(err => {
        console.error("Error:", err.message);

        // Reabilita o botão em caso de erro
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});
</script>

<script>
    // Script para gerenciar a exclusão de loads da tabela
document.addEventListener('DOMContentLoaded', function() {


    // Função para atualizar o total da tabela
    function updateTableTotal() {
        let total = 0;
        const priceColumns = document.querySelectorAll('tbody tr td:nth-child(6)'); // Coluna do price

        priceColumns.forEach(function(cell) {
            const priceText = cell.textContent.replace(/[$,]/g, '').trim();
            const price = parseFloat(priceText) || 0;
            total += price;
        });

        // Atualiza o total na tabela
        const totalElement = document.getElementById('table-total');
        if (totalElement) {
            totalElement.textContent = '$' + total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Atualiza o total no formulário
        const totalAmountInput = document.getElementById('total_amount');
        if (totalAmountInput) {
            totalAmountInput.value = total.toFixed(2);
        }

        // Atualiza o contador de registros
        const remainingRows = document.querySelectorAll('tbody tr').length;
        const headerCount = document.querySelector('h5');
        if (headerCount) {
            headerCount.textContent = `Filtered Loads (${remainingRows} records)`;
        }

        return { total, remainingRows };
    }

    // Função para deletar uma load
    function deleteLoad(loadId, loadNumber, buttonElement) {
        if (!confirm(`Are you sure you want to remove Load ${loadNumber} from this invoice?`)) {
            return;
        }

        // Desabilita o botão durante a operação
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Remove a linha da tabela imediatamente (feedback visual)
        const row = buttonElement.closest('tr');
        if (row) {
            row.style.opacity = '0.5';
            row.style.transition = 'opacity 0.3s ease';

            setTimeout(() => {
                row.remove();
                const result = updateTableTotal();

                // Se não há mais loads, mostra mensagem
                if (result.remainingRows === 0) {
                    const tbody = document.querySelector('tbody');
                    if (tbody) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No loads remaining. Please apply filters to add loads to the invoice.
                                </td>
                            </tr>
                        `;
                    }

                    // Oculta o formulário de salvamento se não há loads
                    const saveForm = document.getElementById('save-form');
                    if (saveForm) {
                        saveForm.closest('.border-top')?.style.setProperty('display', 'none');
                    }
                }

                // Mostra mensagem de sucesso
                showNotification(`Load ${loadNumber} removed successfully!`, 'success');
            }, 300);
        }
    }

    // Função para mostrar notificações
    function showNotification(message, type = 'info') {
        // Remove notificações existentes
        const existingNotifications = document.querySelectorAll('.notification-toast');
        existingNotifications.forEach(n => n.remove());

        // Cria nova notificação
        const notification = document.createElement('div');
        notification.className = `notification-toast alert alert-${type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
            max-width: 400px;
        `;

        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Remove automaticamente após 3 segundos
        setTimeout(() => {
            if (notification && notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    // Event listener para os botões de delete
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-load-btn')) {
            e.preventDefault();

            const button = e.target.closest('.delete-load-btn');
            const loadId = button.getAttribute('data-load-id');
            const loadNumber = button.getAttribute('data-load-number');

            if (loadId && loadNumber) {
                deleteLoad(loadId, loadNumber, button);
            }
        }
    });

    // Função para recarregar a tabela (opcional - para implementação futura)
    window.reloadTable = function() {
        const currentUrl = new URL(window.location.href);
        window.location.reload();
    };

    // Função para adicionar load de volta (opcional - para implementação futura)
    window.addLoadBack = function(loadId) {
        console.log('Adding load back:', loadId);
        // Implementar se necessário
    };
});
</script>



<script>

    // Script para gerenciar cargas duplicadas e seleção
document.addEventListener('DOMContentLoaded', function() {

    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Gerenciar seleção de cargas
    const selectAllCheckbox = document.getElementById('select-all-loads');
    const loadCheckboxes = document.querySelectorAll('.load-checkbox');

    // Função para atualizar o total baseado nas cargas selecionadas
    function updateSelectedTotal() {
        let total = 0;
        let selectedCount = 0;

        loadCheckboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                const row = checkbox.closest('tr');
                const priceCell = row.querySelector('td:nth-child(7)'); // Coluna do price
                const priceText = priceCell.textContent.replace(/[$,]/g, '').trim();
                const price = parseFloat(priceText) || 0;
                total += price;
                selectedCount++;
            }
        });

        // Atualiza o total na tabela
        const totalElement = document.getElementById('table-total');
        if (totalElement) {
            totalElement.textContent = '$' + total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Atualiza o total no formulário
        const totalAmountInput = document.getElementById('total_amount');
        if (totalAmountInput) {
            totalAmountInput.value = total.toFixed(2);
        }

        // Atualiza o contador de registros
        const headerCount = document.querySelector('h5');
        if (headerCount) {
            const totalLoads = loadCheckboxes.length;
            headerCount.textContent = `Filtered Loads (${totalLoads} total, ${selectedCount} selected)`;
        }

        return { total, selectedCount };
    }

    // Select All functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            loadCheckboxes.forEach(function(checkbox) {
                checkbox.checked = this.checked;
            }, this);
            updateSelectedTotal();
        });
    }

    // Individual checkbox change
    loadCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            updateSelectedTotal();

            // Atualizar o estado do select all
            if (selectAllCheckbox) {
                const checkedCount = document.querySelectorAll('.load-checkbox:checked').length;
                const totalCount = loadCheckboxes.length;

                selectAllCheckbox.checked = checkedCount === totalCount;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
            }
        });
    });

    // Botão para selecionar apenas cargas duplicadas
    const selectDuplicatesBtn = document.getElementById('select-duplicates');
    if (selectDuplicatesBtn) {
        selectDuplicatesBtn.addEventListener('click', function() {
            loadCheckboxes.forEach(function(checkbox) {
                const row = checkbox.closest('tr');
                const isDuplicate = row.classList.contains('table-warning');
                checkbox.checked = isDuplicate;
            });
            updateSelectedTotal();
        });
    }

    // Botão para selecionar apenas cargas disponíveis
    const selectAvailableBtn = document.getElementById('select-available-only');
    if (selectAvailableBtn) {
        selectAvailableBtn.addEventListener('click', function() {
            loadCheckboxes.forEach(function(checkbox) {
                const row = checkbox.closest('tr');
                const isDuplicate = row.classList.contains('table-warning');
                checkbox.checked = !isDuplicate;
            });
            updateSelectedTotal();
        });
    }

    // Visualizar detalhes da cobrança anterior
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-previous-charge-btn')) {
            const button = e.target.closest('.view-previous-charge-btn');
            const invoiceId = button.getAttribute('data-invoice-id');
            const internalId = button.getAttribute('data-internal-id');

            showPreviousChargeModal(invoiceId, internalId);
        }
    });

    // Função para mostrar modal com detalhes da cobrança anterior
    function showPreviousChargeModal(invoiceId, internalId) {
        // Criar modal dinamicamente
        const modalHTML = `
            <div class="modal fade" id="previousChargeModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">Previous Charge Details</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Loading charge details...</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="/time_line_charges/${internalId}" class="btn btn-primary" target="_blank">
                                View Full Invoice
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove modal anterior se existir
        const existingModal = document.getElementById('previousChargeModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Adiciona novo modal
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('previousChargeModal'));
        modal.show();

        // Carregar dados da cobrança
        fetch(`/time_line_charges/${internalId}/details`)
            .then(response => response.json())
            .then(data => {
                const modalBody = document.querySelector('#previousChargeModal .modal-body');
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Invoice ID:</strong><br>
                            ${data.invoice_id || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Total Amount:</strong><br>
                            $${parseFloat(data.price || 0).toFixed(2)}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Carrier:</strong><br>
                            ${data.carrier?.company_name || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Dispatcher:</strong><br>
                            ${data.dispatcher?.user?.name || 'N/A'}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Date Range:</strong><br>
                            ${data.date_start} to ${data.date_end}
                        </div>
                        <div class="col-md-6">
                            <strong>Created:</strong><br>
                            ${new Date(data.created_at).toLocaleDateString('en-US')}
                        </div>
                    </div>
                    <hr>
                    <div>
                        <strong>Loads in this invoice:</strong><br>
                        <div class="mt-2">
                            ${Array.isArray(data.load_ids) ? data.load_ids.map(id =>
                                `<span class="badge bg-secondary me-1">${id}</span>`
                            ).join('') : 'No loads found'}
                        </div>
                    </div>
                `;
            })
            .catch(error => {
                const modalBody = document.querySelector('#previousChargeModal .modal-body');
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error loading charge details. Please try again.
                    </div>
                `;
            });
    }

    // Inicializar total na página
    updateSelectedTotal();

    // Aviso ao tentar salvar com cargas duplicadas
    const originalSaveHandler = document.getElementById('save-form');
    if (originalSaveHandler) {
        originalSaveHandler.addEventListener('submit', function(e) {
            const selectedDuplicates = [];

            loadCheckboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    const row = checkbox.closest('tr');
                    const isDuplicate = row.classList.contains('table-warning');
                    if (isDuplicate) {
                        const loadId = checkbox.getAttribute('data-load-id');
                        selectedDuplicates.push(loadId);
                    }
                }
            });

            if (selectedDuplicates.length > 0) {
                const message = `Warning: You have selected ${selectedDuplicates.length} load(s) that have already been charged:\n\n${selectedDuplicates.join(', ')}\n\nDo you want to proceed with duplicate charging?`;

                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
});

</script>


<script>


  // Script para atualizar automaticamente a data de vencimento baseada nos termos de pagamento
document.addEventListener('DOMContentLoaded', function() {
    const paymentTermsSelect = document.querySelector('select[name="payment_terms"]');
    const dueDateInput = document.querySelector('input[name="due_date"]');

    if (paymentTermsSelect && dueDateInput) {

        // Função para calcular e atualizar a data de vencimento
        function updateDueDate(selectedTerm) {
            const today = new Date();
            let dueDate = new Date(today);

            // ⭐ MAPEAMENTO COMPLETO DOS TERMOS DE PAGAMENTO
            switch(selectedTerm) {
                case 'due_on_receipt':
                    dueDate = new Date(today); // Hoje mesmo
                    break;
                case 'net_15':
                    dueDate.setDate(today.getDate() + 15);
                    break;
                case 'net_30':
                    dueDate.setDate(today.getDate() + 30);
                    break;
                case 'net_45':
                    dueDate.setDate(today.getDate() + 45);
                    break;
                case 'net_60':
                    dueDate.setDate(today.getDate() + 60);
                    break;
                case 'custom':
                    // Para custom, não altera a data - deixa o usuário escolher
                    showNotification('Custom payment terms selected. Please set the due date manually.', 'info');
                    return;
                case '':
                    // Se não selecionou nada, volta ao padrão de 30 dias
                    dueDate.setDate(today.getDate() + 30);
                    break;
                default:
                    // Fallback - tenta extrair número de dias do valor
                    const daysMatch = selectedTerm.match(/(\d+)/);
                    if (daysMatch) {
                        const days = parseInt(daysMatch[1]);
                        dueDate.setDate(today.getDate() + days);
                    } else {
                        // Se não conseguir extrair, usa 30 dias como padrão
                        dueDate.setDate(today.getDate() + 30);
                    }
                    break;
            }

            // Formatar a data para o input (YYYY-MM-DD)
            const formattedDate = dueDate.toISOString().split('T')[0];
            dueDateInput.value = formattedDate;

            // ⭐ FEEDBACK VISUAL MELHORADO
            dueDateInput.style.transition = 'all 0.3s ease';
            dueDateInput.style.backgroundColor = '#d4edda';
            dueDateInput.style.borderLeft = '4px solid #28a745';
            dueDateInput.style.transform = 'scale(1.02)';

            // Mostrar notificação informativa
            const termLabels = {
                'due_on_receipt': 'Due on Receipt (Today)',
                'net_15': 'Net 15 days',
                'net_30': 'Net 30 days',
                'net_45': 'Net 45 days',
                'net_60': 'Net 60 days',
                'custom': 'Custom Terms'
            };

            const termLabel = termLabels[selectedTerm] || selectedTerm;
            const formattedDisplayDate = dueDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });

            if (selectedTerm !== 'custom') {
                showNotification(
                    `Due date updated! ${termLabel} → ${formattedDisplayDate}`,
                    'success'
                );
            }

            // Remover destaque após 2 segundos
            setTimeout(() => {
                dueDateInput.style.removeProperty('background-color');
                dueDateInput.style.removeProperty('border-left');
                dueDateInput.style.removeProperty('transform');
            }, 2000);
        }

        // ⭐ EVENT LISTENER PRINCIPAL
        paymentTermsSelect.addEventListener('change', function() {
            const selectedTerm = this.value;
            console.log('Payment term selected:', selectedTerm); // Debug
            updateDueDate(selectedTerm);
        });

        // ⭐ INICIALIZAÇÃO - Se já há um termo selecionado na página
        const initialTerm = paymentTermsSelect.value;
        if (initialTerm && initialTerm !== '') {
            setTimeout(() => {
                updateDueDate(initialTerm);
            }, 500); // Pequeno delay para garantir que a página carregou
        }
    }

    // ⭐ VALIDAÇÃO ADICIONAL PARA DATA DE VENCIMENTO
    if (dueDateInput) {
        dueDateInput.addEventListener('change', function() {
            validateDueDate(this.value);
        });

        // Também validar quando o campo perde o foco
        dueDateInput.addEventListener('blur', function() {
            validateDueDate(this.value);
        });
    }

    // Função para validar a data de vencimento
    function validateDueDate(dateValue) {
        if (!dateValue) return;

        const selectedDate = new Date(dateValue);
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Reset time to compare only dates

        if (selectedDate < today) {
            // Data no passado
            showNotification('⚠️ Due date cannot be in the past. Adjusting to 30 days from today.', 'warning');

            const defaultDate = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
            dueDateInput.value = defaultDate.toISOString().split('T')[0];

            // Reset payment terms para net_30 se a data foi ajustada
            if (paymentTermsSelect) {
                paymentTermsSelect.value = 'net_30';
            }
        } else if (selectedDate > new Date(today.getTime() + 365 * 24 * 60 * 60 * 1000)) {
            // Data muito no futuro (mais de 1 ano)
            showNotification('ℹ️ Due date is more than 1 year in the future. Please confirm this is correct.', 'info');
        }
    }

    // ⭐ FUNÇÃO PARA MOSTRAR NOTIFICAÇÕES
    function showNotification(message, type = 'info') {
        // Remove notificações existentes
        const existingNotifications = document.querySelectorAll('.due-date-notification');
        existingNotifications.forEach(n => n.remove());

        // Cria nova notificação
        const notification = document.createElement('div');
        notification.className = `due-date-notification alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 80px;
            right: 20px;
            z-index: 1060;
            min-width: 350px;
            max-width: 450px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
        `;

        const iconClass = type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'calendar-alt';

        notification.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="fas fa-${iconClass} me-2 mt-1"></i>
                <div class="flex-grow-1">
                    <strong>Payment Terms</strong><br>
                    <small>${message}</small>
                </div>
                <button type="button" class="btn-close btn-sm" onclick="this.closest('.due-date-notification').remove()"></button>
            </div>
        `;

        document.body.appendChild(notification);

        // Remove automaticamente após 4 segundos
        setTimeout(() => {
            if (notification && notification.parentNode) {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }, 4000);
    }

    // ⭐ INDICADOR VISUAL NO SELECT DE PAYMENT TERMS
    if (paymentTermsSelect) {
        paymentTermsSelect.addEventListener('focus', function() {
            this.style.borderColor = '#0d6efd';
            this.style.boxShadow = '0 0 0 0.2rem rgba(13, 110, 253, 0.25)';
        });

        paymentTermsSelect.addEventListener('blur', function() {
            this.style.removeProperty('border-color');
            this.style.removeProperty('box-shadow');
        });
    }

    console.log('Payment Terms → Due Date script initialized successfully!'); // Debug
});


</script>

<!-- Salvar serviços adicionais -->
<script>
$(document).ready(function () {

  // Função comum para enviar os dados com o tipo de ação
  function submitAdditionalService(actionType) {
    let formData = $('#additional-service-form').serializeArray();

    // Pega carrier_id do localStorage
    const carrierId = localStorage.getItem('carrier_id');
    formData.push({ name: 'carrier_id', value: carrierId });

    // Passa a ação no payload (se precisar usar depois)
    formData.push({ name: 'action_type', value: actionType });

    $.ajax({
      url: '{{ route("additional_services.store") }}',
      type: 'POST',
      data: $.param(formData),
      dataType: 'json',

      success: function (response) {
        if (response.success) {
          alert(response.message);
          $('#additionalService').modal('hide');
          $('#additional-service-form')[0].reset();
        }
      },

      error: function (xhr) {
        if (xhr.status === 422) {
          let errors = xhr.responseJSON.errors;
          let messages = Object.values(errors).map(msgArray => msgArray.join(', ')).join('\n');
          alert("Validation errors:\n" + messages);
        } else {
          alert("Error saving. Please try again.");
        }
      }
    });
  }

  // Clique no botão "Charge Now"
  $('#charge-now').on('click', function () {
    submitAdditionalService('now');

  });

  // Botão 'Charge Last' removido conforme solicitado

});
</script>

<!-- Calcular total de serviços adicionais -->
<script>
  $(document).ready(function () {
    function calcularTotal() {
      const quantity = parseFloat($('#quantity').val()) || 0;
      const value = parseFloat($('#value').val()) || 0;
      const total = quantity * value;

      $('#total').val(total.toFixed(2));
    }

    // Atualiza ao digitar
    $('#quantity, #value').on('input', calcularTotal);

    // Controlar exibição dos campos de parcelamento
    $('#is_installment').on('change', function() {
      if ($(this).is(':checked')) {
        $('#installment-fields').removeClass('d-none');
        $('#installment_type').attr('required', true);
        $('#installment_count').attr('required', true);
      } else {
        $('#installment-fields').addClass('d-none');
        $('#installment_type').removeAttr('required').val('');
        $('#installment_count').removeAttr('required').val('');
      }
    });
  });
</script>

<!-- Listar serviços adicionais -->
<script>
$(document).ready(function () {
  $('#open-additional-service').on('click', function () {
    $.ajax({
      url: '{{ route("additional_services.index") }}',
      type: 'GET',
      dataType: 'json',

      success: function (response) {
        if (response.success) {
          let tbody = $('#additional-services-table-body');
          tbody.empty(); // Limpa conteúdo anterior

          response.data.forEach(service => {
            // Format installment info
            let installmentInfo = '-';
            if (service.is_installment) {
              installmentInfo = `${service.installment_count} ${service.installment_type}`;
            }
            
            tbody.append(`
              <tr>
                <td>${service.describe}</td>
                <td>${service.quantity}</td>
                <td>${service.value}</td>
                <td>${service.total}</td>
                <td>${service.status}</td>
                <td>${service.carrier?.user?.name || '-'}</td>
                <td>${installmentInfo}</td>
                <td>${service.created_at}</td>
                <td>
                  <button type="button" class="btn btn-danger btn-sm" onclick="deleteService(${service.id})">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                </td>
              </tr>
            `);
          });
        }
      },

      error: function (xhr) {
        alert("Error loading additional services.");
        console.error(xhr);
      }
    });
  });
});
</script>

<script>
    document.querySelectorAll('.readonly-checkbox').forEach(cb => {
        cb.addEventListener('click', e => {
            e.preventDefault(); // impede alteração
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {


    function formatDateUS(dateString) {
        if (!dateString) return "-";
        const date = new Date(dateString);
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day   = String(date.getDate()).padStart(2, "0");
        const year  = date.getFullYear();
        return `${month}/${day}/${year}`;
    }

    // Código removido - estava fora do contexto correto


    const carrierSelect = document.getElementById('carrier-select');
    const dispatcherSelect = document.querySelector('select[name="dispatcher_id"]');
    const amountTypeSelect = document.querySelector('select[name="amount_type"]');
    const filterCheckboxes = document.querySelectorAll('input[name^="filters["]');
    const chargeSetupSection = document.getElementById('charge-setup');
    const carrierDisplayField = document.getElementById('carrier-display-field');

    // Desabilitar amount_type inicialmente se a seção estiver oculta
    if (chargeSetupSection && chargeSetupSection.classList.contains('d-none') && amountTypeSelect) {
        amountTypeSelect.disabled = true;
    }

    // ⭐ BUSCAR dados dos carriers do backend para usar no JavaScript
    const carriersData = @json($carriers->pluck('company_name', 'id'));

    if (carrierSelect) {
        // Adicionar evento change ao carrier select
        carrierSelect.addEventListener('change', function() {
            const selectedCarrierId = this.value;

            console.log('Selected Carrier ID:', selectedCarrierId);

            // ⭐ ARMAZENAR carrier_id no localStorage para uso nos serviços adicionais
            if (selectedCarrierId && selectedCarrierId !== '') {
                localStorage.setItem('carrier_id', selectedCarrierId);
            } else {
                localStorage.removeItem('carrier_id');
            }

            // ⭐ ATUALIZAR o campo de exibição do Carrier
            updateCarrierDisplayField(selectedCarrierId);

            // ⭐ CORRIGIDO: MOSTRAR a seção charge-setup para QUALQUER seleção válida (incluindo "all")
            if (selectedCarrierId && selectedCarrierId !== '') {
                // Mostrar seção para qualquer carrier selecionado (específico ou "all")
                if (chargeSetupSection) {
                    chargeSetupSection.classList.remove('d-none');
                    // Habilitar select amount_type quando seção estiver visível
                    const amountTypeSelect = chargeSetupSection.querySelector('select[name="amount_type"]');
                    if (amountTypeSelect) {
                        amountTypeSelect.disabled = false;
                    }
                }

                // ⭐ BUSCAR charge setup para carrier específico ou todos os carriers
                if (selectedCarrierId !== 'all') {
                    loadChargeSetupForCarrier(selectedCarrierId);
                } else {
                    // Para "all carriers", carregar setup combinado de todos os carriers
                    loadChargeSetupForAllCarriers();
                }
            } else {
                // Ocultar seção se nenhum carrier selecionado
                if (chargeSetupSection) {
                    chargeSetupSection.classList.add('d-none');
                    // Desabilitar select amount_type quando seção estiver oculta
                    const amountTypeSelect = chargeSetupSection.querySelector('select[name="amount_type"]');
                    if (amountTypeSelect) {
                        amountTypeSelect.disabled = true;
                    }
                }
                clearAutoFilledFields();
            }
        });
    }


    // ⭐ NOVA FUNÇÃO: Configurações padrão para "All Carriers"
    function setDefaultFieldsForAllCarriers() {
        // Definir amount_type padrão
        if (amountTypeSelect) {
            amountTypeSelect.value = 'price'; // Padrão para todos os carriers
            amountTypeSelect.style.backgroundColor = '#e3f2fd';
            amountTypeSelect.style.borderLeft = '3px solid #2196f3';
        }

        // ⭐ OPCIONAL: Marcar alguns filtros padrão para "all carriers"
        const defaultFiltersForAll = ['creation_date', 'actual_delivery_date']; // Customize conforme necessário

        filterCheckboxes.forEach(checkbox => {
            const filterName = checkbox.name.match(/filters\[(.*?)\]/)?.[1];
            if (defaultFiltersForAll.includes(filterName)) {
                checkbox.checked = true;
                const container = checkbox.closest('.col-md-3, .col-6');
                if (container) {
                    container.style.backgroundColor = 'rgba(33, 150, 243, 0.1)';
                    container.style.borderLeft = '3px solid #2196f3';
                    container.style.borderRadius = '4px';
                    container.style.padding = '4px';
                }
            }
        });

        // Remover destaque após alguns segundos
        setTimeout(() => {
            if (amountTypeSelect) {
                amountTypeSelect.style.removeProperty('background-color');
                amountTypeSelect.style.removeProperty('border-left');
            }
            filterCheckboxes.forEach(checkbox => {
                const container = checkbox.closest('.col-md-3, .col-6');
                if (container) {
                    container.style.removeProperty('background-color');
                    container.style.removeProperty('border-left');
                    container.style.removeProperty('border-radius');
                    container.style.removeProperty('padding');
                }
            });
        }, 5000);
    }



    // ⭐ FUNÇÃO ATUALIZADA: Atualizar campo de exibição do Carrier
    function updateCarrierDisplayField(carrierId) {
        if (!carrierDisplayField) return;

        let displayText = 'Select a Carrier';

        if (carrierId === 'all') {
            displayText = '🏢 All Carriers Selected';
        } else if (carrierId && carriersData[carrierId]) {
            displayText = carriersData[carrierId];
        } else if (carrierId === '') {
            displayText = 'Select a Carrier';
        }

        carrierDisplayField.value = displayText;

        // Adicionar feedback visual temporário com cor diferente para "all"
        if (carrierId === 'all') {
            carrierDisplayField.style.backgroundColor = '#fff3cd';
            carrierDisplayField.style.borderLeft = '3px solid #ffc107';
        } else {
            carrierDisplayField.style.backgroundColor = '#e3f2fd';
            carrierDisplayField.style.borderLeft = '3px solid #2196f3';
        }

        setTimeout(() => {
            carrierDisplayField.style.removeProperty('background-color');
            carrierDisplayField.style.removeProperty('border-left');
        }, 3000);
    }

    function loadChargeSetupForCarrier(carrierId) {
        // Não tentar carregar setup para "all carriers"
        if (carrierId === 'all') {
            console.log('Skipping charge setup load for "all carriers"');
            return;
        }

        // Mostrar indicador de loading
        showLoadingIndicator();

        // Fazer requisição para buscar charge setup do carrier
        fetch(`/charge-setups/by-carrier/${carrierId}`)
            .then(response => response.json())
            .then(data => {
                hideLoadingIndicator();

                if (data.success && data.setup) {
                    // Aplicar dados do charge setup encontrado
                    applyChargeSetupData(data.setup, false); // false = não é modo somente leitura
                    showNotification(`✅ Charge setup applied! ${data.setup.summary}`, 'success');
                } else {
                    // Nenhum setup encontrado para este carrier
                    clearAutoFilledFields();
                    showNotification('ℹ️ No charge setup found for this carrier. Please fill fields manually.', 'info');
                }
            })
            .catch(error => {
                hideLoadingIndicator();
                console.error('Error loading charge setup:', error);
                clearAutoFilledFields();
                showNotification('⚠️ Error loading charge setup. Please fill fields manually.', 'warning');
            });
    }

    function loadChargeSetupForAllCarriers() {
        // Mostrar indicador de loading
        showLoadingIndicator();

        // Fazer requisição para buscar charge setup de todos os carriers
        fetch('/charge-setups/all-carriers')
            .then(response => response.json())
            .then(data => {
                hideLoadingIndicator();

                if (data.success && data.all_carriers_setup) {
                    // Aplicar dados combinados dos charge setups
                    applyAllCarriersSetupData(data.all_carriers_setup);
                    showNotification(`✅ All carriers setup loaded! ${data.all_carriers_setup.summary}`, 'success');
                } else {
                    // Nenhum setup encontrado
                    clearAutoFilledFields();
                    showNotification('ℹ️ No charge setups found for any carrier. Please fill fields manually.', 'info');
                }
            })
            .catch(error => {
                hideLoadingIndicator();
                console.error('Error loading all carriers setup:', error);
                clearAutoFilledFields();
                showNotification('⚠️ Error loading charge setups. Please fill fields manually.', 'warning');
            });
    }

    function applyChargeSetupData(setup, readOnlyMode = false) {
        // 1. Preencher Dispatcher
        if (setup.dispatcher_id && dispatcherSelect) {
            dispatcherSelect.value = setup.dispatcher_id;
            dispatcherSelect.style.backgroundColor = '#e8f5e8';
            dispatcherSelect.style.borderLeft = '3px solid #28a745';
            if (readOnlyMode) {
                dispatcherSelect.disabled = true;
            }
        }

        // 2. Preencher Amount Type
        if (setup.price && amountTypeSelect) {
            amountTypeSelect.value = setup.price;
            amountTypeSelect.style.backgroundColor = '#e8f5e8';
            amountTypeSelect.style.borderLeft = '3px solid #28a745';
            if (readOnlyMode) {
                amountTypeSelect.disabled = true;
            }
        }

        // 3. Aplicar Filtros (checkboxes)
        if (setup.filters && Array.isArray(setup.filters)) {
            // Primeiro, desmarcar todos e remover estilos
            filterCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.closest('.col-md-3, .col-6')?.style.removeProperty('background-color');
                checkbox.closest('.col-md-3, .col-6')?.style.removeProperty('border-left');
                if (readOnlyMode) {
                    checkbox.disabled = true;
                }
            });

            // Depois, marcar os do setup e aplicar estilos
            setup.filters.forEach(filterName => {
                const checkbox = document.querySelector(`input[name="filters[${filterName}]"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    if (readOnlyMode) {
                        checkbox.disabled = true;
                    }
                    const container = checkbox.closest('.col-md-3, .col-6');
                    if (container) {
                        container.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
                        container.style.borderLeft = '3px solid #28a745';
                        container.style.borderRadius = '4px';
                        container.style.padding = '4px';
                    }
                }
            });
        }

        // 4. Scroll suave para mostrar a seção preenchida
        setTimeout(() => {
            if (chargeSetupSection) {
                chargeSetupSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }, 500);

        // 5. Remover destaque após alguns segundos
        setTimeout(() => {
            if (dispatcherSelect) {
                dispatcherSelect.style.removeProperty('background-color');
                dispatcherSelect.style.removeProperty('border-left');
            }
            if (amountTypeSelect) {
                amountTypeSelect.style.removeProperty('background-color');
                amountTypeSelect.style.removeProperty('border-left');
            }
            filterCheckboxes.forEach(checkbox => {
                const container = checkbox.closest('.col-md-3, .col-6');
                if (container) {
                    container.style.removeProperty('background-color');
                    container.style.removeProperty('border-left');
                    container.style.removeProperty('border-radius');
                    container.style.removeProperty('padding');
                }
            });
        }, 5000);

        // 6. Adicionar flag de modo somente leitura se aplicável
        if (readOnlyMode) {
            addReadOnlyFlag();
        }
    }

    function applyAllCarriersSetupData(allCarriersSetup) {
        // Limpar campos primeiro
        clearAutoFilledFields();

        // Aplicar filtros combinados de todos os carriers
        if (allCarriersSetup.combined_filters && Array.isArray(allCarriersSetup.combined_filters)) {
            // Primeiro, desmarcar todos e remover estilos
            filterCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.disabled = true; // Modo somente leitura para All Carriers
                checkbox.closest('.col-md-3, .col-6')?.style.removeProperty('background-color');
                checkbox.closest('.col-md-3, .col-6')?.style.removeProperty('border-left');
            });

            // Depois, marcar os filtros combinados
            allCarriersSetup.combined_filters.forEach(filterName => {
                const checkbox = document.querySelector(`input[name="filters[${filterName}]"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    checkbox.disabled = true; // Modo somente leitura
                    const container = checkbox.closest('.col-md-3, .col-6');
                    if (container) {
                        container.style.backgroundColor = 'rgba(255, 193, 7, 0.1)';
                        container.style.borderLeft = '3px solid #ffc107';
                        container.style.borderRadius = '4px';
                        container.style.padding = '4px';
                    }
                }
            });
        }

        // Configurar dispatcher e amount type para All Carriers
        if (dispatcherSelect) {
            // Para All Carriers, usar o primeiro dispatcher disponível ou manter vazio
            if (allCarriersSetup.carrier_summaries && allCarriersSetup.carrier_summaries.length > 0) {
                const firstDispatcherId = allCarriersSetup.carrier_summaries[0].dispatcher_id;
                if (firstDispatcherId) {
                    dispatcherSelect.value = firstDispatcherId;
                }
            }
            dispatcherSelect.disabled = true;
            dispatcherSelect.style.backgroundColor = '#f8f9fa';
            dispatcherSelect.style.borderLeft = '3px solid #6c757d';
        }

        if (amountTypeSelect) {
            // Para All Carriers, definir como "price" por padrão
            amountTypeSelect.value = 'price';
            amountTypeSelect.disabled = true;
            amountTypeSelect.style.backgroundColor = '#f8f9fa';
            amountTypeSelect.style.borderLeft = '3px solid #6c757d';
        }

        // Adicionar flag de modo somente leitura
        addReadOnlyFlag(true, allCarriersSetup);

        // Scroll suave para mostrar a seção preenchida
        setTimeout(() => {
            if (chargeSetupSection) {
                chargeSetupSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }, 500);
    }

    function addReadOnlyFlag(isAllCarriers = false, setupData = null) {
        // Remover flag anterior se existir
        const existingFlag = document.getElementById('readonly-flag');
        if (existingFlag) {
            existingFlag.remove();
        }

        const flagContainer = document.createElement('div');
        flagContainer.id = 'readonly-flag';
        flagContainer.className = 'alert alert-info mt-3';
        flagContainer.style.borderLeft = '4px solid #17a2b8';

        let flagContent = '';
        if (isAllCarriers && setupData) {
            flagContent = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                        <strong>📋 Modo Somente Leitura - All Carriers</strong><br>
                        <small class="text-muted">
                            Dados combinados de ${setupData.total_carriers} carriers. 
                            Filtros: ${setupData.combined_filters ? setupData.combined_filters.join(', ') : 'Nenhum'}
                        </small>
                    </div>
                </div>
            `;
        } else {
            flagContent = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-lock me-2"></i>
                    <strong>🔒 Modo Somente Leitura</strong>
                    <small class="text-muted ms-2">Dados carregados do Charge Setup</small>
                </div>
            `;
        }

        flagContainer.innerHTML = flagContent;

        // Inserir a flag no início da seção charge setup
        if (chargeSetupSection) {
            const cardBody = chargeSetupSection.querySelector('.card-body');
            if (cardBody) {
                const firstChild = cardBody.firstChild;
                cardBody.insertBefore(flagContainer, firstChild);
            } else {
                // Se não encontrar .card-body, inserir diretamente na seção
                chargeSetupSection.insertBefore(flagContainer, chargeSetupSection.firstChild);
            }
        }
    }

    function clearAutoFilledFields() {
        // Remover flag de somente leitura
        const existingFlag = document.getElementById('readonly-flag');
        if (existingFlag) {
            existingFlag.remove();
        }

        // Reabilitar todos os campos
        if (dispatcherSelect) {
            dispatcherSelect.disabled = false;
            dispatcherSelect.value = '';
            dispatcherSelect.style.removeProperty('background-color');
            dispatcherSelect.style.removeProperty('border-left');
        }

        if (amountTypeSelect) {
            amountTypeSelect.disabled = false;
            amountTypeSelect.value = '';
            amountTypeSelect.style.removeProperty('background-color');
            amountTypeSelect.style.removeProperty('border-left');
        }

        // Desmarcar e reabilitar todos os filtros
        filterCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.disabled = false;
            const container = checkbox.closest('.col-md-3, .col-6');
            if (container) {
                container.style.removeProperty('background-color');
                container.style.removeProperty('border-left');
                container.style.removeProperty('border-radius');
                container.style.removeProperty('padding');
            }
        });
    }


    function showLoadingIndicator() {
        const carrierSelect = document.getElementById('carrier-select');

        // Remover indicador anterior se existir
        const existingIndicator = document.getElementById('carrier-loading');
        if (existingIndicator) {
            existingIndicator.remove();
        }

        const loadingIndicator = document.createElement('div');
        loadingIndicator.id = 'carrier-loading';
        loadingIndicator.className = 'text-primary mt-2';
        loadingIndicator.innerHTML = `
            <small>
                <i class="fas fa-spinner fa-spin me-2"></i>
                Loading charge setup for this carrier...
            </small>
        `;

        carrierSelect.parentElement.appendChild(loadingIndicator);

        // Auto-remover se demorar muito (timeout de 10 segundos)
        setTimeout(() => {
            if (document.getElementById('carrier-loading')) {
                hideLoadingIndicator();
                showNotification('⏱️ Timeout loading setup. Please try again or fill manually.', 'warning');
            }
        }, 10000);
    }

    function hideLoadingIndicator() {
        const loadingIndicator = document.getElementById('carrier-loading');
        if (loadingIndicator) {
            loadingIndicator.remove();
        }
    }

    function showNotification(message, type = 'info') {
        // Remove notificações existentes
        const existingNotifications = document.querySelectorAll('.carrier-setup-notification');
        existingNotifications.forEach(n => n.remove());

        // Cria nova notificação
        const notification = document.createElement('div');
        notification.className = `carrier-setup-notification alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 1060;
            min-width: 350px;
            max-width: 450px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
        `;

        const iconClass = type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle';

        notification.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="fas fa-${iconClass} me-2 mt-1"></i>
                <div class="flex-grow-1">
                    <strong>Auto Setup</strong><br>
                    <small>${message}</small>
                </div>
                <button type="button" class="btn-close btn-sm" onclick="this.closest('.carrier-setup-notification').remove()"></button>
            </div>
        `;

        document.body.appendChild(notification);

        // Remove automaticamente após 4 segundos
        setTimeout(() => {
            if (notification && notification.parentNode) {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }, 4000);
    }

    // ⭐ IMPORTANTE: Se já houver um carrier selecionado na página, carregar setup e mostrar seção
    if (carrierSelect && carrierSelect.value && carrierSelect.value !== '') {
        // ⭐ ARMAZENAR carrier_id no localStorage se já selecionado
        localStorage.setItem('carrier_id', carrierSelect.value);

        // Atualizar campo de exibição
        updateCarrierDisplayField(carrierSelect.value);

        // Mostrar seção imediatamente se carrier já selecionado
        if (chargeSetupSection) {
            chargeSetupSection.classList.remove('d-none');
        }

        // Carregar setup após um pequeno delay
        setTimeout(() => {
            loadChargeSetupForCarrier(carrierSelect.value);
        }, 500);
    }
});

// Function to delete service - Global scope
function deleteService(serviceId) {
  if (confirm('Are you sure you want to delete this service?')) {
    $.ajax({
      url: `/additional_services/${serviceId}`,
      type: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        if (response.success) {
          alert(response.message);
          // Reload the services list
          $('#open-additional-service').click();
        }
      },
      error: function(xhr) {
        alert('Error deleting service. Please try again.');
        console.error(xhr);
      }
    });
  }
}
</script>

@endsection
