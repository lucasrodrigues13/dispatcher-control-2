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
                        <form method="POST" action="{{ route('dispatchers.store') }}">
                            @csrf

                            <div class="row">
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="type">Type</label>
                                            <select name="type" id="type" class="form-control" required>
                                                <option value="" selected disabled>Select Type</option>
                                                <option value="Individual">Individual</option>
                                                <option value="Company">Company</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 individual">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" name="name" class="form-control" id="name" placeholder="Enter name">
                                    </div>
                                </div>

                                <div class="col-md-6 company">
                                    <div class="form-group">
                                        <label for="company_name">Company Name</label>
                                        <input type="text" name="company_name" class="form-control" id="company_name" placeholder="Enter company name">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" name="password" class="form-control" id="password" placeholder="Enter Password">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm Password</label>
                                        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Confirm Password">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6 individual">
                                    <div class="form-group">
                                        <label for="ssn_itin">SSN/ITIN</label>
                                        <input type="text" name="ssn_itin" class="form-control" id="ssn_itin" placeholder="Enter SSN or ITIN">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 company">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ein_tax_id">EIN/Tax ID</label>
                                        <input type="text" name="ein_tax_id" class="form-control" id="ein_tax_id" placeholder="Enter EIN or Tax ID">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 company">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="departament">Departament</label>
                                        <input type="text" name="departament" class="form-control" id="departament" placeholder="Departament">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input type="text" name="address" class="form-control" id="address" placeholder="Enter address">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" name="city" class="form-control" id="city" placeholder="Enter city">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" name="state" class="form-control" id="state" placeholder="Enter state">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="zip_code">Zip Code</label>
                                        <input type="text" name="zip_code" class="form-control" id="zip_code" placeholder="Enter zip code">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <input type="text" name="country" class="form-control" id="country" placeholder="Enter country">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="phone">Phone</label>
                                        <input type="text" name="phone" class="form-control" id="phone" placeholder="Enter phone number">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="notes">Notes</label>
                                        <textarea name="notes" class="form-control" id="notes" rows="3" placeholder="Additional notes..."></textarea>
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
        document.querySelectorAll('.individual, .company').forEach(el => el.classList.add('d-none'));
        if (type === 'Individual') {
            document.querySelectorAll('.individual').forEach(el => el.classList.remove('d-none'));
        } else if (type === 'Company') {
            document.querySelectorAll('.company').forEach(el => el.classList.remove('d-none'));
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
      <h4>Sem permissão</h4>
      <p>Você não tem autorização para adicionar dispatchers.</p>
    </div>
  </div>
@endcan
@endsection
