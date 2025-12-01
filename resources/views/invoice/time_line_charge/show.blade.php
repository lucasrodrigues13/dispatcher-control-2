@extends("layouts.app2")

@section('conteudo')
<style>
    * {
        font-size: 14px !important;
    }

    .invoice-header {
        background: linear-gradient(135deg, #013d81 0%, #0056b3 100%);
        color: white;
        padding: 20px;
        border-radius: 10px 10px 0 0;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .invoice-info-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .due-date-badge {
        font-size: 16px !important;
        padding: 8px 15px;
        border-radius: 25px;
    }

    .company-info {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .custom-table th {
        background: #013d81;
        color: white;
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        font-size: 13px !important;
    }

    .custom-table td {
        padding: 12px;
        border-bottom: 1px solid #f8f9fa;
        vertical-align: middle;
    }

    .custom-table tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }

    .custom-table tbody tr:nth-child(even) {
        background-color: #fbfcfd;
    }

    .total-section {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-top: 20px;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px !important;
        font-weight: 600;
    }

    .btn-modern {
        border-radius: 25px;
        padding: 8px 20px;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .invoice-actions {
        position: sticky;
        bottom: 20px;
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        border-top: 1px solid #e9ecef;
    }

    .payment-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    /* ⭐ MELHORADO: CSS para impressão */
    @media print {
        @page {
            size: A4;
            margin: 0.5in;
        }

        /* Ocultar tudo que não deve ser impresso */
        .no-print,
        .navbar,
        .sidebar,
        .main-sidebar,
        .control-sidebar,
        .main-header,
        .main-footer,
        .invoice-actions,
        .btn,
        button,
        .modal,
        .toast,
        .alert-dismissible .btn-close,
        nav,
        .breadcrumb,
        .pagination,
        .dropdown,
        .navbar-nav,
        .sidebar-wrapper,
        #sidebar,
        .content-wrapper > .navbar,
        .wrapper > .navbar,
        .wrapper > .main-sidebar,
        .wrapper > .control-sidebar,
        .wrapper > .main-footer {
            display: none !important;
            visibility: hidden !important;
        }

        /* Garantir que apenas o conteúdo da invoice seja visível */
        body {
            background: white !important;
            color: black !important;
            font-family: Arial, sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .content-wrapper,
        .content,
        main,
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: none !important;
        }

        /* Estilizar especificamente para impressão */
        .invoice-content {
            width: 100% !important;
            margin: 0 !important;
            padding: 20px !important;
            background: white !important;
        }

        .invoice-header {
            background: #013d81 !important;
            color: white !important;
            padding: 20px !important;
            margin-bottom: 20px !important;
            border-radius: 0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .custom-table {
            page-break-inside: avoid;
            border: 1px solid #000 !important;
        }

        .custom-table th {
            background: #013d81 !important;
            color: white !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .total-section {
            background: #28a745 !important;
            color: white !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Quebra de página */
        .page-break {
            page-break-before: always;
        }

        /* Evitar quebra dentro de elementos importantes */
        .invoice-info-card,
        .company-info {
            page-break-inside: avoid;
        }
    }

    /* ⭐ NOVO: Estilo específico para o conteúdo da invoice */
    .invoice-content {
        background: white;
        min-height: 100vh;
    }

    /* ⭐ NOVO: Estilo para PDF */
    .pdf-content {
        background: white !important;
        padding: 20px;
        font-family: Arial, sans-serif;
    }

    .pdf-content .invoice-header {
        background: #013d81 !important;
        color: white !important;
    }

    .pdf-content .custom-table th {
        background: #013d81 !important;
        color: white !important;
    }

    .pdf-content .total-section {
        background: #28a745 !important;
        color: white !important;
    }

    /* ⭐ NOVO: Loading overlay para PDF */
    .pdf-loading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        color: white;
        font-size: 18px;
    }

    .pdf-loading .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin-right: 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<!-- ⭐ NOVO: Wrapper principal para o conteúdo da invoice -->
<div class="invoice-content" id="invoice-content">
    <div class="container-fluid px-4 py-3">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2" style="font-size: 28px !important;">
                        <i class="fas fa-file-invoice me-2"></i>
                        Invoice #{{ $charge->invoice_id }}
                    </h1>
                    <p class="mb-0" style="font-size: 16px !important; opacity: 0.9;">
                        Generated on {{ $charge->created_at->format('m/d/Y') }}
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    @php
                        $dueDate = $charge->due_date ? \Carbon\Carbon::parse($charge->due_date) : null;
                        $isOverdue = $dueDate && $dueDate->isPast() && $charge->status_payment !== 'paid';
                        $daysDiff = $dueDate ? now()->diffInDays($dueDate, false) : null;
                    @endphp

                    @if($dueDate)
                        <div class="due-date-badge badge {{ $isOverdue ? 'bg-danger' : ($daysDiff <= 7 ? 'bg-warning text-dark' : 'bg-success') }}">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Due: {{ $dueDate->format('m/d/Y') }}
                            @if($isOverdue)
                                ({{ abs($daysDiff) }} days overdue)
                            @elseif($daysDiff <= 7)
                                ({{ $daysDiff }} days remaining)
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Information Cards -->
        <div class="row mt-4">
            <!-- Bill To Section -->
            <div class="col-md-6">
                <div class="invoice-info-card">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-building me-2"></i>
                        BILL TO
                    </h5>
                    @if($charge->carrier)
                        <h6 class="fw-bold">{{ $charge->carrier->company_name ?? 'N/A' }}</h6>
                        <div class="text-muted">
                            <p class="mb-1">
                                <i class="fas fa-phone me-2"></i>
                                {{ $charge->carrier->phone ?? 'Not provided' }}
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-envelope me-2"></i>
                                {{ $charge->carrier->email ?? 'Not provided' }}
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                {{ $charge->carrier->address ?? 'Address not provided' }}
                            </p>
                            @if($charge->carrier->dot_number)
                                <p class="mb-1">
                                    <i class="fas fa-id-card me-2"></i>
                                    DOT: {{ $charge->carrier->dot_number }}
                                </p>
                            @endif
                        </div>
                    @else
                        <p class="text-muted">Carrier information not available</p>
                    @endif

                    <div class="mt-3 pt-3 border-top">
                        <p class="mb-1">
                            <strong>Period:</strong>
                            {{ \Carbon\Carbon::parse($charge->date_start)->format('m/d/Y') }} -
                            {{ \Carbon\Carbon::parse($charge->date_end)->format('m/d/Y') }}
                        </p>
                        @if($charge->payment_terms)
                            <p class="mb-1">
                                <strong>Payment Terms:</strong>
                                {{ $charge->payment_terms_formatted }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Company Info Section -->
            <div class="col-md-6">
                <div class="company-info">
                    <h5 class="text-success mb-3">
                        <i class="fas fa-truck me-2"></i>
                        ABBR TRANSPORT AND SHIPPING LLC
                    </h5>
                    <div class="text-muted">
                        <p class="mb-1">
                            <strong>EIN:</strong> 37-2169976
                        </p>
                        <p class="mb-1">
                            <strong>Phone:</strong> (302) 219-3120
                        </p>
                        <p class="mb-1">
                            <strong>Address:</strong> 412 West 7th Street, STE 912<br>
                            Clovis, NM 88101
                        </p>
                    </div>

                    <!-- Invoice Status & Amount -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><strong>Status:</strong></span>
                            <span class="status-badge {{ $charge->status_payment === 'paid' ? 'bg-success' : ($isOverdue ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ ucfirst($charge->status_payment ?? 'pending') }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>Total Amount:</strong></span>
                            <span class="h5 mb-0 text-success">${{ number_format($totalAmount, 2) }}</span>
                        </div>
                        @if($dealAmount > 0)
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span><strong>Commission ({{ $dealPercent }}%):</strong></span>
                                <span class="h6 mb-0 text-info">${{ number_format($dealAmount, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Controls -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Load Details ({{ $loads->count() }} items)
                        @if(isset($historicalLoads) && $historicalLoads->count() > 0)
                            <small class="text-warning ms-2">
                                <i class="fas fa-history"></i>
                                ({{ $historicalLoads->count() }} historical)
                            </small>
                        @endif
                    </h5>
                    {{-- <button class="btn btn-outline-primary btn-modern no-print" data-bs-toggle="modal" data-bs-target="#selectColums">
                        <i class="fas fa-columns me-2"></i>
                        Customize Columns
                    </button> --}}
                </div>
            </div>
        </div>

        <!-- ⭐ MELHORADO: Aviso para loads históricos -->
        @if(isset($historicalLoads) && $historicalLoads->count() > 0)
            <div class="alert alert-warning no-print mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Notice:</strong> This invoice contains {{ $historicalLoads->count() }} load(s) that have been deleted from the system but are preserved in the historical record.
            </div>
        @endif

        <!-- Column Selection Button -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>
                Load Details
            </h5>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#columnSelectionModal">
                <i class="fas fa-columns me-1"></i>
                Select Columns
            </button>
        </div>

        <!-- Loads Table -->
        <div class="table-responsive">
            <table class="custom-table" id="loadsTable">
                <thead>
                    <tr>
                        <th data-column="load_id" class="column-load_id">
                            <i class="fas fa-hashtag me-1"></i>
                            Load ID
                        </th>
                        <th data-column="internal_load_id" class="column-internal_load_id" style="display: none;">
                            <i class="fas fa-hashtag me-1"></i>
                            Internal Load ID
                        </th>
                        <th data-column="year_make_model" class="column-year_make_model">
                            <i class="fas fa-car me-1"></i>
                            Vehicle
                        </th>
                        <th data-column="price" class="column-price">
                            <i class="fas fa-dollar-sign me-1"></i>
                            Price
                        </th>
                        <th data-column="dispatcher" class="column-dispatcher">
                            <i class="fas fa-user-tie me-1"></i>
                            Dispatcher
                        </th>
                        <th data-column="driver" class="column-driver">
                            <i class="fas fa-user me-1"></i>
                            Driver
                        </th>
                        <th data-column="broker_fee" class="column-broker_fee">
                            <i class="fas fa-percent me-1"></i>
                            Broker Fee
                        </th>
                        <th data-column="driver_pay" class="column-driver_pay">
                            <i class="fas fa-money-bill me-1"></i>
                            Driver Pay
                        </th>
                        <th data-column="payment_status" class="column-payment_status">
                            <i class="fas fa-check-circle me-1"></i>
                            Payment Status
                        </th>
                        <th data-column="creation_date" class="column-creation_date" style="display: none;">
                            <i class="fas fa-calendar me-1"></i>
                            Creation Date
                        </th>
                        <th data-column="scheduled_pickup_date" class="column-scheduled_pickup_date" style="display: none;">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Scheduled Pickup
                        </th>
                        <th data-column="actual_pickup_date" class="column-actual_pickup_date" style="display: none;">
                            <i class="fas fa-calendar-check me-1"></i>
                            Actual Pickup
                        </th>
                        <th data-column="scheduled_delivery_date" class="column-scheduled_delivery_date" style="display: none;">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Scheduled Delivery
                        </th>
                        <th data-column="actual_delivery_date" class="column-actual_delivery_date" style="display: none;">
                            <i class="fas fa-calendar-check me-1"></i>
                            Actual Delivery
                        </th>
                        <th data-column="vin" class="column-vin" style="display: none;">
                            <i class="fas fa-barcode me-1"></i>
                            VIN
                        </th>
                        <th data-column="lot_number" class="column-lot_number" style="display: none;">
                            <i class="fas fa-tag me-1"></i>
                            Lot Number
                        </th>
                        <th data-column="pickup_name" class="column-pickup_name" style="display: none;">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            Pickup Location
                        </th>
                        <th data-column="delivery_name" class="column-delivery_name" style="display: none;">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            Delivery Location
                        </th>
                        <th data-column="invoice_number" class="column-invoice_number" style="display: none;">
                            <i class="fas fa-file-invoice me-1"></i>
                            Invoice Number
                        </th>
                        <th data-column="invoice_date" class="column-invoice_date" style="display: none;">
                            <i class="fas fa-calendar me-1"></i>
                            Invoice Date
                        </th>
                        <th data-column="receipt_date" class="column-receipt_date" style="display: none;">
                            <i class="fas fa-receipt me-1"></i>
                            Receipt Date
                        </th>
                        <th class="text-center no-print">
                            <i class="fas fa-cog"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loads as $item)
                        <tr id="row-view-{{ $item->id }}" class="{{ ($item->is_historical ?? false) ? 'table-warning' : '' }}">
                            <td class="column-load_id">
                                <strong class="text-primary">{{ $item->load_id }}</strong>
                                @if($item->is_historical ?? false)
                                    <span class="badge badge-warning ms-1 no-print" title="Historical record">
                                        <i class="fas fa-history"></i>
                                    </span>
                                @endif
                            </td>
                            <td class="column-internal_load_id" style="display: none;">{{ $item->internal_load_id ?? '-' }}</td>
                            <td class="column-year_make_model">{{ $item->year_make_model ?? '-' }}</td>
                            <td class="column-price">
                                <strong class="text-success">${{ number_format($item->price ?? 0, 2) }}</strong>
                            </td>
                            <td class="column-dispatcher">{{ $item->dispatcher ?? '-' }}</td>
                            <td class="column-driver">{{ $item->driver ?? '-' }}</td>
                            <td class="column-broker_fee text-end">${{ number_format($item->broker_fee ?? 0, 2) }}</td>
                            <td class="column-driver_pay text-end">${{ number_format($item->driver_pay ?? 0, 2) }}</td>
                            <td class="column-payment_status">
                                @php
                                    $statusClass = match($item->payment_status) {
                                        'paid' => 'bg-success',
                                        'pending' => 'bg-warning text-dark',
                                        'failed' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst($item->payment_status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="column-creation_date" style="display: none;">{{ $item->creation_date ? $item->creation_date->format('m/d/Y') : '-' }}</td>
                            <td class="column-scheduled_pickup_date" style="display: none;">{{ $item->scheduled_pickup_date ? $item->scheduled_pickup_date->format('m/d/Y') : '-' }}</td>
                            <td class="column-actual_pickup_date" style="display: none;">{{ $item->actual_pickup_date ? $item->actual_pickup_date->format('m/d/Y') : '-' }}</td>
                            <td class="column-scheduled_delivery_date" style="display: none;">{{ $item->scheduled_delivery_date ? $item->scheduled_delivery_date->format('m/d/Y') : '-' }}</td>
                            <td class="column-actual_delivery_date" style="display: none;">{{ $item->actual_delivery_date ? $item->actual_delivery_date->format('m/d/Y') : '-' }}</td>
                            <td class="column-vin" style="display: none;">{{ $item->vin ?? '-' }}</td>
                            <td class="column-lot_number" style="display: none;">{{ $item->lot_number ?? '-' }}</td>
                            <td class="column-pickup_name" style="display: none;">{{ $item->pickup_name ?? '-' }}</td>
                            <td class="column-delivery_name" style="display: none;">{{ $item->delivery_name ?? '-' }}</td>
                            <td class="column-invoice_number" style="display: none;">{{ $item->invoice_number ?? '-' }}</td>
                            <td class="column-invoice_date" style="display: none;">{{ $item->invoice_date ? $item->invoice_date->format('m/d/Y') : '-' }}</td>
                            <td class="column-receipt_date" style="display: none;">{{ $item->receipt_date ? $item->receipt_date->format('m/d/Y') : '-' }}</td>
                            <td class="text-center no-print">
                                @if(!($item->is_historical ?? false))
                                    <form action="{{ route('time_line_charges.load_invoice.destroy', [$item->load_id, $charge->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure you want to remove this load from the invoice?');"
                                          style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger btn-modern"
                                                title="Remove Load">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted" title="Historical record cannot be removed">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total Section -->
        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="total-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-size: 18px !important;">
                            <i class="fas fa-calculator me-2"></i>
                            Total Amount:
                        </span>
                        <span style="font-size: 24px !important; font-weight: bold;" id="total-price">
                            ${{ number_format($totalAmount, 2) }}
                        </span>
                    </div>
                    @if($dealAmount > 0)
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-light">
                            <span style="font-size: 14px !important; opacity: 0.9;">
                                Commission ({{ $dealPercent }}%):
                            </span>
                            <span style="font-size: 16px !important;">
                                ${{ number_format($dealAmount, 2) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="invoice-actions no-print">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Last updated: {{ $charge->updated_at->diffForHumans() }}
                </span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-modern" onclick="gerarPDF()">
                    <i class="fas fa-file-pdf me-2"></i>
                    Download PDF
                </button>
                <button class="btn btn-info btn-modern" onclick="exportarExcel()">
                    <i class="fas fa-file-excel me-2"></i>
                    Export Excel
                </button>
                <button class="btn btn-primary btn-modern" onclick="imprimirInvoice()">
                    <i class="fas fa-print me-2"></i>
                    Print
                </button>
                <a href="{{ route('time_line_charges.index') }}" class="btn btn-secondary btn-modern">
                    <i class="fas fa-arrow-left me-2"></i>
                    Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Column Selection -->
<div class="modal fade" id="selectColums" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-columns me-2"></i>
                    Customize Columns
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="searchColumnsInput" class="form-control" placeholder="Search columns...">
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" id="toggle-all-columns" class="form-check-input">
                    <label class="form-check-label" for="toggle-all-columns">
                        Show/Hide All Columns
                    </label>
                </div>
                <hr>
                <div class="row" style="max-height: 300px; overflow-y: auto;">
                    <!-- Column checkboxes here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ⭐ NOVO: Loading overlay para PDF -->
<div id="pdf-loading" class="pdf-loading" style="display: none;">
    <div class="spinner"></div>
    <span>Generating PDF...</span>
</div>
</div>

<!-- Column Selection Modal -->
<div class="modal fade" id="columnSelectionModal" tabindex="-1" aria-labelledby="columnSelectionModalLabel" aria-hidden="true">
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
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">People & Locations</h6>
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

<!-- Modal de Aviso para PDF com muitas colunas -->
<div class="modal fade" id="pdfWarningModal" tabindex="-1" aria-labelledby="pdfWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="pdfWarningModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Too Many Columns Selected
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    You selected <strong id="modal-column-count">0</strong> columns.
                </p>
                <p>
                    For better visualization, we recommend exporting to <strong>Excel</strong> when there are more than 14 columns.
                </p>
                <p class="mb-0">
                    Do you want to continue with PDF anyway?
                </p>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>The PDF will be generated with reduced font size to accommodate all columns.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirm-pdf-generation">
                    <i class="fas fa-file-pdf me-1"></i>
                    Continue with PDF
                </button>
                <button type="button" class="btn btn-success" id="switch-to-excel">
                    <i class="fas fa-file-excel me-1"></i>
                    Export Excel
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
// ⭐ NOVA: Função para gerar PDF com configurações dinâmicas baseadas no número de colunas
// Definida antes de gerarPDF() para garantir que esteja disponível
function generatePDFWithSettings(elementoClone, fileName, visibleColumnsCount) {
    // Adicionar classe PDF ao clone
    elementoClone.classList.add('pdf-content');
    
    // ⭐ REDUZIR TAMANHO DAS COLUNAS E FONTE QUANDO > 14 COLUNAS
    if (visibleColumnsCount > 14) {
        // Reduzir tamanho da fonte na tabela
        const table = elementoClone.querySelector('#loadsTable');
        if (table) {
            table.style.fontSize = '9px';
            const headers = table.querySelectorAll('th');
            headers.forEach(header => {
                header.style.fontSize = '9px';
                header.style.padding = '4px 6px';
            });
            const cells = table.querySelectorAll('td');
            cells.forEach(cell => {
                cell.style.fontSize = '9px';
                cell.style.padding = '4px 6px';
            });
        }
        
        // Reduzir largura das colunas
        elementoClone.style.fontSize = '9px';
    }

    // ⭐ LÓGICA DINÂMICA: Determinar formato do papel baseado no número de colunas
    let paperFormat = 'a4'; // padrão
    let scale = 1.5; // escala padrão
    let margin = [0.5, 0.5, 0.5, 0.5]; // margens padrão
    
    if (visibleColumnsCount <= 10) {
        paperFormat = 'a4'; // A4 landscape
        scale = 1.5;
        margin = [0.5, 0.5, 0.5, 0.5];
    } else if (visibleColumnsCount <= 14) {
        paperFormat = 'a3'; // A3 landscape
        scale = 1.5;
        margin = [0.4, 0.4, 0.4, 0.4]; // Margens reduzidas para A3
    } else {
        // Mais de 14 colunas: usar A3 com escala e margens reduzidas
        paperFormat = 'a3';
        scale = 1.2; // ⭐ Reduzir escala para caber mais colunas
        margin = [0.3, 0.3, 0.3, 0.3]; // ⭐ Margens ainda menores
    }

    // Configurações otimizadas para PDF
    const options = {
        margin: margin,
        filename: fileName,
        image: {
            type: 'jpeg',
            quality: 0.95
        },
        html2canvas: {
            scale: scale, // ⭐ Escala dinâmica baseada no número de colunas
            useCORS: true,
            allowTaint: true,
            scrollX: 0,
            scrollY: 0,
            backgroundColor: '#ffffff',
            removeContainer: true,
            foreignObjectRendering: false
        },
        jsPDF: {
            unit: 'in',
            format: paperFormat, // ⭐ Formato dinâmico baseado no número de colunas
            orientation: 'landscape',
            compress: true
        },
        pagebreak: {
            mode: ['avoid-all', 'css', 'legacy']
        }
    };

    // Gerar PDF
    html2pdf()
        .set(options)
        .from(elementoClone)
        .save()
        .then(() => {
            console.log('PDF generated successfully');
        })
        .catch((error) => {
            console.error('Error generating PDF:', error);
            if (typeof showAlertModal === 'function') {
                showAlertModal('Error', 'Error generating PDF. Please try again.', 'error');
            } else {
                alert('Error generating PDF. Please try again.');
            }
        })
        .finally(() => {
            // Esconder loading
            document.getElementById('pdf-loading').style.display = 'none';
        });
}

// ⭐ MELHORADO: Função para gerar PDF
function gerarPDF() {
    // Mostrar loading
    document.getElementById('pdf-loading').style.display = 'flex';

    // Aguardar um pouco para o loading aparecer
    setTimeout(() => {
        try {
            // Elemento a ser convertido
            const elemento = document.getElementById('invoice-content');

            if (!elemento) {
                if (typeof showAlertModal === 'function') {
                    showAlertModal('Error', 'Invoice content not found', 'error');
                } else {
                    alert('Error: Invoice content not found');
                }
                document.getElementById('pdf-loading').style.display = 'none';
                return;
            }

            // ⭐ PERSONALIZAÇÃO DO NOME DO ARQUIVO
            const invoiceId = "{{ $charge->invoice_id ?? 'unknown' }}";
            const invoiceDate = "{{ $charge->created_at->format('m-d-Y') }}";
            const carrierName = "{{ $charge->carrier->user->name ?? 'Unknown_Carrier' }}";
            
            // Limpar caracteres especiais do nome do carrier para uso em nome de arquivo
            const cleanCarrierName = carrierName.replace(/[^a-zA-Z0-9_-]/g, '_');
            
            // Formato: Invoice_[ID]_[Date]_[CarrierName].pdf
            const fileName = `Invoice_${invoiceId}_${invoiceDate}_${cleanCarrierName}.pdf`;

            // Clonar o elemento para manipular sem afetar a página
            const elementoClone = elemento.cloneNode(true);

            // Remover elementos que não devem aparecer no PDF
            const elementosRemover = elementoClone.querySelectorAll('.no-print, .btn, button, .modal, .dropdown, .navbar, .sidebar');
            elementosRemover.forEach(el => el.remove());

            // ⭐ NOVO: Ocultar colunas que estão ocultas na tela (respeitar seleção de colunas)
            const table = elementoClone.querySelector('#loadsTable');
            let visibleColumnsCount = 0;
            
            if (table) {
                // Verificar quais colunas estão visíveis na tabela original
                const originalTable = document.getElementById('loadsTable');
                if (originalTable) {
                    const allColumns = originalTable.querySelectorAll('th[data-column]');
                    allColumns.forEach(originalTh => {
                        const columnName = originalTh.getAttribute('data-column');
                        const computedStyle = window.getComputedStyle(originalTh);
                        const isVisible = computedStyle.display !== 'none' && 
                                         computedStyle.visibility !== 'hidden' &&
                                         originalTh.style.display !== 'none';
                        
                        // Contar colunas visíveis
                        if (isVisible) {
                            visibleColumnsCount++;
                        }
                        
                        // Ocultar colunas no clone se estiverem ocultas no original
                        if (!isVisible) {
                            // Ocultar cabeçalhos
                            const clonedHeaders = table.querySelectorAll(`th[data-column="${columnName}"], th.column-${columnName}`);
                            clonedHeaders.forEach(header => {
                                header.style.display = 'none';
                                header.style.visibility = 'hidden';
                            });
                            
                            // Ocultar células do corpo da tabela
                            const clonedCells = table.querySelectorAll(`td.column-${columnName}, td[data-column="${columnName}"]`);
                            clonedCells.forEach(cell => {
                                cell.style.display = 'none';
                                cell.style.visibility = 'hidden';
                            });
                        }
                    });
                }
            }

            // ⭐ VALIDAÇÃO: Limitar a 14 colunas para PDF (com aviso em modal)
            if (visibleColumnsCount > 14) {
                // Armazenar dados para usar após confirmação do modal
                window.pendingPDFData = {
                    elementoClone: elementoClone,
                    fileName: fileName,
                    visibleColumnsCount: visibleColumnsCount
                };
                
                // Mostrar modal ao invés de confirm()
                document.getElementById('modal-column-count').textContent = visibleColumnsCount;
                const modal = new bootstrap.Modal(document.getElementById('pdfWarningModal'));
                modal.show();
                
                // Esconder loading enquanto aguarda decisão do usuário
                document.getElementById('pdf-loading').style.display = 'none';
                return; // Para aqui e aguarda confirmação do modal
            }

            // Se passou da validação, gerar PDF diretamente
            generatePDFWithSettings(elementoClone, fileName, visibleColumnsCount);

        } catch (error) {
            console.error('Error in gerarPDF function:', error);
            if (typeof showAlertModal === 'function') {
                showAlertModal('Error', 'Error generating PDF. Check the console for more details.', 'error');
            } else {
                alert('Error generating PDF. Check the console for more details.');
            }
            document.getElementById('pdf-loading').style.display = 'none';
        }
    }, 100);
}

// ⭐ Event listeners para o modal de aviso PDF
document.addEventListener('DOMContentLoaded', function() {
    const confirmPdfBtn = document.getElementById('confirm-pdf-generation');
    const switchToExcelBtn = document.getElementById('switch-to-excel');
    
    if (confirmPdfBtn) {
        confirmPdfBtn.addEventListener('click', function() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('pdfWarningModal'));
            if (modal) modal.hide();
            
            // Recuperar dados armazenados
            if (window.pendingPDFData) {
                const { elementoClone, fileName, visibleColumnsCount } = window.pendingPDFData;
                
                // Mostrar loading novamente
                document.getElementById('pdf-loading').style.display = 'flex';
                
                // Gerar PDF com as configurações apropriadas
                generatePDFWithSettings(elementoClone, fileName, visibleColumnsCount);
                
                // Limpar dados pendentes
                window.pendingPDFData = null;
            }
        });
    }
    
    if (switchToExcelBtn) {
        switchToExcelBtn.addEventListener('click', function() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('pdfWarningModal'));
            if (modal) modal.hide();
            
            // Limpar dados pendentes
            window.pendingPDFData = null;
            
            // Chamar função de exportar Excel
            exportarExcel();
        });
    }
});

// ⭐ NOVA: Função para exportar Excel
function exportarExcel() {
    try {
        // Coletar colunas visíveis na tabela
        const visibleColumns = [];
        const table = document.getElementById('loadsTable');
        
        if (!table) {
            if (typeof showAlertModal === 'function') {
                showAlertModal('Error', 'Table not found', 'error');
            } else {
                alert('Error: Table not found');
            }
            return;
        }
        
        // Coletar todas as colunas visíveis
        const allHeaders = table.querySelectorAll('th[data-column]');
        allHeaders.forEach(header => {
            const columnName = header.getAttribute('data-column');
            const computedStyle = window.getComputedStyle(header);
            const isVisible = computedStyle.display !== 'none' && 
                             computedStyle.visibility !== 'hidden' &&
                             header.style.display !== 'none';
            
            if (isVisible && columnName) {
                visibleColumns.push(columnName);
            }
        });
        
        // Se não houver colunas visíveis, usar padrão
        if (visibleColumns.length === 0) {
            visibleColumns.push('load_id', 'year_make_model', 'price', 'dispatcher', 
                'driver', 'broker_fee', 'driver_pay', 'payment_status');
        }
        
        // Construir URL com colunas selecionadas
        const invoiceId = {{ $charge->id }};
        const columnsParam = visibleColumns.join(',');
        const baseUrl = "{{ route('time_line_charges.excel', $charge->id) }}";
        const url = baseUrl + `?columns=${encodeURIComponent(columnsParam)}`;
        
        // Mostrar loading
        const loadingDiv = document.getElementById('pdf-loading');
        if (loadingDiv) {
            loadingDiv.querySelector('span').textContent = 'Generating Excel...';
            loadingDiv.style.display = 'flex';
        }
        
        // Abrir URL para download
        window.location.href = url;
        
        // Esconder loading após um delay
        setTimeout(() => {
            if (loadingDiv) {
                loadingDiv.style.display = 'none';
            }
        }, 2000);
        
    } catch (error) {
        console.error('Error exporting Excel:', error);
        if (typeof showAlertModal === 'function') {
            showAlertModal('Error', 'Error exporting Excel. Check the console for more details.', 'error');
        } else {
            alert('Error exporting Excel. Check the console for more details.');
        }
        document.getElementById('pdf-loading').style.display = 'none';
    }
}

// ⭐ NOVA: Função específica para impressão
function imprimirInvoice() {
    try {
        // ⭐ CONTAR COLUNAS VISÍVEIS (mesma lógica do PDF)
        const table = document.getElementById('loadsTable');
        let visibleColumnsCount = 0;
        
        if (table) {
            const allColumns = table.querySelectorAll('th[data-column]');
            allColumns.forEach(th => {
                const computedStyle = window.getComputedStyle(th);
                const isVisible = computedStyle.display !== 'none' && 
                                 computedStyle.visibility !== 'hidden' &&
                                 th.style.display !== 'none';
                if (isVisible) {
                    visibleColumnsCount++;
                }
            });
        }
        
        // ⭐ VALIDAÇÃO: Se > 14 colunas, mostrar modal de aviso
        if (visibleColumnsCount > 14) {
            // Armazenar flag para impressão
            window.pendingPrintData = {
                visibleColumnsCount: visibleColumnsCount
            };
            
            // Mostrar modal (reutilizar o mesmo modal do PDF)
            document.getElementById('modal-column-count').textContent = visibleColumnsCount;
            const modal = new bootstrap.Modal(document.getElementById('pdfWarningModal'));
            
            // Alterar texto do modal para impressão
            const modalTitle = document.querySelector('#pdfWarningModal .modal-title');
            const modalBody = document.querySelector('#pdfWarningModal .modal-body p:last-of-type');
            const confirmBtn = document.getElementById('confirm-pdf-generation');
            
            const originalTitle = modalTitle.innerHTML;
            const originalBody = modalBody.innerHTML;
            const originalBtnText = confirmBtn.innerHTML;
            
            modalTitle.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Too Many Columns for Printing';
            modalBody.innerHTML = 'Do you want to continue with printing anyway?<br><small class="text-muted">Printing will be automatically adjusted to accommodate all columns.</small>';
            confirmBtn.innerHTML = '<i class="fas fa-print me-1"></i> Continue with Printing';
            
            // Event listener temporário para impressão
            const printHandler = function() {
                modal.hide();
                
                // Restaurar textos originais
                modalTitle.innerHTML = originalTitle;
                modalBody.innerHTML = originalBody;
                confirmBtn.innerHTML = originalBtnText;
                
                // Executar impressão
                executePrint(visibleColumnsCount);
                
                window.pendingPrintData = null;
            };
            
            // Adicionar listener temporário (será removido após uso)
            confirmBtn.addEventListener('click', printHandler, { once: true });
            
            // Se clicar em "Exportar Excel" do modal de impressão, também restaurar textos
            const switchToExcelBtn = document.getElementById('switch-to-excel');
            if (switchToExcelBtn) {
                const excelHandler = function() {
                    // Restaurar textos originais antes de fechar
                    modalTitle.innerHTML = originalTitle;
                    modalBody.innerHTML = originalBody;
                    confirmBtn.innerHTML = originalBtnText;
                    window.pendingPrintData = null;
                };
                switchToExcelBtn.addEventListener('click', excelHandler, { once: true });
            }
            
            modal.show();
            return; // Para aqui e aguarda confirmação
        }
        
        // Se passou da validação, imprimir diretamente
        executePrint(visibleColumnsCount);
        
    } catch (error) {
        console.error('Error in imprimirInvoice function:', error);
        if (typeof showAlertModal === 'function') {
            showAlertModal('Error', 'Error preparing print. Check the console for more details.', 'error');
        } else {
            alert('Error preparing print. Check the console for more details.');
        }
    }
}

// ⭐ NOVA: Função para executar impressão com configurações dinâmicas
function executePrint(visibleColumnsCount) {
    // Criar uma nova janela apenas com o conteúdo da invoice
    const conteudoInvoice = document.getElementById('invoice-content').cloneNode(true);

    // Remover elementos que não devem ser impressos
    const elementosRemover = conteudoInvoice.querySelectorAll('.no-print, .btn, button, .modal, .dropdown');
    elementosRemover.forEach(el => el.remove());
    
    // ⭐ APLICAR REDUÇÕES QUANDO > 14 COLUNAS
    if (visibleColumnsCount > 14) {
        const table = conteudoInvoice.querySelector('#loadsTable');
        if (table) {
            table.style.fontSize = '9px';
            const headers = table.querySelectorAll('th');
            headers.forEach(header => {
                header.style.fontSize = '9px';
                header.style.padding = '4px 6px';
            });
            const cells = table.querySelectorAll('td');
            cells.forEach(cell => {
                cell.style.fontSize = '9px';
                cell.style.padding = '4px 6px';
            });
        }
        conteudoInvoice.style.fontSize = '9px';
    }

    // Criar janela de impressão
    const janelaImpressao = window.open('', '_blank');

    // ⭐ PERSONALIZAÇÃO DO TÍTULO DA IMPRESSÃO
    const invoiceId = "{{ $charge->invoice_id ?? 'unknown' }}";
    const carrierName = "{{ $charge->carrier->user->name ?? 'Unknown Carrier' }}";
    const invoiceDate = "{{ $charge->created_at->format('m/d/Y') }}";
    
    // ⭐ DETERMINAR TAMANHO DE PÁGINA E MARGENS DINAMICAMENTE
    let pageSize = 'A4';
    let pageOrientation = 'portrait';
    let pageMargin = '0.5in';
    let bodyFontSize = '14px';
    
    if (visibleColumnsCount <= 10) {
        pageSize = 'A4';
        pageOrientation = 'landscape';
        pageMargin = '0.5in';
        bodyFontSize = '14px';
    } else if (visibleColumnsCount <= 14) {
        pageSize = 'A3';
        pageOrientation = 'landscape';
        pageMargin = '0.4in';
        bodyFontSize = '12px';
    } else {
        // Mais de 14 colunas: reduzir tudo
        pageSize = 'A3';
        pageOrientation = 'landscape';
        pageMargin = '0.3in';
        bodyFontSize = '9px';
    }
    
    janelaImpressao.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invoice #${invoiceId} - ${carrierName} - ${invoiceDate}</title>
            <meta charset="utf-8">
            <style>
                @page {
                    size: ${pageSize} ${pageOrientation};
                    margin: ${pageMargin};
                }

                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    background: white;
                    color: black;
                    font-size: ${bodyFontSize};
                }

                .invoice-header {
                    background: #013d81 !important;
                    color: white !important;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .invoice-info-card, .company-info {
                    background: #f8f9fa;
                    border: 1px solid #e9ecef;
                    border-radius: 8px;
                    padding: 20px;
                    margin-bottom: 20px;
                    page-break-inside: avoid;
                }

                .custom-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                    background: white;
                }

                .custom-table th {
                    background: #013d81 !important;
                    color: white !important;
                    padding: ${visibleColumnsCount > 14 ? '4px 6px' : '12px'};
                    text-align: left;
                    font-weight: 600;
                    border: 1px solid #000;
                    font-size: ${visibleColumnsCount > 14 ? '9px' : 'inherit'};
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .custom-table td {
                    padding: ${visibleColumnsCount > 14 ? '4px 6px' : '10px 12px'};
                    border: 1px solid #ddd;
                    vertical-align: middle;
                    font-size: ${visibleColumnsCount > 14 ? '9px' : 'inherit'};
                }

                .custom-table tbody tr:nth-child(even) {
                    background-color: #f8f9fa;
                }

                .total-section {
                    background: #28a745 !important;
                    color: white !important;
                    padding: 20px;
                    border-radius: 8px;
                    margin-top: 20px;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .status-badge {
                    padding: 4px 8px;
                    border-radius: 15px;
                    font-size: 11px;
                    font-weight: 600;
                    border: 1px solid;
                }

                .bg-success {
                    background-color: #28a745 !important;
                    color: white !important;
                    border-color: #28a745 !important;
                }

                .bg-warning {
                    background-color: #ffc107 !important;
                    color: #000 !important;
                    border-color: #ffc107 !important;
                }

                .bg-danger {
                    background-color: #dc3545 !important;
                    color: white !important;
                    border-color: #dc3545 !important;
                }

                .bg-secondary {
                    background-color: #6c757d !important;
                    color: white !important;
                    border-color: #6c757d !important;
                }

                .badge {
                    padding: 4px 8px;
                    border-radius: 15px;
                    font-size: 11px;
                    font-weight: 600;
                }

                .text-primary { color: #013d81 !important; }
                .text-success { color: #28a745 !important; }
                .text-info { color: #17a2b8 !important; }
                .text-warning { color: #ffc107 !important; }
                .text-muted { color: #6c757d !important; }

                .row {
                    display: flex;
                    flex-wrap: wrap;
                    margin: 0 -15px;
                }

                .col-md-4, .col-md-6, .col-md-8 {
                    padding: 0 15px;
                }

                .col-md-4 { width: 33.333333%; }
                .col-md-6 { width: 50%; }
                .col-md-8 { width: 66.666667%; }

                .d-flex {
                    display: flex !important;
                }

                .justify-content-between {
                    justify-content: space-between !important;
                }

                .align-items-center {
                    align-items: center !important;
                }

                .text-end {
                    text-align: right !important;
                }

                .mb-0 { margin-bottom: 0 !important; }
                .mb-1 { margin-bottom: 0.25rem !important; }
                .mb-2 { margin-bottom: 0.5rem !important; }
                .mb-3 { margin-bottom: 1rem !important; }
                .mt-2 { margin-top: 0.5rem !important; }
                .mt-3 { margin-top: 1rem !important; }
                .mt-4 { margin-top: 1.5rem !important; }
                .pt-2 { padding-top: 0.5rem !important; }
                .pt-3 { padding-top: 1rem !important; }

                .border-top {
                    border-top: 1px solid #dee2e6 !important;
                }

                .border-light {
                    border-color: rgba(255,255,255,0.3) !important;
                }

                .fw-bold {
                    font-weight: 700 !important;
                }

                .h5 {
                    font-size: 1.25rem !important;
                    font-weight: 500;
                }

                .h6 {
                    font-size: 1rem !important;
                    font-weight: 500;
                }

                .table-warning {
                    background-color: #fff3cd !important;
                    border-color: #ffeaa7;
                }

                .alert {
                    padding: 12px 16px;
                    margin-bottom: 20px;
                    border: 1px solid transparent;
                    border-radius: 4px;
                }

                .alert-warning {
                    color: #856404;
                    background-color: #fff3cd;
                    border-color: #ffeaa7;
                }

                /* Quebras de página */
                .page-break {
                    page-break-before: always;
                }

                .custom-table {
                    page-break-inside: avoid;
                }

                .invoice-info-card,
                .company-info,
                .total-section {
                    page-break-inside: avoid;
                }
            </style>
        </head>
        <body>
    `);

    janelaImpressao.document.write(conteudoInvoice.outerHTML);
    janelaImpressao.document.write('</body></html>');

    janelaImpressao.document.close();

    // Aguardar carregamento e imprimir
    janelaImpressao.onload = function() {
        janelaImpressao.focus();
        janelaImpressao.print();
        janelaImpressao.close();
    };

    // Fallback caso onload não funcione
    setTimeout(() => {
        janelaImpressao.focus();
        janelaImpressao.print();
        janelaImpressao.close();
    }, 1000);
}

// ⭐ MELHORADO: Função de impressão alternativa (usando window.print)
function imprimirAlternativo() {
    // Ocultar todos os elementos que não devem ser impressos
    const elementosOcultar = document.querySelectorAll('.navbar, .sidebar, .main-sidebar, .control-sidebar, .main-header, .main-footer, .no-print');
    const estadoOriginal = [];

    // Guardar estado original e ocultar elementos
    elementosOcultar.forEach((el, index) => {
        estadoOriginal[index] = el.style.display;
        el.style.display = 'none';
    });

    // Ajustar o corpo da página para impressão
    const bodyOriginal = document.body.style.cssText;
    document.body.style.cssText = 'margin: 0; padding: 0; background: white;';

    // Ajustar o conteúdo principal
    const conteudoPrincipal = document.getElementById('invoice-content');
    const estiloOriginalConteudo = conteudoPrincipal.style.cssText;
    conteudoPrincipal.style.cssText = 'margin: 0; padding: 20px; background: white; width: 100%;';

    // Imprimir
    window.print();

    // Restaurar estado original após impressão
    setTimeout(() => {
        document.body.style.cssText = bodyOriginal;
        conteudoPrincipal.style.cssText = estiloOriginalConteudo;

        elementosOcultar.forEach((el, index) => {
            el.style.display = estadoOriginal[index];
        });
    }, 1000);
}

// Funcionalidade de seleção de colunas
function initColumnSelector() {
    const modal = document.getElementById('columnSelectionModal');
    const selectAllBtn = document.getElementById('selectAllColumns');
    const deselectAllBtn = document.getElementById('deselectAllColumns');
    const resetDefaultBtn = document.getElementById('resetToDefault');
    const applyBtn = document.getElementById('applyColumnSelection');
    
    // Colunas padrão (visíveis inicialmente)
    const defaultColumns = [
        'load_id', 'year_make_model', 'price', 'dispatcher', 
        'driver', 'broker_fee', 'driver_pay', 'payment_status'
    ];
    
    // Selecionar todas as colunas
    selectAllBtn.addEventListener('click', function() {
        const checkboxes = modal.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
    });
    
    // Desselecionar todas as colunas
    deselectAllBtn.addEventListener('click', function() {
        const checkboxes = modal.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    });
    
    // Redefinir para padrão
    resetDefaultBtn.addEventListener('click', function() {
        const checkboxes = modal.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            const columnName = checkbox.getAttribute('data-column');
            checkbox.checked = defaultColumns.includes(columnName);
        });
    });
    
    // Aplicar seleção
    applyBtn.addEventListener('click', function() {
        const checkboxes = modal.querySelectorAll('input[type="checkbox"].column-toggle');
        const table = document.getElementById('loadsTable');
        
        if (!table) {
            console.error('Table not found');
            return;
        }
        
        checkboxes.forEach(checkbox => {
            const columnName = checkbox.getAttribute('data-column');
            const isChecked = checkbox.checked;
            
            if (!columnName) return;
            
            // Mostrar/ocultar cabeçalhos (th)
            const headers = table.querySelectorAll(`th[data-column="${columnName}"], th.column-${columnName}`);
            headers.forEach(header => {
                header.style.display = isChecked ? '' : 'none';
            });
            
            // Mostrar/ocultar células do corpo (td)
            const cells = table.querySelectorAll(`td.column-${columnName}, td[data-column="${columnName}"]`);
            cells.forEach(cell => {
                cell.style.display = isChecked ? '' : 'none';
            });
        });
        
        // Fechar modal
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        if (bootstrapModal) {
            bootstrapModal.hide();
        }
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar seleção de colunas
    initColumnSelector();
    
    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Verificar se html2pdf está carregado
    if (typeof html2pdf === 'undefined') {
        console.warn('html2pdf library not loaded. PDF generation may not work.');
    }

    // Event listeners para teclas de atalho
    document.addEventListener('keydown', function(e) {
        // Ctrl+P para imprimir
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            imprimirInvoice();
        }

        // Ctrl+S para baixar PDF
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            gerarPDF();
        }
    });
});

// ⭐ NOVA: Função para testar PDF sem html2pdf (usando window.print)
function baixarPDFAlternativo() {
    if (typeof showAlertModal === 'function') {
        showAlertModal('Print to PDF', 'To download as PDF, use the "Save as PDF" option in the print window that will open.', 'info');
    } else {
        alert('To download as PDF, use the "Save as PDF" option in the print window that will open.');
    }
    imprimirInvoice();
}

// ⭐ NOVA: Verificação de dependências
function verificarDependencias() {
    if (typeof html2pdf === 'undefined') {
        console.error('html2pdf library not found');
        return false;
    }
    return true;
}

// ⭐ MELHORADO: Função de debug para PDF
function debugPDF() {
    console.log('=== DEBUG PDF ===');
    console.log('html2pdf disponível:', typeof html2pdf !== 'undefined');
    console.log('Elemento invoice-content:', document.getElementById('invoice-content'));
    console.log('Invoice ID:', '{{ $charge->invoice_id ?? "N/A" }}');

    const elemento = document.getElementById('invoice-content');
    if (elemento) {
        console.log('Dimensões do elemento:', {
            width: elemento.offsetWidth,
            height: elemento.offsetHeight,
            scrollWidth: elemento.scrollWidth,
            scrollHeight: elemento.scrollHeight
        });
    }
}

// Executar debug automaticamente (pode remover em produção)
// debugPDF();
</script>

@endsection
