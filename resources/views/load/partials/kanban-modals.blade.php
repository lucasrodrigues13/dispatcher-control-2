{{-- resources/views/load/partials/kanban-modals.blade.php --}}

<!-- Modal for Shipment Details (EDITÁVEL) -->
<div class="modal fade" id="shipmentDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shipmentModalTitle">Shipment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="shipmentDetailForm">
                    <input type="hidden" id="currentShipmentId">

                    <!-- Basic Information Section -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Basic Information</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary toggle-section" data-target="basicInfoSection">
                                <span class="expand-text">Collapse</span> <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                        <div class="card-body section-content" id="basicInfoSection">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Load ID:</label>
                                    <input type="text" class="form-control" id="load_id" name="load_id" readonly>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Internal Load ID:</label>
                                    <input type="text" class="form-control" id="internal_load_id" name="internal_load_id">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Creation Date:</label>
                                    <input type="date" class="form-control" id="creation_date" name="creation_date">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label for="dispatcher_id" class="form-label fw-semibold">Dispatcher</label>

                                    @if($dispatchers)
                                        {{-- Envia o ID oculto no POST --}}
                                        <input type="hidden" name="dispatcher_id" value="{{ $dispatchers->id }}">

                                        {{-- Mostra o nome do dispatcher logado --}}
                                        <input type="text" class="form-control" value="{{ $dispatchers->user->name }}" readonly>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            No dispatcher linked to your account. Please contact an administrator.
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Trip:</label>
                                    <input type="text" class="form-control" id="trip" name="trip">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Year/Make/Model:</label>
                                    <input type="text" class="form-control" id="year_make_model" name="year_make_model">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">VIN:</label>
                                    <input type="text" class="form-control" id="vin" name="vin">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Driver:</label>
                                    <select class="form-select" id="driver" name="driver">
                                        <option value="">-- Select Driver --</option>
                                        {{-- Populado via JavaScript com drivers únicos --}}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pickup Information Section -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Pickup Information</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary toggle-section" data-target="pickupInfoSection">
                                <span class="expand-text">Expand</span> <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-body section-content" id="pickupInfoSection" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Pickup Name:</label>
                                    <input type="text" class="form-control" id="pickup_name" name="pickup_name">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Pickup Address:</label>
                                    <input type="text" class="form-control" id="pickup_address" name="pickup_address">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Pickup City:</label>
                                    <input type="text" class="form-control" id="pickup_city" name="pickup_city">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Pickup State:</label>
                                    <input type="text" class="form-control" id="pickup_state" name="pickup_state">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Pickup ZIP:</label>
                                    <input type="text" class="form-control" id="pickup_zip" name="pickup_zip">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Scheduled Date:</label>
                                    <input type="date" class="form-control" id="scheduled_pickup_date" name="scheduled_pickup_date">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Actual Date:</label>
                                    <input type="date" class="form-control" id="actual_pickup_date" name="actual_pickup_date">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Phone:</label>
                                    <input type="text" class="form-control" id="pickup_phone" name="pickup_phone">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Mobile:</label>
                                    <input type="text" class="form-control" id="pickup_mobile" name="pickup_mobile">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <label class="form-label fw-bold">Notes:</label>
                                    <textarea class="form-control" id="pickup_notes" name="pickup_notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Information Section -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Delivery Information</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary toggle-section" data-target="deliveryInfoSection">
                                <span class="expand-text">Expand</span> <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-body section-content" id="deliveryInfoSection" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Delivery Name:</label>
                                    <input type="text" class="form-control" id="delivery_name" name="delivery_name">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Delivery Address:</label>
                                    <input type="text" class="form-control" id="delivery_address" name="delivery_address">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Delivery City:</label>
                                    <input type="text" class="form-control" id="delivery_city" name="delivery_city">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Delivery State:</label>
                                    <input type="text" class="form-control" id="delivery_state" name="delivery_state">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Delivery ZIP:</label>
                                    <input type="text" class="form-control" id="delivery_zip" name="delivery_zip">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Scheduled Date:</label>
                                    <input type="date" class="form-control" id="scheduled_delivery_date" name="scheduled_delivery_date">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Actual Date:</label>
                                    <input type="date" class="form-control" id="actual_delivery_date" name="actual_delivery_date">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Phone:</label>
                                    <input type="text" class="form-control" id="delivery_phone" name="delivery_phone">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Mobile:</label>
                                    <input type="text" class="form-control" id="delivery_mobile" name="delivery_mobile">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <label class="form-label fw-bold">Notes:</label>
                                    <textarea class="form-control" id="delivery_notes" name="delivery_notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information Section -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Financial Information</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary toggle-section" data-target="financialInfoSection">
                                <span class="expand-text">Expand</span> <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-body section-content" id="financialInfoSection" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Price:</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Expenses:</label>
                                    <input type="number" step="0.01" class="form-control" id="expenses" name="expenses">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Driver Pay:</label>
                                    <input type="number" step="0.01" class="form-control" id="driver_pay" name="driver_pay">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Broker Fee:</label>
                                    <input type="number" step="0.01" class="form-control" id="broker_fee" name="broker_fee">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Paid Amount:</label>
                                    <input type="number" step="0.01" class="form-control" id="paid_amount" name="paid_amount">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Payment Method:</label>
                                    <input type="text" class="form-control" id="payment_method" name="payment_method">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Paid Method:</label>
                                    <input type="text" class="form-control" id="paid_method" name="paid_method">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Payment Terms:</label>
                                    <input type="text" class="form-control" id="payment_terms" name="payment_terms">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label fw-bold">Payment Status:</label>
                                    <input type="text" class="form-control" id="payment_status" name="payment_status">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveShipmentBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Configuração de Campos do Card -->
<div class="modal fade" id="cardFieldsConfigModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configure Card Fields</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Select which fields should be visible on the Kanban cards:</p>

                <!-- Basic Information -->
                <h6 class="text-primary mt-3 mb-2"><i class="fas fa-info-circle me-1"></i>Basic Information</h6>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_load_id" checked>
                            <label class="form-check-label" for="config_load_id">Load ID</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_internal_load_id">
                            <label class="form-check-label" for="config_internal_load_id">Internal Load ID</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_dispatcher" checked>
                            <label class="form-check-label" for="config_dispatcher">Dispatcher</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_trip">
                            <label class="form-check-label" for="config_trip">Trip</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_creation_date">
                            <label class="form-check-label" for="config_creation_date">Creation Date</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_driver">
                            <label class="form-check-label" for="config_driver">Driver</label>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Information -->
                <h6 class="text-primary mt-3 mb-2"><i class="fas fa-car me-1"></i>Vehicle Information</h6>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_year_make_model">
                            <label class="form-check-label" for="config_year_make_model">Year/Make/Model</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_vin">
                            <label class="form-check-label" for="config_vin">VIN</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_lot_number">
                            <label class="form-check-label" for="config_lot_number">Lot Number</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_buyer_number">
                            <label class="form-check-label" for="config_buyer_number">Buyer Number</label>
                        </div>
                    </div>
                </div>

                <!-- Address Information (Combined) -->
                <h6 class="text-primary mt-3 mb-2"><i class="fas fa-map-marker-alt me-1"></i>Address Information</h6>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_addresses" checked>
                            <label class="form-check-label" for="config_addresses">Pickup & Delivery Addresses</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_pickup_city" checked>
                            <label class="form-check-label" for="config_pickup_city">Pickup City</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_delivery_city" checked>
                            <label class="form-check-label" for="config_delivery_city">Delivery City</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_pickup_state">
                            <label class="form-check-label" for="config_pickup_state">Pickup State</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_delivery_state">
                            <label class="form-check-label" for="config_delivery_state">Delivery State</label>
                        </div>
                    </div>
                </div>

                <!-- Date Information -->
                <h6 class="text-primary mt-3 mb-2"><i class="fas fa-calendar me-1"></i>Date Information</h6>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_scheduled_pickup_date" checked>
                            <label class="form-check-label" for="config_scheduled_pickup_date">Scheduled Pickup Date</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_actual_pickup_date">
                            <label class="form-check-label" for="config_actual_pickup_date">Actual Pickup Date</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_scheduled_delivery_date">
                            <label class="form-check-label" for="config_scheduled_delivery_date">Scheduled Delivery Date</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_actual_delivery_date">
                            <label class="form-check-label" for="config_actual_delivery_date">Actual Delivery Date</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_receipt_date">
                            <label class="form-check-label" for="config_receipt_date">Receipt Date</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_invoice_date">
                            <label class="form-check-label" for="config_invoice_date">Invoice Date</label>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <h6 class="text-primary mt-3 mb-2"><i class="fas fa-dollar-sign me-1"></i>Financial Information</h6>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_price">
                            <label class="form-check-label" for="config_price">Price</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_driver_pay">
                            <label class="form-check-label" for="config_driver_pay">Driver Pay</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_paid_amount">
                            <label class="form-check-label" for="config_paid_amount">Paid Amount</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_payment_status">
                            <label class="form-check-label" for="config_payment_status">Payment Status</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_broker_fee">
                            <label class="form-check-label" for="config_broker_fee">Broker Fee</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_expenses">
                            <label class="form-check-label" for="config_expenses">Expenses</label>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <h6 class="text-primary mt-3 mb-2"><i class="fas fa-info me-1"></i>Additional Information</h6>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_reference_number">
                            <label class="form-check-label" for="config_reference_number">Reference Number</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_invoice_number">
                            <label class="form-check-label" for="config_invoice_number">Invoice Number</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_has_terminal">
                            <label class="form-check-label" for="config_has_terminal">Has Terminal</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_shipper_name">
                            <label class="form-check-label" for="config_shipper_name">Shipper Name</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveCardConfigBtn">Save Configuration</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Apply Filter (ATUALIZADO) -->
<div class="modal fade" id="applyFilter" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Apply Filter</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('loads.mode') }}" method="GET" class="mb-4">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Load ID</label>
                            <input type="text" name="load_id" class="form-control" placeholder="Load ID" value="{{ request('load_id') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Internal Load ID</label>
                            <input type="text" name="internal_load_id" class="form-control" placeholder="Internal Load ID" value="{{ request('internal_load_id') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="dispatcher_id" class="form-label fw-semibold">Dispatcher</label>

                            @if($dispatchers)
                                {{-- Envia o ID oculto no POST --}}
                                <input type="hidden" name="dispatcher_id" value="{{ $dispatchers->id }}">

                                {{-- Mostra o nome do dispatcher logado --}}
                                <input type="text" class="form-control" value="{{ $dispatchers->user->name }}" readonly>
                            @else
                                <div class="alert alert-warning mb-0">
                                    No dispatcher linked to your account. Please contact an administrator.
                                </div>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Carrier (Select)</label>
                            <select name="carrier_id" class="form-select">
                                <option value="">-- Select Carrier --</option>
                                @foreach($carriers as $carrier)
                                    <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                        {{ $carrier->user ? $carrier->user->name : $carrier->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Carrier (Name)</label>
                            <input type="text" name="carrier" class="form-control" placeholder="Carrier Name" value="{{ request('carrier') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">VIN</label>
                            <input type="text" name="vin" class="form-control" placeholder="VIN" value="{{ request('vin') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pickup City</label>
                            <input type="text" name="pickup_city" class="form-control" placeholder="Pickup City" value="{{ request('pickup_city') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Delivery City</label>
                            <input type="text" name="delivery_city" class="form-control" placeholder="Delivery City" value="{{ request('delivery_city') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Scheduled Pickup Date</label>
                            <input type="date" name="scheduled_pickup_date" class="form-control" value="{{ request('scheduled_pickup_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Driver</label>
                            <input type="text" name="driver" class="form-control" placeholder="Driver Name" value="{{ request('driver') }}">
                        </div>
                        <div class="col-md-12 d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
                            <a href="{{ route('loads.mode') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Importação (mantido igual) -->
<div class="modal fade" id="importLoadsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Import Excel Loads</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('loads.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="dispatcher_id" class="form-label fw-semibold">Dispatcher</label>

                            @if($dispatchers)
                                {{-- Envia o ID oculto no POST --}}
                                <input type="hidden" name="dispatcher_id" value="{{ $dispatchers->id }}">

                                {{-- Mostra o nome do dispatcher logado --}}
                                <input type="text" class="form-control" value="{{ $dispatchers->user->name }}" readonly>
                            @else
                                <div class="alert alert-warning mb-0">
                                    No dispatcher linked to your account. Please contact an administrator.
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="employee_id" class="form-label fw-semibold">Add Employee</label>
                            <select name="employee_id" id="employee_id" class="form-select">
                                <option value="" selected>Select Employee</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-4">
                            <label for="carrier_id" class="form-label fw-semibold">Carrier</label>
                            <select name="carrier_id" class="form-select" required>
                                <option value="" disabled selected>Select Carrier</option>
                                @foreach($carriers as $item)
                                    <option value="{{ $item->id }}">{{ $item->user ? $item->user->name : ($item->name ?? 'User #' . $item->id) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="arquivo" class="form-label fw-semibold">Select Excel archive</label>
                        <input class="form-control form-control-lg" type="file" id="arquivo" name="arquivo" accept=".xls,.xlsx" required>
                        <div class="form-text">Allow Formats: .xlsx, .xls</div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('loads.index') }}" class="btn btn-outline-secondary">See Registers</a>
                        <a href="{{ route('loads.create') }}" class="btn btn-outline-primary">Manual Register</a>
                        <button type="submit" class="btn btn-success">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Search (mantido igual) -->
<!-- Modal Apply Filter -->
<div class="modal fade" id="applyFilter" tabindex="-1" aria-labelledby="applyFilterLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="applyFilterLabel">Apply Filter</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('loads.mode') }}" method="GET" class="mb-4">
          <div class="row g-2">
            <div class="col-md-3">
              <input type="text" name="load_id" class="form-control" placeholder="Load ID" value="{{ request('load_id') }}">
            </div>
            <div class="col-md-3">
              <input type="text" name="internal_load_id" class="form-control" placeholder="Internal Load ID" value="{{ request('internal_load_id') }}">
            </div>

            <!-- Select Dispatcher -->
            <div class="col-md-3 mb-4">
              <select name="dispatcher_id" class="form-select">
                <option value="">Filter Dispatcher</option>
                @if($dispatchers)
                    <option value="{{ $dispatchers->id }}" {{ request('dispatcher_id') == $dispatchers->id ? 'selected' : '' }}>{{ $dispatchers->user->name }}</option>
                @endif
              </select>
            </div>

            <div class="col-md-3 mb-4">
              <select name="carrier_id" class="form-select">
                <option value="">Filter Carrier</option>
                @foreach($carriers as $item)
                  <option value="{{ $item->id }}" {{ request('carrier_id') == $item->id ? 'selected' : '' }}>
                    {{ $item->company_name }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Select Employee -->
            <div class="col-md-3 mb-4">
              <select name="employee_id" class="form-select">
                <option value="">Filter Employee</option>
                @foreach($employees as $item)
                  <option value="{{ $item->id }}" {{ request('employee_id') == $item->id ? 'selected' : '' }}>
                    {{ $item->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3">
              <input type="text" name="vin" class="form-control" placeholder="VIN" value="{{ request('vin') }}">
            </div>
            <div class="col-md-3">
              <input type="text" name="pickup_city" class="form-control" placeholder="Pickup City" value="{{ request('pickup_city') }}">
            </div>
            <div class="col-md-3">
              <input type="text" name="delivery_city" class="form-control" placeholder="Delivery City" value="{{ request('delivery_city') }}">
            </div>
            <div class="col-md-3">
              <input type="date" name="scheduled_pickup_date" class="form-control" placeholder="Scheduled Pickup Date" value="{{ request('scheduled_pickup_date') }}">
            </div>
            <div class="col-md-3">
              <input type="text" name="driver" class="form-control" placeholder="Driver" value="{{ request('driver') }}">
            </div>
            <div class="col-md-12 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">Filter</button>
              <a href="{{ route('loads.mode') }}" class="btn btn-secondary ms-2">Clear</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Show/Hide Columns (mantido igual) -->
<div class="modal fade" id="selectColums" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Show/Hide Columns</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="searchColumnsInput" class="form-control" placeholder="Search columns...">
                </div>
                <div class="col-12">
                    <label>
                        <input type="checkbox" id="toggle-all-columns">
                        Show/Hide All Columns
                    </label>
                </div>
                <hr>
                <div class="row" style="max-height: 300px; overflow: auto;">
                    <div class="col-md-6 mb-2"><label><input type="checkbox" class="toggle-column" data-column="Load Id" checked> Load Id</label></div>
                    <!-- Adicione outros campos conforme necessário -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
