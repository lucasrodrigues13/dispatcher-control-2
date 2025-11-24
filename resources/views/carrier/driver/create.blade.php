@extends("layouts.app2")

@section('conteudo')
@can('pode_registrar_drivers')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add New Driver</h3>
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
                    <a href="{{ route('drivers.index') }}">Drivers</a>
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

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li><i class="fas fa-exclamation-circle me-1"></i>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <a href="{{ route('drivers.index') }}" class="me-3" aria-label="Back to list">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h4 class="card-title mb-0">Driver Information</h4>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('drivers.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- ⭐ NOVO: Campo de seleção de owner para admins --}}
                            @if(auth()->user()->isAdmin() && isset($owners) && $owners->count() > 0)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="owner_id">Owner <span class="text-danger">*</span></label>
                                        <select name="owner_id" id="owner_id" class="form-control" required>
                                            <option value="" selected disabled>Select Owner</option>
                                            @foreach($owners as $owner)
                                                <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                                    {{ $owner->name }} ({{ $owner->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('owner_id')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Driver Name <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}"
                                        placeholder="Enter driver name"
                                        required
                                    >
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}"
                                        placeholder="email@example.com"
                                        required
                                    >
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Driver Information --}}
                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="phone"
                                        id="phone"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone') }}"
                                        placeholder="+1 (555) 000-0000"
                                        required
                                    >
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="ssn_tax_id" class="form-label">SSN/Tax ID <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="ssn_tax_id"
                                        id="ssn_tax_id"
                                        class="form-control @error('ssn_tax_id') is-invalid @enderror"
                                        value="{{ old('ssn_tax_id') }}"
                                        placeholder="Enter SSN or Tax ID"
                                        required
                                    >
                                    @error('ssn_tax_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label for="carrier_id" class="form-label">Carrier <span class="text-danger">*</span></label>
                                    <select name="carrier_id" id="carrier_id" class="form-control @error('carrier_id') is-invalid @enderror" required>
                                        <option value="">Select a carrier</option>
                                        @foreach($carriers as $carrier)
                                            <option value="{{ $carrier->id }}" {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                                {{ $carrier->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('carrier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12 d-flex justify-content-end">
                                    <a href="{{ route('drivers.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Save Driver</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal de upgrade de plano -->
<div class="modal fade" id="upgradeModal" tabindex="-1" aria-labelledby="upgradeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="upgradeModalLabel">Plan Limit Exceeded</h5>
      </div>
      <div class="modal-body">
        <p>{{ $usageCheck['message'] ?? 'You have reached the limit of your current plan. Please upgrade to add more carriers.' }}</p>
      </div>
      <div class="modal-footer">
        <a href="{{ route('subscription.build-plan') }}" class="btn btn-primary">Upgrade Plan</a>
      </div>
    </div>
  </div>
</div>

@if($showUpgradeModal)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('upgradeModal'));
        modal.show();

        // Disable all form fields and submit buttons
        document.querySelectorAll('form input, form select, form textarea, form button[type="submit"]').forEach(function(el) {
            el.disabled = true;
        });
    });
</script>
@endif

@else
  <div class="container py-5">
    <div class="alert alert-warning text-center">
      <h4>Without permission</h4>
      <p>You are not authorized to add drivers.</p>
    </div>
  </div>
@endcan
@endsection
