@extends("layouts.app2")

@section('conteudo')

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
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Filtered Loads ({{ $loads->count() }} records)</h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="text-muted">
                        Total: ${{ number_format($totalAmount ?? 0, 2) }}
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#selectColums">
                        <i class="fas fa-columns me-1"></i>
                        Select Columns
                    </button>
                </div>
            </div>
        </div>

        {{-- ⭐ NOVA TABELA COM TODAS AS COLUNAS DE FILTROS --}}
        <table class="table table-striped table-bordered table-hover table-sm">
            <thead class="table-dark">
                <tr>
                    <th class="text-center" style="width: 50px;">
                        <input type="checkbox" id="select-all-loads" title="Select/Deselect All">
                    </th>
                    <th data-column="load_id" class="column-load_id" style="min-width: 100px;">LOAD ID</th>
                    <th data-column="carrier" class="column-carrier" style="min-width: 120px;">CARRIER</th>
                    <th data-column="driver" class="column-driver" style="min-width: 100px;">DRIVER</th>
                    <th data-column="dispatcher" class="column-dispatcher" style="min-width: 120px;">DISPATCHER</th>
                    <th data-column="price" class="text-end column-price" style="min-width: 100px;">PRICE</th>
                    <th data-column="charge_status" class="text-center column-charge_status" style="min-width: 120px;">CHARGE STATUS</th>
                    <th data-column="internal_load_id" class="column-internal_load_id" style="display: none; min-width: 120px;">INTERNAL LOAD ID</th>
                    <th data-column="year_make_model" class="column-year_make_model" style="display: none; min-width: 120px;">VEHICLE</th>
                    <th data-column="vin" class="column-vin" style="display: none; min-width: 120px;">VIN</th>
                    <th data-column="lot_number" class="column-lot_number" style="display: none; min-width: 120px;">LOT NUMBER</th>
                    <th data-column="broker_fee" class="text-end column-broker_fee" style="display: none; min-width: 100px;">BROKER FEE</th>
                    <th data-column="driver_pay" class="text-end column-driver_pay" style="display: none; min-width: 100px;">DRIVER PAY</th>
                    <th data-column="payment_status" class="text-center column-payment_status" style="display: none; min-width: 120px;">PAYMENT STATUS</th>
                    <th data-column="invoice_number" class="text-center column-invoice_number" style="display: none; min-width: 120px;">INVOICE NUMBER</th>
                    <th data-column="pickup_name" class="column-pickup_name" style="display: none; min-width: 150px;">PICKUP LOCATION</th>
                    <th data-column="delivery_name" class="column-delivery_name" style="display: none; min-width: 150px;">DELIVERY LOCATION</th>
                    <th data-column="creation_date" class="text-center bg-info text-white column-creation_date" style="min-width: 120px;">
                        <small>CREATION DATE</small>
                    </th>
                    <th data-column="actual_pickup_date" class="text-center bg-info text-white column-actual_pickup_date" style="min-width: 120px;">
                        <small>ACTUAL PICKUP</small>
                    </th>
                    <th data-column="actual_delivery_date" class="text-center bg-info text-white column-actual_delivery_date" style="min-width: 120px;">
                        <small>ACTUAL DELIVERY</small>
                    </th>
                    <th data-column="scheduled_pickup_date" class="text-center bg-info text-white column-scheduled_pickup_date" style="min-width: 120px;">
                        <small>SCHEDULED PICKUP</small>
                    </th>
                    <th data-column="scheduled_delivery_date" class="text-center bg-info text-white column-scheduled_delivery_date" style="min-width: 120px;">
                        <small>SCHEDULED DELIVERY</small>
                    </th>
                    <th data-column="invoice_date" class="text-center bg-info text-white column-invoice_date" style="min-width: 120px;">
                        <small>INVOICE DATE</small>
                    </th>
                    <th data-column="receipt_date" class="text-center bg-info text-white column-receipt_date" style="min-width: 120px;">
                        <small>RECEIPT DATE</small>
                    </th>
                    <th data-column="paid_amount" class="text-end bg-warning text-dark column-paid-amount" style="min-width: 100px;">
                        <small>PAID AMOUNT</small>
                    </th>
                    <th class="text-center column-actions" style="width: 100px;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loads as $load)
                    @php
                        $dealValue = 0;
                        if ($load->carrier_id && isset($deals[$load->carrier_id])) {
                            $dealValue = $deals[$load->carrier_id]->value ?? 0;
                        }
                    @endphp
                    <tr id="load-row-{{ $load->id }}" 
                        class="{{ $load->already_charged ? 'table-warning' : '' }}"
                        data-deal-value="{{ $dealValue }}"
                        data-carrier-id="{{ $load->carrier_id }}">
                        {{-- Checkbox --}}
                        <td class="text-center">
                            <input type="checkbox"
                                   class="load-checkbox"
                                   data-load-id="{{ $load->load_id }}"
                                   {{ $load->already_charged ? '' : 'checked' }}>
                        </td>

                        {{-- Load ID --}}
                        <td class="column-load_id">
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
                        <td class="column-carrier">
                            @if($load->carrier)
                                <strong>{{ $load->carrier->company_name ?? $load->carrier->user->name ?? '-' }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Driver --}}
                        <td class="column-driver">
                            {{ $load->driver ?? '-' }}
                        </td>

                        {{-- Dispatcher --}}
                        <td class="column-dispatcher">
                            @if($load->dispatcher)
                                {{ $load->dispatcher->user->name ?? '-' }}
                            @else
                                <span class="text-muted">{{ $load->dispatcher ?? '-' }}</span>
                            @endif
                        </td>

                        {{-- Price --}}
                        <td class="text-end column-price">
                            @php
                                $price = $load->price ?? 0;
                            @endphp
                            <strong class="{{ $price > 0 ? 'text-success' : 'text-muted' }}" data-price="{{ $price }}">
                                ${{ number_format($price, 2) }}
                            </strong>
                        </td>

                        {{-- Charge Status --}}
                        <td class="text-center column-charge_status">
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

                        {{-- Internal Load ID --}}
                        <td class="column-internal_load_id" style="display: none;">
                            {{ $load->internal_load_id ?? '-' }}
                        </td>

                        {{-- Vehicle --}}
                        <td class="column-year_make_model" style="display: none;">
                            {{ $load->year_make_model ?? '-' }}
                        </td>

                        {{-- VIN --}}
                        <td class="column-vin" style="display: none;">
                            {{ $load->vin ?? '-' }}
                        </td>

                        {{-- Lot Number --}}
                        <td class="column-lot_number" style="display: none;">
                            {{ $load->lot_number ?? '-' }}
                        </td>

                        {{-- Broker Fee --}}
                        <td class="text-end column-broker_fee" style="display: none;">
                            ${{ number_format($load->broker_fee ?? 0, 2) }}
                        </td>

                        {{-- Driver Pay --}}
                        <td class="text-end column-driver_pay" style="display: none;">
                            ${{ number_format($load->driver_pay ?? 0, 2) }}
                        </td>

                        {{-- Payment Status --}}
                        <td class="text-center column-payment_status" style="display: none;">
                            @php
                                $paymentStatus = $load->payment_status ?? 'pending';
                            @endphp
                            @if($paymentStatus === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($paymentStatus === 'partial')
                                <span class="badge bg-warning text-dark">Partial</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>

                        {{-- Invoice Number --}}
                        <td class="text-center column-invoice_number" style="display: none;">
                            {{ $load->invoice_number ?? '-' }}
                        </td>

                        {{-- Pickup Location --}}
                        <td class="column-pickup_name" style="display: none;">
                            {{ $load->pickup_name ?? '-' }}
                        </td>

                        {{-- Delivery Location --}}
                        <td class="column-delivery_name" style="display: none;">
                            {{ $load->delivery_name ?? '-' }}
                        </td>

                        {{-- ⭐ COLUNAS DOS FILTROS DE DATAS --}}

                        {{-- Creation Date --}}
                        <td class="text-center column-creation_date">
                            @if($load->creation_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->creation_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Actual Pickup Date --}}
                        <td class="text-center column-actual_pickup_date">
                            @if($load->actual_pickup_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->actual_pickup_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Actual Delivery Date --}}
                        <td class="text-center column-actual_delivery_date">
                            @if($load->actual_delivery_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->actual_delivery_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Scheduled Pickup Date --}}
                        <td class="text-center column-scheduled_pickup_date">
                            @if($load->scheduled_pickup_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->scheduled_pickup_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Scheduled Delivery Date --}}
                        <td class="text-center column-scheduled_delivery_date">
                            @if($load->scheduled_delivery_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->scheduled_delivery_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Invoice Date --}}
                        <td class="text-center column-invoice_date">
                            @if($load->invoice_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->invoice_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Receipt Date --}}
                        <td class="text-center column-receipt_date">
                            @if($load->receipt_date)
                                <span class="badge bg-light text-dark">
                                    {{ \Carbon\Carbon::parse($load->receipt_date)->format('m/d/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- PAID AMOUNT --}}
                        <td class="text-end column-paid-amount">
                            @php
                                $paidAmount = $load->paid_amount ?? 0;
                            @endphp
                            @if($paidAmount > 0)
                                <strong class="text-info" data-paid-amount="{{ $paidAmount }}">
                                    ${{ number_format($paidAmount, 2) }}
                                </strong>
                            @else
                                <span class="text-muted" data-paid-amount="0">$0.00</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="text-center column-actions">
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
                    {{-- Coluna 1: Checkbox - vazia --}}
                    <th></th>
                    {{-- Coluna 2: LOAD ID - Label TOTAL --}}
                    <th class="text-end column-load_id" id="total-label-cell">
                        <strong>TOTAL:</strong>
                    </th>
                    {{-- Coluna 3: CARRIER - vazia --}}
                    <th class="column-carrier"></th>
                    {{-- Coluna 4: DRIVER - vazia --}}
                    <th class="column-driver"></th>
                    {{-- Coluna 5: DISPATCHER - vazia --}}
                    <th class="column-dispatcher"></th>
                    {{-- Coluna 6: PRICE - Total --}}
                    <th class="text-end column-price">
                        <strong class="text-success" id="table-total-price">
                            ${{ number_format($loads->sum('price') ?? 0, 2) }}
                        </strong>
                    </th>
                    {{-- Coluna 7: CHARGE STATUS - vazia --}}
                    <th class="column-charge_status"></th>
                    {{-- Coluna 8: INTERNAL LOAD ID - vazia e hidden --}}
                    <th class="column-internal_load_id hidden"></th>
                    {{-- Coluna 9: VEHICLE - vazia e hidden --}}
                    <th class="column-year_make_model hidden"></th>
                    {{-- Coluna 10: VIN - vazia e hidden --}}
                    <th class="column-vin hidden"></th>
                    {{-- Coluna 11: LOT NUMBER - vazia e hidden --}}
                    <th class="column-lot_number hidden"></th>
                    {{-- Coluna 12: BROKER FEE - vazia e hidden --}}
                    <th class="column-broker_fee hidden"></th>
                    {{-- Coluna 13: DRIVER PAY - vazia e hidden --}}
                    <th class="column-driver_pay hidden"></th>
                    {{-- Coluna 14: PAYMENT STATUS - vazia e hidden --}}
                    <th class="column-payment_status hidden"></th>
                    {{-- Coluna 15: INVOICE NUMBER - vazia e hidden --}}
                    <th class="column-invoice_number hidden"></th>
                    {{-- Coluna 16: PICKUP LOCATION - vazia e hidden --}}
                    <th class="column-pickup_name hidden"></th>
                    {{-- Coluna 17: DELIVERY LOCATION - vazia e hidden --}}
                    <th class="column-delivery_name hidden"></th>
                    {{-- Coluna 18: CREATION DATE - vazia --}}
                    <th class="column-creation_date"></th>
                    {{-- Coluna 19: ACTUAL PICKUP - vazia --}}
                    <th class="column-actual_pickup_date"></th>
                    {{-- Coluna 20: ACTUAL DELIVERY - vazia --}}
                    <th class="column-actual_delivery_date"></th>
                    {{-- Coluna 21: SCHEDULED PICKUP - vazia --}}
                    <th class="column-scheduled_pickup_date"></th>
                    {{-- Coluna 22: SCHEDULED DELIVERY - vazia --}}
                    <th class="column-scheduled_delivery_date"></th>
                    {{-- Coluna 23: INVOICE DATE - vazia --}}
                    <th class="column-invoice_date"></th>
                    {{-- Coluna 24: RECEIPT DATE - vazia --}}
                    <th class="column-receipt_date"></th>
                    {{-- Coluna 25: PAID AMOUNT - Total --}}
                    <th class="text-end column-paid-amount">
                        <strong class="text-info" id="table-total-paid-amount">
                            ${{ number_format($loads->sum('paid_amount') ?? 0, 2) }}
                        </strong>
                    </th>
                    {{-- Coluna 26: ACTIONS - vazia --}}
                    <th class="column-actions"></th>
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
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Amount Type</label>
                                    <input type="text" class="form-control" readonly value="{{ ucfirst(str_replace('_', ' ', request('amount_type', 'price'))) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Date Range</label>
                                    <input type="text" class="form-control" readonly value="{{ request('date_start') }} to {{ request('date_end') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Carrier Revenue</label>
                                    <input type="number"
                                           name="total_amount"
                                           id="total_amount"
                                           class="form-control fw-bold text-success"
                                           readonly
                                           value="{{ $totalAmount ?? 0 }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-calculator me-1"></i>Dispatcher Revenue
                                        <i class="fas fa-info-circle text-info ms-1" 
                                           data-bs-toggle="tooltip" 
                                           title="Calculated based on Deal percentage for each carrier"></i>
                                    </label>
                                    <input type="number"
                                           id="dispatcher_revenue"
                                           class="form-control fw-bold text-primary"
                                           readonly
                                           value="0.00"
                                           step="0.01">
                                    <small class="text-muted">Based on Deal %</small>
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
                                    <select name="dispatcher_id" class="form-select" required>
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
                                        <input type="text" class="form-control" readonly value="All Carriers">
                                    @else
                                        @php
                                            $selectedCarrier = $carriers->firstWhere('id', request('carrier_id'));
                                        @endphp
                                        <input type="text"
                                            id="carrier-display-field"
                                            class="form-control"
                                            readonly
                                            value="Select a Carrier">
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Due Date <span class="text-danger">*</span></label>
                                    <input type="date"
                                           name="due_date"
                                           id="due_date"
                                           class="form-control"
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
                                    <select name="payment_terms_option" id="payment_terms_option" class="form-select">
                                        <option value="">Select Payment Terms</option>
                                        <option value="today">Today</option>
                                        <option value="2">2 days</option>
                                        <option value="5">5 days</option>
                                        <option value="15">15 days</option>
                                        <option value="30" selected>30 days</option>
                                        <option value="45">45 days</option>
                                        <option value="60">60 days</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                    <div class="form-text">Select payment terms to auto-update due date</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Invoice Notes (Optional)</label>
                                    <textarea name="invoice_notes"
                                              class="form-control"
                                              rows="3"
                                              placeholder="Add any special instructions or notes for this invoice..."></textarea>
                                </div>
                                {{-- Hidden input to store the actual payment_terms value --}}
                                <input type="hidden" name="payment_terms" id="payment_terms" value="net_30">
                            </div>

                            {{-- Custom Payment Terms Date (shown only when Custom is selected) --}}
                            <div class="row mb-3" id="custom-payment-terms-row" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Custom Payment Terms Date <span class="text-danger">*</span></label>
                                    <input type="date"
                                           name="custom_payment_date"
                                           id="custom_payment_date"
                                           class="form-control"
                                           min="{{ date('Y-m-d') }}"
                                           value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                                    <div class="form-text">Select a custom payment terms date</div>
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
        if (typeof showAlertModal === 'function') {
            showAlertModal('No Loads Found', 'No loads found to create invoice. Please apply filters first.', 'warning');
        } else {
            alert('No loads found to create invoice. Please apply filters first.');
        }
        return;
    }

    // Verifica se há alguma linha válida (não mensagens de erro)
    const validRows = Array.from(table.rows).filter(row => {
        const firstCell = row.cells[0];
        return firstCell && !firstCell.textContent.includes('No loads') && !firstCell.textContent.includes('remaining');
    });

    if (validRows.length === 0) {
        if (typeof showAlertModal === 'function') {
            showAlertModal('No Valid Loads', 'No valid loads available to create invoice.', 'warning');
        } else {
            alert('No valid loads available to create invoice.');
        }
        return;
    }

    // ⭐ CORRIGIDO: Captura carrier_id do select correto
    const urlParams = new URLSearchParams(window.location.search);
    const carrierId = urlParams.get('carrier_id') ||
                     document.querySelector('#carrier-select')?.value ||
                     document.querySelector('input[name="carrier_id"]')?.value;

    console.log('Carrier ID encontrado:', carrierId); // Debug

    if (!carrierId || carrierId === '') {
        if (typeof showAlertModal === 'function') {
            showAlertModal('Required Field', 'Please select a Carrier.', 'warning');
        } else {
            alert('Please select a Carrier.');
        }
        document.querySelector('#carrier-select')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        document.querySelector('#carrier-select')?.focus();
        return;
    }

    // Verifica se dispatcher foi selecionado
    const dispatcherId = document.querySelector('select[name="dispatcher_id"]')?.value;
    if (!dispatcherId) {
        if (typeof showAlertModal === 'function') {
            showAlertModal('Required Field', 'Please select a Dispatcher.', 'warning');
        } else {
            alert('Please select a Dispatcher.');
        }
        document.querySelector('select[name="dispatcher_id"]')?.focus();
        return;
    }

    // Verifica se a data de vencimento foi preenchida
    const dueDateCheck = document.querySelector('input[name="due_date"]')?.value;
    if (!dueDateCheck) {
        if (typeof showAlertModal === 'function') {
            showAlertModal('Required Field', 'Please select a Due Date.', 'warning');
        } else {
            alert('Please select a Due Date.');
        }
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
        if (typeof showAlertModal === 'function') {
            showAlertModal('No Loads Selected', 'Please select at least one load to create the invoice.', 'warning');
        } else {
            alert('Please select at least one load to create the invoice.\n\nDEBUG INFO:\n- Checkboxes encontrados: ' + selectedCheckboxes.length + '\n- Verifique o console para mais detalhes');
        }
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
    const paymentTerms = document.getElementById('payment_terms')?.value || 'net_30';
    const invoiceNotes = document.querySelector('textarea[name="invoice_notes"]')?.value || '';
    const dueDate = document.querySelector('input[name="due_date"]')?.value || '';

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
            // Restaurar botão
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }

            // Tratar erro específico de Deal não encontrado
            if (res.status === 422 && data.error && data.redirect_to_deals) {
                let errorMessage = data.error;
                
                // Se houver lista de carriers sem Deal, formatar mensagem
                if (data.carriers_sem_deal && data.carriers_sem_deal.length > 0) {
                    const carrierNames = data.carriers_sem_deal.map(c => c.carrier_name).join(', ');
                    errorMessage = `Cannot create invoice. The following carriers do not have a Deal created:\n${carrierNames}\n\nPlease create a Deal for each carrier before generating the invoice.`;
                }
                
                // Criar modal customizado com botão para redirecionar
                showDealRequiredModal(errorMessage);
            } else if (data.message) {
                if (typeof showAlertModal === 'function') {
                    showAlertModal('Error', data.message || 'Error saving Time Line Charge.', 'error');
                } else {
                    alert(data.message || 'Error saving Time Line Charge.');
                }
            } else if (data.error) {
                if (typeof showAlertModal === 'function') {
                    showAlertModal('Error', data.error || 'Error saving Time Line Charge.', 'error');
                } else {
                    alert(data.error || 'Error saving Time Line Charge.');
                }
            } else {
                if (typeof showAlertModal === 'function') {
                    showAlertModal('Error', 'Error saving Time Line Charge.', 'error');
                } else {
                    alert('Error saving Time Line Charge.');
                }
            }
            throw new Error(data.message || data.error || 'Error saving');
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

        if (typeof showAlertModal === 'function') {
            showAlertModal('Success', message, 'success');
            setTimeout(() => {
                window.location.href = '{{ route('time_line_charges.index') }}';
            }, 1500);
        } else {
            alert(message);
            window.location.href = '{{ route('time_line_charges.index') }}';
        }
    })
    .catch(err => {
        console.error("Error:", err.message);
        // Restaurar botão em caso de erro
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }

        // Reabilita o botão em caso de erro
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});
</script>

<script>
    // Script para gerenciar a exclusão de loads da tabela

// ⭐ Função global para atualizar os totais da tabela
function updateTableTotals() {
        let totalPrice = 0;
        let totalPaidAmount = 0;

        // Calcula total de PRICE - busca em todas as linhas do tbody, mesmo que a coluna esteja oculta
        const allRows = document.querySelectorAll('tbody tr');
        allRows.forEach(function(row) {
            // Verifica se a linha não está oculta
            if (row.style.display === 'none') return;
            
            // Busca a célula de PRICE na linha (mesmo que esteja oculta)
            const priceCell = row.querySelector('.column-price [data-price]');
            if (priceCell) {
                const price = parseFloat(priceCell.getAttribute('data-price')) || 0;
                totalPrice += price;
            }
            
            // Busca a célula de PAID AMOUNT na linha (mesmo que esteja oculta)
            const paidAmountCell = row.querySelector('.column-paid-amount [data-paid-amount]');
            if (paidAmountCell) {
                const paidAmount = parseFloat(paidAmountCell.getAttribute('data-paid-amount')) || 0;
                totalPaidAmount += paidAmount;
            }
        });

        // Atualiza o total de PRICE na tabela
        const totalPriceElement = document.getElementById('table-total-price');
        if (totalPriceElement) {
            totalPriceElement.textContent = '$' + totalPrice.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Atualiza o total de PAID AMOUNT na tabela
        const totalPaidAmountElement = document.getElementById('table-total-paid-amount');
        if (totalPaidAmountElement) {
            totalPaidAmountElement.textContent = '$' + totalPaidAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Atualiza o total no formulário (usa PRICE como padrão)
        const totalAmountInput = document.getElementById('total_amount');
        if (totalAmountInput) {
            totalAmountInput.value = totalPrice.toFixed(2);
        }

        // Atualiza o contador de registros
        const remainingRows = document.querySelectorAll('tbody tr:not([style*="display: none"])').length;
        const headerCount = document.querySelector('.table-responsive h5');
        if (headerCount) {
            headerCount.textContent = `Filtered Loads (${remainingRows} records)`;
        }

        // ⭐ Atualiza o Dispatcher Revenue baseado nos Deals
        updateDispatcherRevenue();

        return { totalPrice, totalPaidAmount, remainingRows };
}

// ⭐ Função global para calcular Dispatcher Revenue baseado em Deals
function updateDispatcherRevenue() {
    let totalDispatcherRevenue = 0;

    // Busca todas as linhas do tbody
    const allRows = document.querySelectorAll('tbody tr');
    allRows.forEach(function(row) {
        // Ignora linhas ocultas
        if (row.style.display === 'none') return;

        // Busca o checkbox para verificar se o load está selecionado
        const checkbox = row.querySelector('.load-checkbox');
        if (!checkbox || !checkbox.checked) return;

        // Pega o valor do price
        const priceCell = row.querySelector('.column-price [data-price]');
        if (!priceCell) return;

        const price = parseFloat(priceCell.getAttribute('data-price')) || 0;

        // Pega o valor do deal (porcentagem) do data attribute da linha
        const dealValue = parseFloat(row.getAttribute('data-deal-value')) || 0;

        // Calcula a comissão do dispatcher: price * (dealValue / 100)
        const dispatcherCommission = price * (dealValue / 100);
        totalDispatcherRevenue += dispatcherCommission;
    });

    // Atualiza o campo de Dispatcher Revenue
    const dispatcherRevenueInput = document.getElementById('dispatcher_revenue');
    if (dispatcherRevenueInput) {
        dispatcherRevenueInput.value = totalDispatcherRevenue.toFixed(2);
    }

    return totalDispatcherRevenue;
}

// Inicialização do gerenciamento de exclusão de loads
document.addEventListener('DOMContentLoaded', function() {
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
                const result = updateTableTotals();

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

    // Função para atualizar os totais baseado nas cargas visíveis (não apenas selecionadas)
    function updateSelectedTotal() {
        // Atualiza os totais de todos os registros visíveis
        updateTableTotals();
        
        // Conta apenas os selecionados para exibição
        let selectedCount = 0;
        loadCheckboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                selectedCount++;
            }
        });

        // Atualiza o contador de registros
        const headerCount = document.querySelector('.table-responsive h5');
        if (headerCount) {
            const totalLoads = loadCheckboxes.length;
            headerCount.textContent = `Filtered Loads (${totalLoads} total, ${selectedCount} selected)`;
        }

        return { selectedCount };
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

    // Inicializar totais na página
    updateTableTotals();
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


  // Script para atualizar automaticamente a data de vencimento baseada nos Payment Terms selecionados
document.addEventListener('DOMContentLoaded', function() {
    const paymentTermsOptionSelect = document.getElementById('payment_terms_option');
    const dueDateInput = document.getElementById('due_date');
    const paymentTermsHidden = document.getElementById('payment_terms');
    const customPaymentTermsRow = document.getElementById('custom-payment-terms-row');
    const customPaymentDateInput = document.getElementById('custom_payment_date');

    if (paymentTermsOptionSelect && dueDateInput) {
        // Função para calcular e atualizar a data de vencimento baseada nos payment terms
        function updateDueDateFromPaymentTerms(selectedOption) {
            const today = new Date();
            let dueDate = new Date(today);
            let paymentTermsValue = 'net_30';

            // Calcula a data baseada na opção selecionada
            switch(selectedOption) {
                case 'today':
                    // Hoje mesmo
                    dueDate = new Date(today);
                    paymentTermsValue = 'due_on_receipt';
                    break;
                case '2':
                    dueDate.setDate(today.getDate() + 2);
                    paymentTermsValue = 'custom';
                    break;
                case '5':
                    dueDate.setDate(today.getDate() + 5);
                    paymentTermsValue = 'custom';
                    break;
                case '15':
                    dueDate.setDate(today.getDate() + 15);
                    paymentTermsValue = 'net_15';
                    break;
                case '30':
                    dueDate.setDate(today.getDate() + 30);
                    paymentTermsValue = 'net_30';
                    break;
                case '45':
                    dueDate.setDate(today.getDate() + 45);
                    paymentTermsValue = 'net_45';
                    break;
                case '60':
                    dueDate.setDate(today.getDate() + 60);
                    paymentTermsValue = 'net_60';
                    break;
                case 'custom':
                    // Mostra o campo de data customizada
                    if (customPaymentTermsRow) {
                        customPaymentTermsRow.style.display = 'block';
                    }
                    if (customPaymentDateInput) {
                        customPaymentDateInput.required = true;
                        // Se já tiver um valor, mantém; senão, usa 30 dias como padrão
                        if (!customPaymentDateInput.value) {
                            const defaultDate = new Date(today);
                            defaultDate.setDate(today.getDate() + 30);
                            customPaymentDateInput.value = defaultDate.toISOString().split('T')[0];
                        }
                        // Atualiza o due_date com o valor custom
                        dueDateInput.value = customPaymentDateInput.value;
                    }
                    paymentTermsValue = 'custom';
                    return; // Não atualiza automaticamente quando é custom
                case '':
                    // Se não selecionou nada, usa 30 dias como padrão
                    dueDate.setDate(today.getDate() + 30);
                    paymentTermsValue = 'net_30';
                    break;
                default:
                    // Fallback - tenta usar como número de dias
                    const days = parseInt(selectedOption);
                    if (!isNaN(days)) {
                        dueDate.setDate(today.getDate() + days);
                        paymentTermsValue = days <= 15 ? 'net_15' : (days <= 30 ? 'net_30' : (days <= 45 ? 'net_45' : 'net_60'));
                    } else {
                        dueDate.setDate(today.getDate() + 30);
                        paymentTermsValue = 'net_30';
                    }
            }

            // Esconde o campo custom se não for necessário
            if (customPaymentTermsRow && selectedOption !== 'custom') {
                customPaymentTermsRow.style.display = 'none';
            }
            if (customPaymentDateInput && selectedOption !== 'custom') {
                customPaymentDateInput.required = false;
            }

            // Formata a data para YYYY-MM-DD
            const formattedDate = dueDate.toISOString().split('T')[0];
            
            // Atualiza o campo hidden de payment_terms
            if (paymentTermsHidden) {
                paymentTermsHidden.value = paymentTermsValue;
            }
            
            // Atualiza o campo de due_date se não for custom
            if (selectedOption !== 'custom') {
                dueDateInput.value = formattedDate;
            }
        }

        // Event listener para mudanças na opção de payment terms
        paymentTermsOptionSelect.addEventListener('change', function() {
            updateDueDateFromPaymentTerms(this.value);
        });

        // Se o campo custom de data for alterado manualmente, atualiza o due_date
        if (customPaymentDateInput) {
            customPaymentDateInput.addEventListener('change', function() {
                if (paymentTermsOptionSelect.value === 'custom') {
                    dueDateInput.value = this.value;
                    if (paymentTermsHidden) {
                        paymentTermsHidden.value = 'custom';
                    }
                }
            });
        }

        // Inicializa com o valor padrão (30 dias)
        if (paymentTermsOptionSelect.value) {
            updateDueDateFromPaymentTerms(paymentTermsOptionSelect.value);
        } else {
            // Se não tiver valor selecionado, define 30 dias como padrão
            paymentTermsOptionSelect.value = '30';
            updateDueDateFromPaymentTerms('30');
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
            if (paymentTermsOptionSelect) {
                paymentTermsOptionSelect.value = '30';
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
    if (paymentTermsOptionSelect) {
        paymentTermsOptionSelect.addEventListener('focus', function() {
            this.style.borderColor = '#0d6efd';
            this.style.boxShadow = '0 0 0 0.2rem rgba(13, 110, 253, 0.25)';
        });

        paymentTermsOptionSelect.addEventListener('blur', function() {
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
          if (typeof showAlertModal === 'function') {
            showAlertModal('Success', response.message, 'success');
          } else {
            alert(response.message);
          }
          $('#additionalService').modal('hide');
          $('#additional-service-form')[0].reset();
        }
      },

      error: function (xhr) {
        if (xhr.status === 422) {
          let errors = xhr.responseJSON.errors;
          let messages = Object.values(errors).map(msgArray => msgArray.join(', ')).join('\n');
          if (typeof showAlertModal === 'function') {
            showAlertModal('Validation Errors', messages, 'error');
          } else {
            alert("Validation errors:\n" + messages);
          }
        } else {
          if (typeof showAlertModal === 'function') {
            showAlertModal('Error', 'Error saving. Please try again.', 'error');
          } else {
            alert("Error saving. Please try again.");
          }
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
        if (typeof showAlertModal === 'function') {
          showAlertModal('Error', 'Error loading additional services.', 'error');
        } else {
          alert("Error loading additional services.");
        }
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
          if (typeof showAlertModal === 'function') {
            showAlertModal('Success', response.message, 'success');
          } else {
            alert(response.message);
          }
          // Reload the services list
          $('#open-additional-service').click();
        }
      },
      error: function(xhr) {
        if (typeof showAlertModal === 'function') {
          showAlertModal('Error', 'Error deleting service. Please try again.', 'error');
        } else {
          alert('Error deleting service. Please try again.');
        }
        console.error(xhr);
      }
    });
  }
}
</script>

<!-- Column Selection Modal -->
<div class="modal fade" id="selectColums" tabindex="-1" aria-labelledby="columnSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="columnSelectionModalLabel">
                    <i class="fas fa-columns me-2"></i>
                    Select Columns to Display
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success" id="selectAllColumns">
                                <i class="fas fa-check-double me-1"></i>
                                Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="deselectAllColumns">
                                <i class="fas fa-times me-1"></i>
                                Deselect All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="resetToDefault">
                                <i class="fas fa-undo me-1"></i>
                                Reset to Default
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Basic Information</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_load_id" data-column="load_id" checked>
                            <label class="form-check-label" for="col_load_id">Load ID</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_internal_load_id" data-column="internal_load_id">
                            <label class="form-check-label" for="col_internal_load_id">Internal Load ID</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_year_make_model" data-column="year_make_model" checked>
                            <label class="form-check-label" for="col_year_make_model">Vehicle</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_vin" data-column="vin">
                            <label class="form-check-label" for="col_vin">VIN</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_lot_number" data-column="lot_number">
                            <label class="form-check-label" for="col_lot_number">Lot Number</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_creation_date" data-column="creation_date">
                            <label class="form-check-label" for="col_creation_date">Creation Date</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Financial Information</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_price" data-column="price" checked>
                            <label class="form-check-label" for="col_price">Price</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_broker_fee" data-column="broker_fee" checked>
                            <label class="form-check-label" for="col_broker_fee">Broker Fee</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_driver_pay" data-column="driver_pay" checked>
                            <label class="form-check-label" for="col_driver_pay">Driver Pay</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_payment_status" data-column="payment_status" checked>
                            <label class="form-check-label" for="col_payment_status">Payment Status</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_invoice_number" data-column="invoice_number">
                            <label class="form-check-label" for="col_invoice_number">Invoice Number</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_invoice_date" data-column="invoice_date">
                            <label class="form-check-label" for="col_invoice_date">Invoice Date</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_receipt_date" data-column="receipt_date">
                            <label class="form-check-label" for="col_receipt_date">Receipt Date</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_paid_amount" data-column="paid_amount" checked>
                            <label class="form-check-label" for="col_paid_amount">Paid Amount</label>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Status</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_charge_status" data-column="charge_status" checked>
                            <label class="form-check-label" for="col_charge_status">Charge Status</label>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">People & Locations</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_carrier" data-column="carrier" checked>
                            <label class="form-check-label" for="col_carrier">Carrier</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_dispatcher" data-column="dispatcher" checked>
                            <label class="form-check-label" for="col_dispatcher">Dispatcher</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_driver" data-column="driver" checked>
                            <label class="form-check-label" for="col_driver">Driver</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_pickup_name" data-column="pickup_name">
                            <label class="form-check-label" for="col_pickup_name">Pickup Location</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_delivery_name" data-column="delivery_name">
                            <label class="form-check-label" for="col_delivery_name">Delivery Location</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Dates</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_scheduled_pickup_date" data-column="scheduled_pickup_date">
                            <label class="form-check-label" for="col_scheduled_pickup_date">Scheduled Pickup</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_actual_pickup_date" data-column="actual_pickup_date">
                            <label class="form-check-label" for="col_actual_pickup_date">Actual Pickup</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_scheduled_delivery_date" data-column="scheduled_delivery_date">
                            <label class="form-check-label" for="col_scheduled_delivery_date">Scheduled Delivery</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_actual_delivery_date" data-column="actual_delivery_date">
                            <label class="form-check-label" for="col_actual_delivery_date">Actual Delivery</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="applyColumnSelection">
                    <i class="fas fa-check me-1"></i>
                    Apply Changes
                </button>
            </div>
        </div>
    </div>
</div>

<style>
  .hidden {
    display: none !important;
  }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const checkboxes = document.querySelectorAll(".toggle-column");
  const toggleAll = document.getElementById("toggle-all-columns");

  function getColumnIndexByName(columnName) {
    // Use more specific selector to target only the loads table
    const table = document.querySelector(".table-responsive table");
    if (!table) return -1;
    const headerCells = table.querySelectorAll("thead th");
    const searchName = columnName.toUpperCase().trim();
    
    // Map of column names to their exact header text (handling variations)
    const columnMapping = {
      'LOAD ID': 'LOAD ID',
      'CARRIER': 'CARRIER',
      'DRIVER': 'DRIVER',
      'DISPATCHER': 'DISPATCHER',
      'PRICE': 'PRICE',
      'CHARGE STATUS': 'CHARGE STATUS',
      'CREATION DATE': 'CREATION DATE',
      'ACTUAL PICKUP': 'ACTUAL PICKUP',
      'ACTUAL DELIVERY': 'ACTUAL DELIVERY',
      'SCHEDULED PICKUP': 'SCHEDULED PICKUP',
      'SCHEDULED DELIVERY': 'SCHEDULED DELIVERY',
      'INVOICE DATE': 'INVOICE DATE',
      'RECEIPT DATE': 'RECEIPT DATE',
      'PAID AMOUNT': 'PAID AMOUNT',
      'ACTIONS': 'ACTIONS'
    };
    
    for (let i = 0; i < headerCells.length; i++) {
      const headerText = headerCells[i].textContent.trim().toUpperCase();
      // Remove HTML tags and extra whitespace, normalize spaces
      const cleanText = headerText.replace(/\s+/g, ' ').trim();
      
      // Check for exact match
      if (cleanText === searchName) {
        return i;
      }
      
      // Check if header contains the search name
      if (cleanText.includes(searchName)) {
        return i;
      }
      
      // Check reverse - if search name contains key words from header
      const searchWords = searchName.split(' ').filter(w => w.length > 2);
      if (searchWords.length > 0 && searchWords.every(word => cleanText.includes(word))) {
        return i;
      }
    }
    return -1;
  }


  function toggleColumnByName(columnName, show) {
    const colIndex = getColumnIndexByName(columnName);
    if (colIndex === -1) {
      console.warn(`Column "${columnName}" not found`);
      return;
    }
    // Use more specific selector to target only the loads table
    const table = document.querySelector(".table-responsive table");
    if (!table) return;
    const rows = table.querySelectorAll("tr");
    rows.forEach(row => {
      const cell = row.cells[colIndex];
      if (cell) {
        cell.classList.toggle("hidden", !show);
      }
    });
    
    // Se PRICE ou PAID AMOUNT foram mostradas novamente, recalcula os totais primeiro
    if (show && (columnName.toUpperCase() === 'PRICE' || columnName.toUpperCase() === 'PAID AMOUNT')) {
      // Aguarda um pouco para garantir que o DOM foi atualizado e as colunas estão visíveis
      setTimeout(function() {
        // Atualiza os totais
        if (typeof updateTableTotals === 'function') {
          updateTableTotals();
        }
      }, 200);
    } else {
      // Atualiza os totais normalmente
      if (typeof updateTableTotals === 'function') {
        updateTableTotals();
      }
    }
  }

  function toggleActionsColumn() {
    const table = document.querySelector(".table-responsive table");
    if (!table) return;
    const actionsColIndex = Array.from(table.querySelectorAll("thead th"))
      .findIndex(th => th.textContent.trim().toUpperCase().includes("ACTIONS"));
    if (actionsColIndex === -1) return;

    const showActions = Array.from(checkboxes).some(cb => {
      return cb.dataset.column.toUpperCase() === "ACTIONS" && cb.checked;
    });

    const rows = table.querySelectorAll("tr");
    rows.forEach(row => {
      const cell = row.cells[actionsColIndex];
      if (cell) {
        cell.classList.toggle("hidden", !showActions);
      }
    });
  }

  checkboxes.forEach(checkbox => {
    checkbox.addEventListener("change", () => {
      const columnName = checkbox.dataset.column;
      toggleColumnByName(columnName, checkbox.checked);

      const allChecked = Array.from(checkboxes).every(cb => cb.checked);
      if (toggleAll) {
        toggleAll.checked = allChecked;
      }

      toggleActionsColumn();
    });
  });

  if (toggleAll) {
    toggleAll.addEventListener("change", () => {
      const show = toggleAll.checked;
      checkboxes.forEach(cb => {
        cb.checked = show;
        const columnName = cb.dataset.column;
        toggleColumnByName(columnName, show);
      });

      toggleActionsColumn();
      
      // Se mostrando todas as colunas e PRICE/PAID AMOUNT estão incluídas, recalcula totais
      if (show) {
        setTimeout(function() {
          if (typeof updateTableTotals === 'function') {
            updateTableTotals();
          }
        }, 100);
      }
    });
  }

  // Executar ao carregar para garantir consistência inicial
  toggleActionsColumn();
  
  // Atualiza os totais ao carregar
  if (typeof updateTableTotals === 'function') {
    updateTableTotals();
  }
});

// Função para inicializar o modal de seleção de colunas
function initColumnSelector() {
    const modal = document.getElementById('selectColums');
    const selectAllBtn = document.getElementById('selectAllColumns');
    const deselectAllBtn = document.getElementById('deselectAllColumns');
    const resetDefaultBtn = document.getElementById('resetToDefault');
    const applyBtn = document.getElementById('applyColumnSelection');
    
    if (!modal) return;
    
    // Colunas padrão (visíveis inicialmente)
    const defaultColumns = [
        'load_id', 'carrier', 'driver', 'dispatcher', 'price', 'charge_status',
        'creation_date', 'actual_pickup_date', 'actual_delivery_date',
        'scheduled_pickup_date', 'scheduled_delivery_date', 'invoice_date',
        'receipt_date', 'paid_amount', 'broker_fee', 'driver_pay', 'payment_status'
    ];
    
    // Selecionar todas as colunas
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            const checkboxes = modal.querySelectorAll('input[type="checkbox"].column-toggle');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        });
    }
    
    // Desselecionar todas as colunas
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            const checkboxes = modal.querySelectorAll('input[type="checkbox"].column-toggle');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    }
    
    // Redefinir para padrão
    if (resetDefaultBtn) {
        resetDefaultBtn.addEventListener('click', function() {
            const checkboxes = modal.querySelectorAll('input[type="checkbox"].column-toggle');
            checkboxes.forEach(checkbox => {
                const columnName = checkbox.getAttribute('data-column');
                checkbox.checked = defaultColumns.includes(columnName);
            });
        });
    }
    
    // Aplicar seleção quando clicar em "Apply Changes"
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            const checkboxes = modal.querySelectorAll('input[type="checkbox"].column-toggle');
            const table = document.querySelector(".table-responsive table");
            
            if (!table) {
                console.error('Table not found');
                return;
            }
            
            checkboxes.forEach(checkbox => {
                const columnName = checkbox.getAttribute('data-column');
                const isChecked = checkbox.checked;
                
                if (!columnName) return;
                
                // Mostrar/ocultar cabeçalhos do thead (th) usando classes
                const headers = table.querySelectorAll(`thead th[data-column="${columnName}"], thead th.column-${columnName}`);
                headers.forEach(header => {
                    if (isChecked) {
                        header.classList.remove('hidden');
                        header.style.display = ''; // Remove inline style se existir
                    } else {
                        header.classList.add('hidden');
                    }
                });
                
                // Mostrar/ocultar células do tbody (td) usando classes
                const cells = table.querySelectorAll(`td.column-${columnName}, td[data-column="${columnName}"]`);
                cells.forEach(cell => {
                    if (isChecked) {
                        cell.classList.remove('hidden');
                        cell.style.display = ''; // Remove inline style se existir
                    } else {
                        cell.classList.add('hidden');
                    }
                });
                
                // Mostrar/ocultar células do tfoot (th) usando classes
                const footerCells = table.querySelectorAll(`tfoot th.column-${columnName}`);
                footerCells.forEach(footerCell => {
                    if (isChecked) {
                        footerCell.classList.remove('hidden');
                        footerCell.style.display = ''; // Remove inline style se existir
                    } else {
                        footerCell.classList.add('hidden');
                    }
                });
            });
            
            // Recalcular totais e reconstruir tfoot
            if (typeof updateTableTotals === 'function') {
                updateTableTotals();
            }
            
            // Fechar modal
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
        });
    }
    
    // Inicializar checkboxes do modal quando ele for aberto
    if (modal) {
        modal.addEventListener('show.bs.modal', function () {
            const checkboxes = modal.querySelectorAll('input[type="checkbox"].column-toggle');
            const table = document.querySelector(".table-responsive table");
            
            if (table) {
                checkboxes.forEach(checkbox => {
                    const columnName = checkbox.getAttribute('data-column');
                    if (columnName) {
                        const header = table.querySelector(`thead th[data-column="${columnName}"], thead th.column-${columnName}`);
                        // Verifica se a coluna está visível (não tem classe hidden e não tem display: none)
                        checkbox.checked = header && !header.classList.contains('hidden') && header.style.display !== 'none';
                    }
                });
            }
        });
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    initColumnSelector();
    
    // Pesquisa dinâmica de colunas
    const searchColumnsInput = document.getElementById('searchColumnsInput');
    if (searchColumnsInput) {
      searchColumnsInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const checkboxes = document.querySelectorAll('#selectColums .toggle-column');

        checkboxes.forEach(function (checkbox) {
          const label = checkbox.closest('label');
          const container = checkbox.closest('.col-md-6');

          if (label && container) {
            if (label.textContent.toLowerCase().includes(searchTerm)) {
              container.style.display = 'block';
            } else {
              container.style.display = 'none';
            }
          }
        });
      });
    }
});

// Function to show Deal Required modal with redirect button
function showDealRequiredModal(message) {
    // Criar modal dinamicamente se não existir
    let modalElement = document.getElementById('dealRequiredModal');
    if (!modalElement) {
        modalElement = document.createElement('div');
        modalElement.id = 'dealRequiredModal';
        modalElement.className = 'modal fade';
        modalElement.setAttribute('tabindex', '-1');
        modalElement.setAttribute('aria-labelledby', 'dealRequiredModalLabel');
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="dealRequiredModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Deal Required
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="dealRequiredModalMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </button>
                        <a href="{{ route('deals.index') }}" class="btn btn-primary" id="goToDealsBtn">
                            <i class="fas fa-handshake me-1"></i>
                            Go to Deals
                        </a>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modalElement);
    }
    
    // Atualizar mensagem
    const messageElement = document.getElementById('dealRequiredModalMessage');
    if (messageElement) {
        // Converter quebras de linha em <br> ou parágrafos
        messageElement.innerHTML = message.replace(/\n/g, '<br>');
    }
    
    // Mostrar modal
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}
</script>

@endsection
