@extends("layouts.app2")

@section('conteudo')
@can('pode_editar_dispatchers')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Edit Dispatcher</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Dispatchers</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Edit</a></li>
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
                        <form method="POST" action="{{ route('dispatchers.update', $dispatcher->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="type">Type</label>
                                        <select name="type" id="type" class="form-control" disabled required>
                                            <option value="" disabled>Select Type</option>
                                            <option value="Individual" {{ $dispatcher->type=='Individual' ? 'selected':'' }}>Individual</option>
                                            <option value="Company"    {{ $dispatcher->type=='Company'    ? 'selected':'' }}>Company</option>
                                        </select>
                                        <input type="hidden" name="type" value="{{ $dispatcher->type }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6 individual">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" name="name" class="form-control" id="name"
                                            value="{{ old('name', $dispatcher->name) }}">
                                    </div>
                                </div>
                                <div class="col-md-6 company">
                                    <div class="form-group">
                                        <label for="company_name">Company Name</label>
                                        <input type="text" name="company_name" class="form-control" id="company_name"
                                            value="{{ old('company_name', $dispatcher->company_name) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" class="form-control" id="email" required
                                            value="{{ old('email', $dispatcher->user->email) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password <small>(leave blank to keep current)</small></label>
                                        <input type="password" name="password" class="form-control" id="password">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm Password</label>
                                        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6 individual">
                                    <div class="form-group">
                                        <label for="ssn_itin">SSN/ITIN</label>
                                        <input type="text" name="ssn_itin" class="form-control" id="ssn_itin"
                                            value="{{ old('ssn_itin', $dispatcher->ssn_itin) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 company">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ein_tax_id">EIN/Tax ID</label>
                                        <input type="text" name="ein_tax_id" class="form-control" id="ein_tax_id"
                                            value="{{ old('ein_tax_id', $dispatcher->ein_tax_id) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 company">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="departament">Departament</label>
                                        <input type="text" name="departament" class="form-control" id="departament"
                                            value="{{ old('departament', $dispatcher->departament) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4"><div class="form-group">
                                    <label for="address">Address</label>
                                    <input type="text" name="address" class="form-control" id="address"
                                        value="{{ old('address', $dispatcher->address) }}">
                                </div></div>
                                <div class="col-md-4"><div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" name="city" class="form-control" id="city"
                                        value="{{ old('city', $dispatcher->city) }}">
                                </div></div>
                                <div class="col-md-4"><div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" name="state" class="form-control" id="state"
                                        value="{{ old('state', $dispatcher->state) }}">
                                </div></div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4"><div class="form-group">
                                    <label for="zip_code">Zip Code</label>
                                    <input type="text" name="zip_code" class="form-control" id="zip_code"
                                        value="{{ old('zip_code', $dispatcher->zip_code) }}">
                                </div></div>
                                <div class="col-md-4"><div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text" name="country" class="form-control" id="country"
                                        value="{{ old('country', $dispatcher->country) }}">
                                </div></div>
                                <div class="col-md-4"><div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" class="form-control" id="phone"
                                        value="{{ old('phone', $dispatcher->phone) }}">
                                </div></div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12"><div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" class="form-control" id="notes" rows="3">{{ old('notes', $dispatcher->notes) }}</textarea>
                                </div></div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12 d-flex justify-content-end">
                                    <a href="{{ route('dispatchers.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Update</button>
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
        } else {
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
      <p>Você não tem autorização para editar dispatchers.</p>
    </div>
  </div>
@endcan
@endsection
