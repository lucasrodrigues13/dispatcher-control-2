{{-- resources/views/loads/edit.blade.php --}}
@extends("layouts.app2")

@section('conteudo')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Edit Load</h3>
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
                    Edit
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

                        <form method="POST" action="{{ route('loads.update', $load->id) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="source" value="{{ $source ?? 'lista' }}">

                            {{-- ====================================== --}}
                            {{-- IDENTIFICAÇÃO / BÁSICO --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Identificação Básica</h5>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                <label for="dispatcher_id" class="form-label">Dispatcher (Company)</label>
                                <select name="dispatcher_id" id="dispatcher_id" class="form-select" required>
                                    <option value="" disabled>Select Dispatcher</option>
                                    @foreach($dispatchers as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('dispatcher_id', $load->dispatcher_id ?? '') == $item->id ? 'selected' : '' }}>
                                        {{ $item->user ? $item->user->name : ($item->name ?? 'User #' . $item->id) }}
                                    </option>
                                    @endforeach
                                </select>
                                </div>

                                <div class="col-md-6 mb-4">
                                <label for="carrier_id" class="form-label">Carrier (Customer)</label>
                                <select name="carrier_id" id="carrier_id" class="form-select" required>
                                    <option value="" disabled>Select Carrier</option>
                                    @foreach($carriers as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('carrier_id', $load->carrier_id ?? '') == $item->id ? 'selected' : '' }}>
                                        {{ $item->user ? $item->user->name : ($item->name ?? 'User #' . $item->id) }}
                                    </option>
                                    @endforeach
                                </select>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-3">
                                    <label for="load_id" class="form-label">Load ID <span class="text-danger">*</span></label>
                                    <input type="text" name="load_id" id="load_id" class="form-control"
                                        value="{{ old('load_id', $load->load_id) }}" required>
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label for="internal_load_id" class="form-label">Internal Load ID</label>
                                    <input type="text" name="internal_load_id" id="internal_load_id" class="form-control"
                                        value="{{ old('internal_load_id', $load->internal_load_id) }}">
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label for="creation_date" class="form-label">Creation Date</label>
                                    <input type="date" name="creation_date" id="creation_date" class="form-control"
                                        value="{{ old('creation_date', $load->creation_date ? $load->creation_date->format('Y-m-d') : '') }}">
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label for="dispatcher" class="form-label">Dispatcher</label>
                                    <input type="text" name="dispatcher" id="dispatcher" class="form-control"
                                        value="{{ old('dispatcher', $load->dispatcher) }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="mb-3 col-md-3">
                                    <label for="trip" class="form-label">Trip</label>
                                    <input type="text" name="trip" id="trip" class="form-control"
                                        value="{{ old('trip', $load->trip) }}">
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label for="year_make_model" class="form-label">Year, Make, Model</label>
                                    <input type="text" name="year_make_model" id="year_make_model" class="form-control"
                                        value="{{ old('year_make_model', $load->year_make_model) }}">
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label for="vin" class="form-label">VIN</label>
                                    <input type="text" name="vin" id="vin" class="form-control"
                                        value="{{ old('vin', $load->vin) }}">
                                </div>

                                <div class="mb-3 col-md-2">
                                    <label for="lot_number" class="form-label">Lot Number</label>
                                    <input type="text" name="lot_number" id="lot_number" class="form-control"
                                        value="{{ old('lot_number', $load->lot_number) }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-md-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="has_terminal" id="has_terminal" class="form-check-input" value="1"
                                            {{ old('has_terminal', $load->has_terminal) ? 'checked' : '' }}>
                                        <label for="has_terminal" class="form-check-label">Has Terminal</label>
                                    </div>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="dispatched_to_carrier" class="form-label">Dispatched to Carrier</label>
                                    <input type="text" name="dispatched_to_carrier" id="dispatched_to_carrier" class="form-control"
                                        value="{{ old('dispatched_to_carrier', $load->dispatched_to_carrier) }}">
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES DE PICKUP --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Informações de Pickup</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-6">
                                    <label for="pickup_name" class="form-label">Pickup Name</label>
                                    <input type="text" name="pickup_name" id="pickup_name" class="form-control"
                                        value="{{ old('pickup_name', $load->pickup_name) }}">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="pickup_address" class="form-label">Pickup Address</label>
                                    <input type="text" name="pickup_address" id="pickup_address" class="form-control"
                                        value="{{ old('pickup_address', $load->pickup_address) }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="mb-3 col-md-3">
                                    <label for="pickup_city" class="form-label">Pickup City</label>
                                    <input type="text" name="pickup_city" id="pickup_city" class="form-control"
                                        value="{{ old('pickup_city', $load->pickup_city) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="pickup_state" class="form-label">Pickup State</label>
                                    <input type="text" name="pickup_state" id="pickup_state" class="form-control"
                                        value="{{ old('pickup_state', $load->pickup_state) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="pickup_zip" class="form-label">Pickup ZIP</label>
                                    <input type="text" name="pickup_zip" id="pickup_zip" class="form-control"
                                        value="{{ old('pickup_zip', $load->pickup_zip) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="scheduled_pickup_date" class="form-label">Scheduled Pickup Date</label>
                                    <input type="date" name="scheduled_pickup_date" id="scheduled_pickup_date" class="form-control"
                                        value="{{ old('scheduled_pickup_date', $load->scheduled_pickup_date ? $load->scheduled_pickup_date->format('Y-m-d') : '') }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-md-3">
                                    <label for="pickup_phone" class="form-label">Pickup Phone</label>
                                    <input type="text" name="pickup_phone" id="pickup_phone" class="form-control"
                                        value="{{ old('pickup_phone', $load->pickup_phone) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="pickup_mobile" class="form-label">Pickup Mobile</label>
                                    <input type="text" name="pickup_mobile" id="pickup_mobile" class="form-control"
                                        value="{{ old('pickup_mobile', $load->pickup_mobile) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="actual_pickup_date" class="form-label">Actual Pickup Date</label>
                                    <input type="date" name="actual_pickup_date" id="actual_pickup_date" class="form-control"
                                        value="{{ old('actual_pickup_date', $load->actual_pickup_date ? $load->actual_pickup_date->format('Y-m-d') : '') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="buyer_number" class="form-label">Buyer Number</label>
                                    <input type="number" name="buyer_number" id="buyer_number" class="form-control"
                                        value="{{ old('buyer_number', $load->buyer_number) }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-12">
                                    <label for="pickup_notes" class="form-label">Pickup Notes</label>
                                    <textarea name="pickup_notes" id="pickup_notes" class="form-control" rows="2">{{ old('pickup_notes', $load->pickup_notes) }}</textarea>
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES DE DELIVERY --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Informações de Delivery</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-6">
                                    <label for="delivery_name" class="form-label">Delivery Name</label>
                                    <input type="text" name="delivery_name" id="delivery_name" class="form-control"
                                        value="{{ old('delivery_name', $load->delivery_name) }}">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="delivery_address" class="form-label">Delivery Address</label>
                                    <input type="text" name="delivery_address" id="delivery_address" class="form-control"
                                        value="{{ old('delivery_address', $load->delivery_address) }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_city" class="form-label">Delivery City</label>
                                    <input type="text" name="delivery_city" id="delivery_city" class="form-control"
                                        value="{{ old('delivery_city', $load->delivery_city) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_state" class="form-label">Delivery State</label>
                                    <input type="text" name="delivery_state" id="delivery_state" class="form-control"
                                        value="{{ old('delivery_state', $load->delivery_state) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_zip" class="form-label">Delivery ZIP</label>
                                    <input type="text" name="delivery_zip" id="delivery_zip" class="form-control"
                                        value="{{ old('delivery_zip', $load->delivery_zip) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="scheduled_delivery_date" class="form-label">Scheduled Delivery Date</label>
                                    <input type="date" name="scheduled_delivery_date" id="scheduled_delivery_date" class="form-control"
                                        value="{{ old('scheduled_delivery_date', $load->scheduled_delivery_date ? $load->scheduled_delivery_date->format('Y-m-d') : '') }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-md-3">
                                    <label for="actual_delivery_date" class="form-label">Actual Delivery Date</label>
                                    <input type="date" name="actual_delivery_date" id="actual_delivery_date" class="form-control"
                                        value="{{ old('actual_delivery_date', $load->actual_delivery_date ? $load->actual_delivery_date->format('Y-m-d') : '') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_phone" class="form-label">Delivery Phone</label>
                                    <input type="text" name="delivery_phone" id="delivery_phone" class="form-control"
                                        value="{{ old('delivery_phone', $load->delivery_phone) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_mobile" class="form-label">Delivery Mobile</label>
                                    <input type="text" name="delivery_mobile" id="delivery_mobile" class="form-control"
                                        value="{{ old('delivery_mobile', $load->delivery_mobile) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="delivery_notes" class="form-label">Delivery Notes</label>
                                    <textarea name="delivery_notes" id="delivery_notes" class="form-control" rows="2">{{ old('delivery_notes', $load->delivery_notes) }}</textarea>
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES DE SHIPPER --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Informações do Shipper</h5>
                            <div class="row mb-5">
                                <div class="mb-3 col-md-6">
                                    <label for="shipper_name" class="form-label">Shipper Name</label>
                                    <input type="text" name="shipper_name" id="shipper_name" class="form-control"
                                        value="{{ old('shipper_name', $load->shipper_name) }}">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="shipper_phone" class="form-label">Shipper Phone</label>
                                    <input type="text" name="shipper_phone" id="shipper_phone" class="form-control"
                                        value="{{ old('shipper_phone', $load->shipper_phone) }}">
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES FINANCEIRAS --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Informações Financeiras</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-3">
                                    <label for="price" class="form-label">Price ($)</label>
                                    <input type="number" step="0.01" name="price" id="price" class="form-control"
                                        value="{{ old('price', $load->price) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="expenses" class="form-label">Expenses ($)</label>
                                    <input type="number" step="0.01" name="expenses" id="expenses" class="form-control"
                                        value="{{ old('expenses', $load->expenses) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="broker_fee" class="form-label">Broker Fee ($)</label>
                                    <input type="number" step="0.01" name="broker_fee" id="broker_fee" class="form-control"
                                        value="{{ old('broker_fee', $load->broker_fee) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="driver_pay" class="form-label">Driver Pay ($)</label>
                                    <input type="number" step="0.01" name="driver_pay" id="driver_pay" class="form-control"
                                        value="{{ old('driver_pay', $load->driver_pay) }}">
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES DE PAGAMENTO --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Informações de Pagamento</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-3">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <input type="text" name="payment_method" id="payment_method" class="form-control"
                                        value="{{ old('payment_method', $load->payment_method) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="paid_amount" class="form-label">Paid Amount ($)</label>
                                    <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control"
                                        value="{{ old('paid_amount', $load->paid_amount) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="paid_method" class="form-label">Paid Method</label>
                                    <input type="text" name="paid_method" id="paid_method" class="form-control"
                                        value="{{ old('paid_method', $load->paid_method) }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" name="reference_number" id="reference_number" class="form-control"
                                        value="{{ old('reference_number', $load->reference_number) }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-md-3">
                                    <label for="receipt_date" class="form-label">Receipt Date</label>
                                    <input type="date" name="receipt_date" id="receipt_date" class="form-control"
                                        value="{{ old('receipt_date', $load->receipt_date ? $load->receipt_date->format('Y-m-d') : '') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="payment_terms" class="form-label">Payment Terms</label>
                                    <input type="text" name="payment_terms" id="payment_terms" class="form-control"
                                        value="{{ old('payment_terms', $load->payment_terms) }}">
                                </div>
                                <div class="mb-3 col-6">
                                    <label for="payment_notes" class="form-label">Payment Notes</label>
                                    <textarea name="payment_notes" id="payment_notes" class="form-control" rows="1">{{ old('payment_notes', $load->payment_notes) }}</textarea>
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- INFORMAÇÕES DE INVOICE --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Informações de Invoice</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-4">
                                    <label for="payment_status" class="form-label">Payment Status</label>
                                    <input type="text" name="payment_status" id="payment_status" class="form-control"
                                        value="{{ old('payment_status', $load->payment_status) }}">
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label for="invoice_number" class="form-label">Invoice Number</label>
                                    <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                                        value="{{ old('invoice_number', $load->invoice_number) }}">
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label for="invoice_date" class="form-label">Invoice Date</label>
                                    <input type="date" name="invoice_date" id="invoice_date" class="form-control"
                                        value="{{ old('invoice_date', $load->invoice_date ? $load->invoice_date->format('Y-m-d') : '') }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="mb-3 col-12">
                                    <label for="invoice_notes" class="form-label">Invoice Notes</label>
                                    <textarea name="invoice_notes" id="invoice_notes" class="form-control" rows="2">{{ old('invoice_notes', $load->invoice_notes) }}</textarea>
                                </div>
                            </div>

                            {{-- ====================================== --}}
                            {{-- OUTROS --}}
                            {{-- ====================================== --}}
                            <h5 class="mb-3">Outros</h5>
                            <div class="row mb-4">
                                <div class="mb-3 col-md-4">
                                    <label for="driver" class="form-label">Driver</label>
                                    <input type="text" name="driver" id="driver" class="form-control"
                                        value="{{ old('driver', $load->driver) }}">
                                </div>
                            </div>

                            {{-- BOTÕES --}}
                            <div class="d-flex justify-content-end">
                                @if(isset($source) && $source === 'kanban')
                                    <a href="{{ route('loads.mode') }}" class="btn btn-secondary me-2">Cancel</a>
                                @else
                                    <a href="{{ route('loads.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                @endif
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
