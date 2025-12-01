@extends("layouts.app2")

@section('conteudo')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add New Broker</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('brokers.index') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ route('brokers.index') }}">Brokers</a>
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
                            <a href="{{ route('brokers.index') }}"><i class="fas fa-arrow-left"></i></a>
                        </div>
                        <h4 class="card-title ms-2 mb-0">Broker Information</h4>
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

                        <form method="POST" action="{{ route('brokers.store') }}">
                            @csrf


                            <div class="row">
                                {{-- Campos do usuário --}}
                                <div class="mb-3 col-md-6">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter name" value="{{ old('name') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" value="{{ old('email') }}" required>

                                        @error('email')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Campos do broker --}}
                                <div class="mb-3 col-md-6">
                                    <label for="license_number" class="form-label">License Number <span class="text-danger">*</label>
                                    <input type="text" name="license_number" id="license_number" class="form-control" placeholder="Enter license number" value="{{ old('license_number') }}" required>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="company_name" class="form-label">Company Name <span class="text-danger">*<span class="text-danger">*</label>
                                    <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Enter company name" value="{{ old('company_name') }}" required>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter phone" value="{{ old('phone') }}">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" name="address" id="address" class="form-control" placeholder="Enter address" value="{{ old('address') }}">
                                </div>

                                <div class="mb-3 col-12">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Additional notes">{{ old('notes') }}</textarea>
                                </div>

                                {{-- Novos campos adicionados --}}
                                <div class="mb-3 col-md-6">
                                    <label for="accounting_email" class="form-label">Accounting Email</label>
                                    <input type="email" name="accounting_email" id="accounting_email" class="form-control" placeholder="Enter accounting email" value="{{ old('accounting_email') }}">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="accounting_phone_number" class="form-label">Accounting Phone</label>
                                    <input type="text" name="accounting_phone_number" id="accounting_phone_number" class="form-control" placeholder="Enter accounting phone" value="{{ old('accounting_phone_number') }}">
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label for="fee_percent" class="form-label">Fee Percent (%)</label>
                                    <input type="number" step="0.01" name="fee_percent" id="fee_percent" class="form-control" placeholder="e.g. 5.00" value="{{ old('fee_percent') }}">
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select name="payment_method" id="payment_method" class="form-select">
                                        <option value="">Select payment method</option>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                                        <option value="cashers_check" {{ old('payment_method') == 'cashers_check' ? 'selected' : '' }}>Cashers Check</option>
                                        <option value="money_order" {{ old('payment_method') == 'money_order' ? 'selected' : '' }}>Money Order</option>
                                        <option value="comchek" {{ old('payment_method') == 'comchek' ? 'selected' : '' }}>Comchek</option>
                                        <option value="ach" {{ old('payment_method') == 'ach' ? 'selected' : '' }}>ACH</option>
                                        <option value="direct_deposit" {{ old('payment_method') == 'direct_deposit' ? 'selected' : '' }}>Direct Deposit</option>
                                        <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                        <option value="venmo" {{ old('payment_method') == 'venmo' ? 'selected' : '' }}>Venmo</option>
                                        <option value="cashapp" {{ old('payment_method') == 'cashapp' ? 'selected' : '' }}>CashApp</option>
                                        <option value="uship" {{ old('payment_method') == 'uship' ? 'selected' : '' }}>uShip</option>
                                        <option value="zelle" {{ old('payment_method') == 'zelle' ? 'selected' : '' }}>Zelle</option>
                                        <option value="factoring" {{ old('payment_method') == 'factoring' ? 'selected' : '' }}>Factoring</option>
                                        <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label for="payment_terms" class="form-label">Payment Terms</label>
                                    <select name="payment_terms" id="payment_terms" class="form-select">
                                        <option value="">Select Payment Terms</option>
                                        <option value="check_on_delivery" {{ old('payment_terms') == 'check_on_delivery' ? 'selected' : '' }}>Check on Delivery</option>
                                        <option value="check_on_pickup" {{ old('payment_terms') == 'check_on_pickup' ? 'selected' : '' }}>Check on Pickup</option>
                                        <option value="2_business_days" {{ old('payment_terms') == '2_business_days' ? 'selected' : '' }}>2 Business Days</option>
                                        <option value="5_business_days" {{ old('payment_terms') == '5_business_days' ? 'selected' : '' }}>5 Business Days</option>
                                        <option value="7_business_days" {{ old('payment_terms') == '7_business_days' ? 'selected' : '' }}>7 Business Days</option>
                                        <option value="10_business_days" {{ old('payment_terms') == '10_business_days' ? 'selected' : '' }}>10 Business Days</option>
                                        <option value="15_business_days" {{ old('payment_terms') == '15_business_days' ? 'selected' : '' }}>15 Business Days</option>
                                        <option value="20_business_days" {{ old('payment_terms') == '20_business_days' ? 'selected' : '' }}>20 Business Days</option>
                                        <option value="30_business_days" {{ old('payment_terms') == '30_business_days' ? 'selected' : '' }}>30 Business Days</option>
                                        <option value="45_business_days" {{ old('payment_terms') == '45_business_days' ? 'selected' : '' }}>45 Business Days</option>
                                        <option value="60_business_days" {{ old('payment_terms') == '60_business_days' ? 'selected' : '' }}>60 Business Days</option>
                                    </select>
                                </div>



                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('brokers.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
