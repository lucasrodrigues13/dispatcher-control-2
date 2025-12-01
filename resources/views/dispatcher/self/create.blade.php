@extends("layouts.app2")

@section('conteudo')
@can('pode_registrar_dispatchers')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add Dispatcher</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="#"><i class="icon-home"></i></a>
                </li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Dispatchers</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Add New</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="seta-voltar">
                            <a href="{{ route('dispatchers.index') }}"><i class="fas fa-arrow-left"></i></a>
                        </div>
                        <h4 class="card-title ms-2">Dispatcher Information</h4>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('dispatchers.store.dashboard') }}">
                            @csrf

                            <div class="row">

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="type">Type <span class="text-danger">*</span></label>
                                            <select name="type" id="type" class="form-control" required>
                                                <option value="" selected disabled>Select Type</option>
                                                <option value="Individual" {{ old('type') == 'Individual' ? 'selected' : '' }}>Individual</option>
                                                <option value="Company" {{ old('type') == 'Company' ? 'selected' : '' }}>Company</option>
                                            </select>
                                            @error('type')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 individual-field" style="display: none;">
                                    <div class="form-group">
                                        <label for="name">Name <span class="text-danger">*</span></label>
                                        <input type="text"
                                        name="name"
                                        class="form-control"
                                        id="name"
                                        placeholder="Enter dispatcher name"
                                        value="{{ old('name') }}"
                                        >
                                        @error('name')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 company-field" style="display: none;">
                                    <div class="form-group">
                                        <label for="company_name">Company Name <span class="text-danger">*</span></label>
                                        <input type="text"
                                        name="company_name"
                                        class="form-control"
                                        id="company_name"
                                        placeholder="Enter company name"
                                        value="{{ old('company_name') }}"
                                        >
                                        @error('company_name')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@example.com"
                                            id="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6 individual-field" style="display: none;">
                                    <div class="form-group">
                                        <label for="ssn_itin">SSN/ITIN <span class="text-danger">*</span></label>
                                        <input type="text"
                                        name="ssn_itin"
                                        class="form-control"
                                        id="ssn_itin"
                                        placeholder="Enter SSN or ITIN"
                                        value="{{ old('ssn_itin') }}"
                                        >
                                        @error('ssn_itin')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 company-field" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ein_tax_id">EIN/Tax ID <span class="text-danger">*</span></label>
                                        <input type="text"
                                        name="ein_tax_id"
                                        class="form-control"
                                        id="ein_tax_id"
                                        placeholder="Enter EIN or Tax ID"
                                        value="{{ old('ein_tax_id') }}"
                                        >
                                        @error('ein_tax_id')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 company-field" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="departament">Department <span class="text-danger">*</span></label>
                                        <input type="text"
                                        name="departament"
                                        class="form-control"
                                        id="departament"
                                        placeholder="Department"
                                        value="{{ old('departament') }}"
                                        >
                                        @error('departament')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input type="text" name="address" class="form-control" id="address" placeholder="Enter address" value="{{ old('address') }}">
                                        @error('address')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" name="city" class="form-control" id="city" placeholder="Enter city" value="{{ old('city') }}">
                                        @error('city')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" name="state" class="form-control" id="state" placeholder="Enter state" value="{{ old('state') }}">
                                        @error('state')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="zip_code">Zip Code</label>
                                        <input type="text" name="zip_code" class="form-control" id="zip_code" placeholder="Enter zip code" value="{{ old('zip_code') }}">
                                        @error('zip_code')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <input type="text" name="country" class="form-control" id="country" placeholder="Enter country" value="{{ old('country') }}">
                                        @error('country')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="phone">Phone</label>
                                        <input type="text" name="phone" class="form-control" id="phone" placeholder="Enter phone number" value="{{ old('phone') }}">
                                        @error('phone')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="notes">Notes</label>
                                        <textarea name="notes" class="form-control" id="notes" rows="3" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12 d-flex justify-content-end">
                                    <a href="{{ route('dispatchers.index') }}" class="btn btn-secondary me-2">Cancel</a>
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

<script>
    function toggleFields() {
        const type = document.getElementById('type').value;

        // Oculta todos os campos específicos
        document.querySelectorAll('.individual-field, .company-field').forEach(el => {
            el.style.display = 'none';
            // Remove required de todos os campos dentro do elemento
            el.querySelectorAll('input').forEach(input => {
                input.removeAttribute('required');
            });
        });

        if (type === 'Individual') {
            document.querySelectorAll('.individual-field').forEach(el => {
                el.style.display = 'block';
                // Adiciona required nos campos obrigatórios
                const nameInput = el.querySelector('#name');
                const ssnInput = el.querySelector('#ssn_itin');
                if (nameInput) nameInput.setAttribute('required', 'required');
                if (ssnInput) ssnInput.setAttribute('required', 'required');
            });
        } else if (type === 'Company') {
            document.querySelectorAll('.company-field').forEach(el => {
                el.style.display = 'block';
                // Adiciona required nos campos obrigatórios
                const companyInput = el.querySelector('#company_name');
                const einInput = el.querySelector('#ein_tax_id');
                const deptInput = el.querySelector('#departament');
                if (companyInput) companyInput.setAttribute('required', 'required');
                if (einInput) einInput.setAttribute('required', 'required');
                if (deptInput) deptInput.setAttribute('required', 'required');
            });
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        toggleFields();
        document.getElementById('type').addEventListener('change', toggleFields);
    });
</script>

@else
  <div class="container py-5">
    <div class="alert alert-warning text-center">
      <h4>Without permission</h4>
      <p>You are not authorized to add dispatcher.</p>
    </div>
  </div>
@endcan
@endsection
