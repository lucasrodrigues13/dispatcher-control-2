@extends("layouts.app2")

@section('conteudo')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add New Load</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('loads.index') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ route('loads.index') }}">Loads</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    Add New
                </li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="seta-voltar">
                            <a href="{{ route('loads.index') }}"><i class="fas fa-arrow-left"></i></a>
                        </div>
                        <h4 class="card-title ms-2 mb-0">Load Information</h4>
                    </div>

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('loads.store') }}">
                            @csrf

                            {{-- ====================================== --}}
                            {{-- IDENTIFICAÇÃO / BÁSICO --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Basic Identification</h5>
                            <div class="row mb-4">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                    <label for="arquivo" class="form-label fw-semibold">Dispatcher</label>
                                    <select name="dispatcher_id" id="" class="form-select" required>
                                        <option value="" disabled selected>Select Dispatcher</option>
                                        @foreach($dispatchers as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->user ? $item->user->name : ($item->name ?? 'User #' . $item->id) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                    <label for="arquivo" class="form-label fw-semibold">Carrier</label>
                                    <select name="carrier_id" id="" class="form-select" required>
                                        <option value="" disabled selected>Select Carrier</option>
                                        @foreach($carriers as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->company_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="load_id" class="form-label">Load ID <span class="text-danger">*</span></label>
                                    <input type="number" name="load_id" id="load_id" class="form-control"
                                        placeholder="Enter Load ID" value="{{ old('load_id') }}" required>
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label for="internal_load_id" class="form-label">Internal Load ID</label>
                                    <input type="text" name="internal_load_id" id="internal_load_id" class="form-control"
                                        placeholder="Enter Internal Load ID" value="{{ old('internal_load_id') }}">
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label for="creation_date" class="form-label">Creation Date</label>
                                    <input type="date" name="creation_date" id="creation_date" class="form-control"
                                        value="{{ old('creation_date') }}">
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label for="dispatcher" class="form-label">Dispatcher</label>
                                    <input type="text" name="dispatcher" id="dispatcher" class="form-control"
                                        placeholder="Enter Dispatcher" value="{{ old('dispatcher') }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="mb-3 col-md-3">
                                    <label for="trip" class="form-label">Trip</label>
                                    <input type="text" name="trip" id="trip" class="form-control"
                                        placeholder="Enter Trip" value="{{ old('trip') }}">
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label for="year_make_model" class="form-label">
                                        Year, Make, Model
                                        <small class="text-muted">(Auto-filled from VIN)</small>
                                    </label>
                                    <input type="text" name="year_make_model" id="year_make_model" class="form-control"
                                        placeholder="e.g. 2025 Ford F-150" value="{{ old('year_make_model') }}"
                                        style="background-color: #f8f9fa;" readonly>
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label for="vin" class="form-label">
                                        VIN
                                        <small class="text-muted">(17 characters)</small>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="vin" id="vin" class="form-control"
                                            placeholder="Enter VIN" value="{{ old('vin') }}" maxlength="17"
                                            style="text-transform: uppercase;">
                                        <button type="button" id="decodeVinBtn" class="btn btn-outline-primary"
                                                title="Decode VIN" disabled>
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <div id="vinMessage" class="mt-1"></div>

                                    <!-- Loading Spinner -->
                                    <div id="loadingSpinner" class="mt-2" style="display: none;">
                                        <div class="d-flex align-items-center">
                                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <small class="text-muted">Decoding VIN...</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-md-2">
                                    <label for="lot_number" class="form-label">Lot Number</label>
                                    <input type="text" name="lot_number" id="lot_number" class="form-control"
                                        placeholder="Enter Lot Number" value="{{ old('lot_number') }}">
                                </div>
                            </div>

                            <!-- VIN Decoded Information Card -->
                            <div id="vehicleInfoCard" class="alert alert-info" style="display: none;">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Vehicle Information (Decoded from VIN)
                                </h6>
                                <div id="vehicleInfoContent" class="row"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="clearVinData()">
                                    <i class="fas fa-times me-1"></i>Clear VIN Data
                                </button>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-md-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="has_terminal" id="has_terminal" class="form-check-input" value="1"
                                            {{ old('has_terminal') ? 'checked' : '' }}>
                                        <label for="has_terminal" class="form-check-label">Has Terminal</label>
                                    </div>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="dispatched_to_carrier" class="form-label">Dispatched to Carrier</label>
                                    <input type="text" name="dispatched_to_carrier" id="dispatched_to_carrier" class="form-control"
                                        placeholder="Enter Carrier" value="{{ old('dispatched_to_carrier') }}">
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES DE PICKUP --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Pickup Information</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-6">
                                    <label for="pickup_name" class="form-label">Name</label>
                                    <input type="text" name="pickup_name" id="pickup_name" class="form-control"
                                        placeholder="Enter Name" value="{{ old('pickup_name') }}">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="pickup_address" class="form-label">Address</label>
                                    <input type="text" name="pickup_address" id="pickup_address" class="form-control"
                                        placeholder="Enter Address" value="{{ old('pickup_address') }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="mb-3 col-md-3">
                                    <label for="pickup_city" class="form-label">City</label>
                                    <input type="text" name="pickup_city" id="pickup_city" class="form-control"
                                        placeholder="Enter City" value="{{ old('pickup_city') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="pickup_state" class="form-label">State</label>
                                    <input type="text" name="pickup_state" id="pickup_state" class="form-control"
                                        placeholder="Enter State" value="{{ old('pickup_state') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="pickup_zip" class="form-label">ZIP</label>
                                    <input type="text" name="pickup_zip" id="pickup_zip" class="form-control"
                                        placeholder="Enter ZIP" value="{{ old('pickup_zip') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="scheduled_pickup_date" class="form-label">Scheduled Date</label>
                                    <input type="date" name="scheduled_pickup_date" id="scheduled_pickup_date" class="form-control"
                                        value="{{ old('scheduled_pickup_date') }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-md-3">
                                    <label for="pickup_phone" class="form-label">Phone</label>
                                    <input type="text" name="pickup_phone" id="pickup_phone" class="form-control"
                                        placeholder="Enter Phone" value="{{ old('pickup_phone') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="pickup_mobile" class="form-label">Mobile</label>
                                    <input type="text" name="pickup_mobile" id="pickup_mobile" class="form-control"
                                        placeholder="Enter Mobile" value="{{ old('pickup_mobile') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="actual_pickup_date" class="form-label">Actual Date</label>
                                    <input type="date" name="actual_pickup_date" id="actual_pickup_date" class="form-control"
                                        value="{{ old('actual_pickup_date') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="buyer_number" class="form-label">Buyer Number</label>
                                    <input type="number" name="buyer_number" id="buyer_number" class="form-control"
                                        placeholder="Enter Buyer Number" value="{{ old('buyer_number') }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-12">
                                    <label for="pickup_notes" class="form-label">Notes</label>
                                    <textarea name="pickup_notes" id="pickup_notes" class="form-control" rows="2"
                                        placeholder="Enter any notes">{{ old('pickup_notes') }}</textarea>
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES DE DELIVERY --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Delivery Information</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-6">
                                    <label for="delivery_name" class="form-label">Name</label>
                                    <input type="text" name="delivery_name" id="delivery_name" class="form-control"
                                        placeholder="Enter Delivery Name" value="{{ old('delivery_name') }}">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="delivery_address" class="form-label">Address</label>
                                    <input type="text" name="delivery_address" id="delivery_address" class="form-control"
                                        placeholder="Enter Delivery Address" value="{{ old('delivery_address') }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_city" class="form-label">City</label>
                                    <input type="text" name="delivery_city" id="delivery_city" class="form-control"
                                        placeholder="Enter City" value="{{ old('delivery_city') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_state" class="form-label">State</label>
                                    <input type="text" name="delivery_state" id="delivery_state" class="form-control"
                                        placeholder="Enter State" value="{{ old('delivery_state') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_zip" class="form-label">ZIP</label>
                                    <input type="text" name="delivery_zip" id="delivery_zip" class="form-control"
                                        placeholder="Enter ZIP" value="{{ old('delivery_zip') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="scheduled_delivery_date" class="form-label">Scheduled Delivery Date</label>
                                    <input type="date" name="scheduled_delivery_date" id="scheduled_delivery_date" class="form-control"
                                        value="{{ old('scheduled_delivery_date') }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-md-3">
                                    <label for="actual_delivery_date" class="form-label">Actual Delivery Date</label>
                                    <input type="date" name="actual_delivery_date" id="actual_delivery_date" class="form-control"
                                        value="{{ old('actual_delivery_date') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_phone" class="form-label">Phone</label>
                                    <input type="text" name="delivery_phone" id="delivery_phone" class="form-control"
                                        placeholder="Enter Phone" value="{{ old('delivery_phone') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_mobile" class="form-label">Mobile</label>
                                    <input type="text" name="delivery_mobile" id="delivery_mobile" class="form-control"
                                        placeholder="Enter Mobile" value="{{ old('delivery_mobile') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_notes" class="form-label">Notes</label>
                                    <textarea name="delivery_notes" id="delivery_notes" class="form-control" rows="2"
                                        placeholder="Enter any notes">{{ old('delivery_notes') }}</textarea>
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES DE SHIPPER --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Shipper Information</h5>
                            <div class="row mb-5">
                                <div class="mb-3 col-md-6">
                                    <label for="shipper_name" class="form-label">Name</label>
                                    <input type="text" name="shipper_name" id="shipper_name" class="form-control"
                                        placeholder="Enter Shipper Name" value="{{ old('shipper_name') }}">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="shipper_phone" class="form-label">Phone</label>
                                    <input type="text" name="shipper_phone" id="shipper_phone" class="form-control"
                                        placeholder="Enter Phone" value="{{ old('shipper_phone') }}">
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES FINANCEIRAS --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Finance Information</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-3">
                                    <label for="price" class="form-label">Price ($)</label>
                                    <input type="number" step="0.01" name="price" id="price" class="form-control"
                                        placeholder="0.00" value="{{ old('price') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="expenses" class="form-label">Expenses ($)</label>
                                    <input type="number" step="0.01" name="expenses" id="expenses" class="form-control"
                                        placeholder="0.00" value="{{ old('expenses') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="broker_fee" class="form-label">Broker Fee ($)</label>
                                    <input type="number" step="0.01" name="broker_fee" id="broker_fee" class="form-control"
                                        placeholder="0.00" value="{{ old('broker_fee') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="driver_pay" class="form-label">Driver Pay ($)</label>
                                    <input type="number" step="0.01" name="driver_pay" id="driver_pay" class="form-control"
                                        placeholder="0.00" value="{{ old('driver_pay') }}">
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES DE PAGAMENTO --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Payment Information</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-3">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <input type="text" name="payment_method" id="payment_method" class="form-control"
                                        placeholder="Enter Payment Method" value="{{ old('payment_method') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="paid_amount" class="form-label">Paid Amount ($)</label>
                                    <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control"
                                        placeholder="0.00" value="{{ old('paid_amount') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="paid_method" class="form-label">Paid Method</label>
                                    <input type="text" name="paid_method" id="paid_method" class="form-control"
                                        placeholder="Enter Paid Method" value="{{ old('paid_method') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" name="reference_number" id="reference_number" class="form-control"
                                        placeholder="Enter Reference Number" value="{{ old('reference_number') }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-md-3">
                                    <label for="receipt_date" class="form-label">Receipt Date</label>
                                    <input type="date" name="receipt_date" id="receipt_date" class="form-control"
                                        value="{{ old('receipt_date') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="payment_terms" class="form-label">Payment Terms</label>
                                    <input type="text" name="payment_terms" id="payment_terms" class="form-control"
                                        placeholder="Enter Payment Terms" value="{{ old('payment_terms') }}">
                                </div>
                                <div class="mb-3 col-6">
                                    <label for="payment_notes" class="form-label">Payment Notes</label>
                                    <textarea name="payment_notes" id="payment_notes" class="form-control" rows="1"
                                        placeholder="Enter any notes">{{ old('payment_notes') }}</textarea>
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES DE INVOICE --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Invoice Information</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-4">
                                    <label for="payment_status" class="form-label">Payment Status</label>
                                    <input type="text" name="payment_status" id="payment_status" class="form-control"
                                        placeholder="Enter Payment Status" value="{{ old('payment_status') }}">
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label for="invoice_number" class="form-label">Number</label>
                                    <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                                        placeholder="Enter Number" value="{{ old('invoice_number') }}">
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label for="invoice_date" class="form-label">Date</label>
                                    <input type="date" name="invoice_date" id="invoice_date" class="form-control"
                                        value="{{ old('invoice_date') }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-12">
                                    <label for="invoice_notes" class="form-label">Notes</label>
                                    <textarea name="invoice_notes" id="invoice_notes" class="form-control" rows="2"
                                        placeholder="Enter any notes">{{ old('invoice_notes') }}</textarea>
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- OUTROS --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Others</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-4">
                                    <label for="driver" class="form-label">Driver</label>
                                    <input type="text" name="driver" id="driver" class="form-control"
                                        placeholder="Enter Driver" value="{{ old('driver') }}">
                                </div>
                            </div>

                            {{-- BOTÕES --}}
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('loads.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.vehicle-detail-item {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}
.vehicle-detail-item:last-child {
    border-bottom: none;
}
.vehicle-detail-label {
    font-weight: 600;
    color: #495057;
    flex: 0 0 50%;
}
.vehicle-detail-value {
    color: #212529;
    text-align: right;
    font-weight: 500;
}
#vehicleInfoCard {
    animation: fadeIn 0.3s ease-in;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Destaque especial para informações de peso */
.bg-light .vehicle-detail-item {
    border-bottom: 1px solid rgba(0,0,0,0.05);
    margin-bottom: 2px;
}

.bg-light h6 {
    border-bottom: 2px solid #28a745;
    padding-bottom: 5px;
    margin-bottom: 10px;
}

/* Melhorar layout responsivo */
@media (max-width: 768px) {
    .vehicle-detail-item {
        flex-direction: column;
        text-align: left;
    }
    .vehicle-detail-value {
        text-align: left;
        font-weight: 600;
        color: #007bff;
    }
}

/* Estilização do campo Year Make Model quando preenchido */
#year_make_model.filled {
    background-color: #e7f3ff !important;
    border-color: #007bff;
    font-weight: 600;
}
</style>

<script>
// VIN Decoder Implementation
class VinDecoder {
    constructor() {
        this.apiUrl = 'https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/';
        this.vinInput = document.getElementById('vin');
        this.decodeBtn = document.getElementById('decodeVinBtn');
        this.messageDiv = document.getElementById('vinMessage');
        this.loadingSpinner = document.getElementById('loadingSpinner');
        this.vehicleInfoCard = document.getElementById('vehicleInfoCard');
        this.vehicleInfoContent = document.getElementById('vehicleInfoContent');
        this.yearMakeModelInput = document.getElementById('year_make_model');

        this.initializeEventListeners();
    }

    initializeEventListeners() {
        // VIN input validation
        this.vinInput.addEventListener('input', (e) => {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            // Remove I, O, Q characters (not allowed in VIN)
            value = value.replace(/[IOQ]/g, '');
            e.target.value = value;
            this.validateVinLength(value);
        });

        // Decode button
        this.decodeBtn.addEventListener('click', () => {
            this.decodeVin();
        });

        // Enter key on VIN input
        this.vinInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (this.vinInput.value.length === 17) {
                    this.decodeVin();
                }
            }
        });

        // Auto-decode when VIN has 17 characters
        this.vinInput.addEventListener('blur', () => {
            if (this.vinInput.value.length === 17) {
                this.decodeVin();
            }
        });
    }

    validateVinLength(vin) {
        const isValid = vin.length === 17;
        this.decodeBtn.disabled = !isValid;

        if (vin.length > 0 && vin.length < 17) {
            this.showMessage(`VIN deve ter 17 caracteres (atual: ${vin.length})`, 'warning');
        } else if (vin.length === 17) {
            this.showMessage('VIN válido - Clique para decodificar', 'success');
        } else {
            this.clearMessage();
        }
    }

    async decodeVin() {
        const vin = this.vinInput.value.trim();

        if (vin.length !== 17) {
            this.showMessage('Digite um VIN válido de 17 caracteres', 'error');
            return;
        }

        this.showLoading(true);
        this.hideVehicleInfo();
        this.clearMessage();

        try {
            const response = await fetch(`${this.apiUrl}${vin}?format=json`);
            const data = await response.json();

            if (data.Results && data.Results.length > 0) {
                this.processVinData(data.Results);
                this.showMessage('VIN decodificado com sucesso!', 'success');
            } else {
                this.showMessage('Nenhum dado encontrado para este VIN', 'error');
            }
        } catch (error) {
            console.error('VIN decode error:', error);
            this.showMessage('Erro ao decodificar VIN. Tente novamente.', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    processVinData(results) {
        // Extract relevant vehicle information
        const vehicleData = {};

        results.forEach(item => {
            if (item.Value && item.Value !== 'Not Applicable' && item.Value !== null && item.Value !== '') {
                vehicleData[item.Variable] = item.Value;
            }
        });

        // Check for errors
        const errorCode = vehicleData['Error Code'] || '';
        if (errorCode && errorCode !== '0') {
            this.showMessage('VIN pode estar inválido ou incompleto', 'warning');
        }

        // Build year make model string
        const year = vehicleData['Model Year'] || '';
        const make = vehicleData['Make'] || '';
        const model = vehicleData['Model'] || '';
        const trim = vehicleData['Trim'] || '';

        // Criar string mais completa
        let yearMakeModel = `${year} ${make} ${model}`.trim();
        if (trim) {
            yearMakeModel += ` (${trim})`;
        }

        // Auto-fill the year_make_model field
        if (yearMakeModel) {
            this.yearMakeModelInput.value = yearMakeModel;
            this.yearMakeModelInput.style.backgroundColor = '#e7f3ff';
            this.yearMakeModelInput.classList.add('filled');
        }

        // Display vehicle information with weight details
        this.displayVehicleInfo(vehicleData);
    }

    displayVehicleInfo(data) {
        // Extrair informações de peso
        const weightInfo = this.extractWeightInfo(data);

        // Criar HTML estruturado com peso em destaque
        const html = `
            <div class="row">
                <!-- Informações Básicas do Veículo -->
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-car me-2"></i>Informações do Veículo
                    </h6>
                    ${this.createDetailItem('Ano', data['Model Year'])}
                    ${this.createDetailItem('Marca', data['Make'])}
                    ${this.createDetailItem('Modelo', data['Model'])}
                    ${this.createDetailItem('Versão/Trim', data['Trim'])}
                    ${this.createDetailItem('Classe', data['Body Class'])}
                    ${this.createDetailItem('Tipo', data['Vehicle Type'])}
                    ${this.createDetailItem('Portas', data['Doors'])}
                    ${this.createDetailItem('Fabricante', data['Manufacturer Name'])}
                </div>

                <!-- Informações de Peso e Especificações -->
                <div class="col-md-6">
                    <h6 class="text-success mb-3">
                        <i class="fas fa-weight me-2"></i>Peso e Especificações
                    </h6>

                    <!-- Seção de Peso -->
                    <div class="bg-light p-3 rounded mb-3">
                        <h6 class="text-success mb-2">⚖️ Informações de Peso</h6>
                        ${weightInfo.gvwrClass ? `
                            <div class="vehicle-detail-item">
                                <span class="vehicle-detail-label"><strong>Peso Estimado:</strong></span>
                                <span class="vehicle-detail-value text-primary"><strong>${weightInfo.gvwrClass.average.toLocaleString()} lb</strong></span>
                            </div>
                            <div class="vehicle-detail-item">
                                <span class="vehicle-detail-label">Faixa GVWR:</span>
                                <span class="vehicle-detail-value">${weightInfo.gvwrClass.min.toLocaleString()} - ${weightInfo.gvwrClass.max.toLocaleString()} lb</span>
                            </div>
                            <div class="vehicle-detail-item">
                                <span class="vehicle-detail-label">Classificação:</span>
                                <span class="vehicle-detail-value">${weightInfo.gvwrClass.class}</span>
                            </div>
                            <div class="vehicle-detail-item">
                                <span class="vehicle-detail-label">Em KG (aprox):</span>
                                <span class="vehicle-detail-value text-info"><strong>${Math.round(weightInfo.gvwrClass.average * 0.453592).toLocaleString()} kg</strong></span>
                            </div>
                        ` : `
                            <div class="vehicle-detail-item">
                                <span class="vehicle-detail-label">GVWR:</span>
                                <span class="vehicle-detail-value">${data['GVWR'] || 'Não disponível'}</span>
                            </div>
                        `}
                        ${data['CurbWeightLB'] ? `
                            <div class="vehicle-detail-item">
                                <span class="vehicle-detail-label">Peso Vazio:</span>
                                <span class="vehicle-detail-value">${parseFloat(data['CurbWeightLB']).toLocaleString()} lb (${Math.round(parseFloat(data['CurbWeightLB']) * 0.453592).toLocaleString()} kg)</span>
                            </div>
                        ` : ''}
                    </div>

                    <!-- Motor e Transmissão -->
                    ${data['Engine Number of Cylinders'] || data['Displacement (L)'] || data['Transmission Style'] ? `
                    <div class="bg-light p-3 rounded">
                        <h6 class="text-info mb-2">🔧 Motor e Transmissão</h6>
                        ${this.createDetailItem('Cilindros', data['Engine Number of Cylinders'])}
                        ${this.createDetailItem('Cilindrada', data['Displacement (L)'] ? data['Displacement (L)'] + 'L' : null)}
                        ${this.createDetailItem('Configuração', data['Engine Configuration'])}
                        ${this.createDetailItem('Potência', data['EngineHP'] ? data['EngineHP'] + ' HP' : null)}
                        ${this.createDetailItem('Combustível', data['Fuel Type - Primary'])}
                        ${this.createDetailItem('Transmissão', data['Transmission Style'])}
                        ${this.createDetailItem('Tração', data['Drive Type'])}
                    </div>
                    ` : ''}
                </div>
            </div>

            <!-- Informações Adicionais -->
            ${data['Plant Country'] || data['Plant City'] || data['Series'] ? `
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="text-secondary mb-2">
                        <i class="fas fa-info-circle me-2"></i>Informações Adicionais
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            ${this.createDetailItem('País de Fabricação', data['Plant Country'])}
                            ${this.createDetailItem('Cidade', data['Plant City'])}
                            ${this.createDetailItem('Estado', data['Plant State'])}
                        </div>
                        <div class="col-md-6">
                            ${this.createDetailItem('Série', data['Series'])}
                            ${this.createDetailItem('Modelo do Motor', data['Engine Model'])}
                            ${this.createDetailItem('Eletrificação', data['ElectrificationLevel'])}
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
        `;

        this.vehicleInfoContent.innerHTML = html;
        this.showVehicleInfo();
    }

    createDetailItem(label, value) {
        if (!value || value === '') return '';
        return `
            <div class="vehicle-detail-item">
                <span class="vehicle-detail-label">${label}:</span>
                <span class="vehicle-detail-value">${value}</span>
            </div>
        `;
    }

    extractWeightInfo(vehicleData) {
        const weightInfo = {
            gvwr: null,
            gvwrClass: null,
            curbWeight: null
        };

        // GVWR (Gross Vehicle Weight Rating)
        if (vehicleData['GVWR']) {
            weightInfo.gvwr = vehicleData['GVWR'];
            weightInfo.gvwrClass = this.parseGVWRClass(vehicleData['GVWR']);
        }

        // Peso do veículo vazio
        if (vehicleData['CurbWeightLB']) {
            weightInfo.curbWeight = parseFloat(vehicleData['CurbWeightLB']);
        }

        return weightInfo;
    }

    parseGVWRClass(gvwr) {
        // Extrair peso numérico da string GVWR
        const match = gvwr.match(/(\d{1,3}(?:,\d{3})*)\s*-\s*(\d{1,3}(?:,\d{3})*)\s*lb/);
        if (match) {
            const minWeight = parseInt(match[1].replace(/,/g, ''));
            const maxWeight = parseInt(match[2].replace(/,/g, ''));
            return {
                min: minWeight,
                max: maxWeight,
                average: Math.round((minWeight + maxWeight) / 2),
                class: this.getWeightClass(minWeight, maxWeight)
            };
        }
        return null;
    }

    getWeightClass(minWeight, maxWeight) {
        // Classificação baseada no GVWR
        const avgWeight = (minWeight + maxWeight) / 2;

        if (avgWeight <= 6000) return 'Veículo Leve';
        if (avgWeight <= 10000) return 'Veículo Médio';
        if (avgWeight <= 26000) return 'Veículo Pesado';
        return 'Veículo Muito Pesado';
    }

    showVehicleInfo() {
        this.vehicleInfoCard.style.display = 'block';
    }

    hideVehicleInfo() {
        this.vehicleInfoCard.style.display = 'none';
    }

    showLoading(show) {
        this.loadingSpinner.style.display = show ? 'block' : 'none';
        this.decodeBtn.disabled = show;
    }

    showMessage(message, type) {
        let iconClass = 'fas fa-info-circle';
        let textClass = 'text-info';

        switch(type) {
            case 'error':
                iconClass = 'fas fa-exclamation-triangle';
                textClass = 'text-danger';
                break;
            case 'success':
                iconClass = 'fas fa-check-circle';
                textClass = 'text-success';
                break;
            case 'warning':
                iconClass = 'fas fa-exclamation-circle';
                textClass = 'text-warning';
                break;
        }

        this.messageDiv.innerHTML = `<small class="${textClass}"><i class="${iconClass} me-1"></i>${message}</small>`;
    }

    clearMessage() {
        this.messageDiv.innerHTML = '';
    }
}

// Clear VIN data function (global)
function clearVinData() {
    document.getElementById('vin').value = '';
    const yearMakeModelField = document.getElementById('year_make_model');
    yearMakeModelField.value = '';
    yearMakeModelField.style.backgroundColor = '#f8f9fa';
    yearMakeModelField.classList.remove('filled');
    document.getElementById('vehicleInfoCard').style.display = 'none';
    document.getElementById('vinMessage').innerHTML = '';
    document.getElementById('decodeVinBtn').disabled = true;
}

// Initialize VIN Decoder when page loads
document.addEventListener('DOMContentLoaded', function() {
    new VinDecoder();

    // Test VIN button for development
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        const vinInput = document.getElementById('vin');
        const testButton = document.createElement('button');
        testButton.type = 'button';
        testButton.className = 'btn btn-sm btn-outline-info mt-1';
        testButton.innerHTML = '<i class="fas fa-vial me-1"></i>Test VIN';
        testButton.onclick = function() {
            vinInput.value = '1HGBH41JXMN109186';
            vinInput.dispatchEvent(new Event('input'));
            setTimeout(() => {
                document.getElementById('decodeVinBtn').click();
            }, 500);
        };
        vinInput.parentNode.appendChild(testButton);
    }
});
</script>

@endsection
