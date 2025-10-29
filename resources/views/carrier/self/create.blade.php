@extends("layouts.app2")

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
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <a href="{{ route('carriers.index') }}" class="me-3"><i class="fas fa-arrow-left"></i></a>
                        <h4 class="card-title">Carrier Information</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('carriers.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">User Name</label>
                                        <input type="text" name="name" id="name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" name="email" id="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                                </div>
                            </div>

                            {{-- Dados do Carrier --}}
                            <div class="row mt-4">
                                <div class="col-md-6 mb-3">
                                    <label for="company_name">Company Name</label>
                                    <input type="text" name="company_name" id="company_name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contact_name">Contact Name</label>
                                    <input type="text" name="contact_name" id="contact_name" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contact_phone">Contact Phone</label>
                                    <input type="text" name="contact_phone" id="contact_phone" class="form-control">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <label for="address">Address</label>
                                    <input type="text" name="address" id="address" class="form-control" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="city">City</label>
                                    <input type="text" name="city" id="city" class="form-control" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="state">State</label>
                                    <input type="text" name="state" id="state" class="form-control" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="zip">Zip</label>
                                    <input type="text" name="zip" id="zip" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <label for="country">Country</label>
                                    <input type="text" name="country" id="country" class="form-control" value="US" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="mc">MC</label>
                                    <input type="text" name="mc" id="mc" class="form-control">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="dot">DOT</label>
                                    <input type="text" name="dot" id="dot" class="form-control">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="ein">EIN</label>
                                    <input type="text" name="ein" id="ein" class="form-control">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4 mb-3">
                                    <label for="website">Website</label>
                                    <input type="url" name="website" id="website" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="about">About</label>
                                    <textarea name="about" id="about" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="trailer_capacity">Trailer Capacity</label>
                                    <input type="number" name="trailer_capacity" id="trailer_capacity" class="form-control">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_auto_hauler" id="is_auto_hauler" value="1">
                                        <label class="form-check-label" for="is_auto_hauler">Auto Hauler</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_towing" id="is_towing" value="1">
                                        <label class="form-check-label" for="is_towing">Towing</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_driveaway" id="is_driveaway" value="1">
                                        <label class="form-check-label" for="is_driveaway">Driveaway</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <label for="dispatcher_company_id">Dispatcher</label>
                                    <select name="dispatcher_company_id" id="dispatcher_company_id" class="form-control" required>
                                        <option value="">Select Dispatcher</option>
                                        @foreach ($dispatchers as $dispatcher)
                                            <option value="{{ $dispatcher->id }}">{{ $dispatcher->user->name }}</option>
                                        @endforeach
                                    </select>
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

@else
  <div class="container py-5">
    <div class="alert alert-warning text-center">
      <h4>Sem permissão</h4>
      <p>Você não tem autorização para adicionar carriers.</p>
    </div>
  </div>
@endcan

@endsection
