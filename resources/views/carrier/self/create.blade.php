@extends('layouts.app2')

@section('conteudo')
@can('pode_registrar_carriers')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add New Carrier</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('carriers.index') }}">Carriers</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Add New</a></li>
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
                        <a href="{{ route('carriers.index') }}" class="me-3" aria-label="Back to list">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h4 class="card-title mb-0">Carrier Information</h4>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('carriers.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">User Name <span class="text-danger">*</label>
                                    <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}"
                                        placeholder="Enter carrier name"
                                        required
                                    >
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
                                    @error('company_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Carrier (obrigatórios: company_name, contact_name) --}}
                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label for="company_name" class="form-label">Company Name <span class="text-danger">*</label>
                                    <input
                                        type="text"
                                        name="company_name"
                                        id="company_name"
                                        class="form-control @error('company_name') is-invalid @enderror"
                                        value="{{ old('company_name') }}"
                                        placeholder="Enter company name"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="contact_name" class="form-label">Contact Name <span class="text-danger">*</label>
                                    <input
                                        type="text"
                                        name="contact_name"
                                        id="contact_name"
                                        class="form-control @error('contact_name') is-invalid @enderror"
                                        value="{{ old('contact_name') }}"
                                        placeholder="Enter contact person name"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="text" name="contact_phone" id="contact_phone" class="form-control" value="{{ old('contact_phone') }}" placeholder="+1 (555) 000-0000">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" name="address" id="address" class="form-control" value="{{ old('address') }}" placeholder="Street, number">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" name="city" id="city" class="form-control" value="{{ old('city') }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" name="state" id="state" class="form-control" value="{{ old('state') }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="zip" class="form-label">Zip</label>
                                    <input type="text" name="zip" id="zip" class="form-control" value="{{ old('zip') }}">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" name="country" id="country" class="form-control" value="{{ old('country', 'US') }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="mc" class="form-label">MC</label>
                                    <input type="text" name="mc" id="mc" class="form-control" value="{{ old('mc') }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="dot" class="form-label">DOT</label>
                                    <input type="text" name="dot" id="dot" class="form-control" value="{{ old('dot') }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="ein" class="form-label">EIN</label>
                                    <input type="text" name="ein" id="ein" class="form-control" value="{{ old('ein') }}">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-4 mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="url" name="website" id="website" class="form-control" value="{{ old('website') }}" placeholder="https://">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="about" class="form-label">About</label>
                                    <textarea name="about" id="about" class="form-control" rows="2" placeholder="Optional notes">{{ old('about') }}</textarea>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="trailer_capacity" class="form-label">Trailer Capacity</label>
                                    <input type="number" name="trailer_capacity" id="trailer_capacity" class="form-control" value="{{ old('trailer_capacity') }}" min="0">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_auto_hauler" id="is_auto_hauler" value="1" {{ old('is_auto_hauler') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_auto_hauler">Auto Hauler</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_towing" id="is_towing" value="1" {{ old('is_towing') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_towing">Towing</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_driveaway" id="is_driveaway" value="1" {{ old('is_driveaway') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_driveaway">Driveaway</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label for="dispatcher_company_id" class="form-label">Dispatcher</label>

                                    @if($dispatchers)
                                        {{-- Envia o ID oculto no POST --}}
                                        <input type="hidden" name="dispatcher_company_id" value="{{ $dispatchers->id }}">

                                        {{-- Mostra apenas o nome do dispatcher logado --}}
                                        <input type="text" class="form-control" value="{{ $dispatchers->user->name }}" readonly>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            No dispatcher linked to your account. Please contact an administrator.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12 d-flex justify-content-end">
                                    <a href="{{ route('carriers.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Save Carrier</button>
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
        <h5 class="modal-title" id="upgradeModalLabel">Limite do Plano Excedido</h5>
      </div>
      <div class="modal-body">
        <p>{{ $usageCheck['message'] ?? 'Você atingiu o limite do seu plano. Atualize para adicionar mais carriers.' }}</p>
      </div>
      <div class="modal-footer">
        <a href="{{ route('subscription.build-plan') }}" class="btn btn-primary">Atualizar Plano</a>
      </div>
    </div>
  </div>
</div>

@if($showUpgradeModal)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('upgradeModal'));
        modal.show();

        // Desabilita todos os campos do formulário
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
      <p>You are not authorized to add carriers.</p>
    </div>
  </div>
@endcan
@endsection
