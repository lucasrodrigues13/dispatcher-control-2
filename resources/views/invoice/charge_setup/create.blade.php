@extends("layouts.app2")

@section('conteudo')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add New Charge Setup</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="#">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="#">Charge Setup</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="#">Add New</a>
                </li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                   {{-- Form para Adicionar --}}
                    <form id="filter-form" method="POST" action="{{ route('charges_setups.store') }}" class="mb-4">
                        @csrf

                        {{-- Filtros de data --}}
                        <div class="row mb-3 border p-3 rounded">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Carrier <span class="text-danger">*</span></label>
                                <select id="carrier-select" name="carrier_id" class="form-select" required>
                                    <option value="" disabled selected>Select Carrier</option>
                                    @foreach ($carriers as $carrier)
                                        <option value="{{ $carrier->id }}" @selected(old('carrier_id') == $carrier->id)>
                                            {{ $carrier->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount Type</label>
                                <select name="amount_type" class="form-select" required>
                                    <option value="" disabled {{ !old('amount_type') ? 'selected' : '' }}>Select...</option>
                                    <option value="price" @selected(old('amount_type') === 'price')>Price</option>
                                    <option value="paid amount" @selected(old('amount_type') === 'paid amount')>Paid Amount</option>
                                </select>
                            </div>
                        </div>

                        {{-- Checkboxes --}}
                        <div class="row mb-3 border p-3 rounded bg-light">
                            @foreach ([
                                'actual_delivery_date' => 'Actual Delivery Date',
                                'actual_pickup_date' => 'Actual Pickup Date',
                                'creation_date' => 'Creation Date',
                                'invoice_date' => 'Invoice Date',
                                'receipt_date' => 'Receipt Date',
                                'scheduled_pickup_date' => 'Scheduled Pickup Date',
                                'scheduled_delivery_date' => 'Scheduled Delivery Date'
                            ] as $field => $label)
                                <div class="col-md-3 col-6 mb-2">
                                    <input type="checkbox" id="filter_{{ $field }}" name="filters[{{ $field }}]" value="1"
                                        @checked(old("filters.$field"))>
                                    <label for="filter_{{ $field }}" class="ms-1">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-md-6 mb-3">
                                <label class="form-label">Dispatcher<span class="text-danger">*</span></label>
                                <select id="carrier-select" name="dispatcher_id" class="form-select" required>
                                    <option value="" disabled selected>Select Dispatcher</option>
                                    @foreach ($dispatchers as $item)
                                        <option value="{{ $item->id }}" @selected(old('dispatcher_id') == $item->id)>
                                            {{ $item->user ? $item->user->name : ($item->name ?? 'User #' . $item->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        <div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Save</button>
                            </div>
                        </div>
                    </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
