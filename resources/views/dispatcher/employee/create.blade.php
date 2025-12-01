@extends("layouts.app2")

@section('conteudo')
@can('pode_registrar_employees')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add New Employee</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Employee</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Add New</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="seta-voltar">
                            <a href="{{ route('employees.index') }}"><i class="fas fa-arrow-left"></i></a>
                        </div>
                        <h4 class="card-title ms-2">Employee Information</h4>
                    </div>

                    <div class="card-body">
                       <form method="POST" action="{{ route('employees.store') }}">
                            @csrf


                            {{-- Campos de Usuário --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="name"
                                        id="name"
                                        class="form-control"
                                        value="{{ old('name') }}"
                                        placeholder="Enter employee name"
                                        required
                                    >
                                    @error('name')
                                        <div class="text-danger small">{{ $message }}</div>
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

                            {{-- Campos de Employee --}}
                            <div class="row mt-4">
                                <div class="col-md-4 mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        id="phone"
                                        class="form-control"
                                        value="{{ old('phone') }}"
                                        placeholder="+1 (555) 000-0000"
                                    >
                                    @error('phone')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="position" class="form-label">Position</label>
                                    <input
                                        type="text"
                                        name="position"
                                        id="position"
                                        class="form-control"
                                        value="{{ old('position') }}"
                                        placeholder="Enter position"
                                    >
                                    @error('position')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="ssn_tax_id" class="form-label">SSN / Tax ID</label>
                                    <input
                                        type="text"
                                        name="ssn_tax_id"
                                        id="ssn_tax_id"
                                        class="form-control"
                                        value="{{ old('ssn_tax_id') }}"
                                        placeholder="Enter SSN or Tax ID"
                                    >
                                    @error('ssn_tax_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="dispatcher_id" class="form-label">Dispatcher</label>

                                    @if($dispatchers)
                                        {{-- Envia o ID oculto no POST --}}
                                        <input type="hidden" name="dispatcher_id" value="{{ $dispatchers->id }}">

                                        {{-- Mostra apenas o nome do dispatcher logado --}}
                                        <input type="text" class="form-control" value="{{ $dispatchers->user->name }}" readonly>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            No dispatcher linked to your account. Please contact an administrator.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Ações --}}
                            <div class="row mt-4">
                                <div class="col d-flex justify-content-end">
                                    <a href="{{ route('employees.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Save</button>
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
      <p>You are not authorized to add employee.</p>
    </div>
  </div>
@endcan

@endsection
