@extends("layouts.app2")

@section('conteudo')
@can('pode_editar_carriers')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Edit Carrier</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('carriers.index') }}">Carriers</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item">Edit</li>
            </ul>
        </div>

        {{-- Mensagens de sucesso --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Mensagens de erro --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <a href="{{ route('carriers.index') }}" class="me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h4 class="card-title">Carrier Information</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('carriers.update', $carrier->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">User Name</label>
                                        <input type="text" name="name" id="name" class="form-control" required
                                            value="{{ old('name', $carrier->user ? $carrier->user->name : '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">User Email</label>
                                        <input type="email" name="email" id="email" class="form-control" required
                                            value="{{ old('email', $carrier->user->email) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password <small>(Leave blank to keep current password)</small></label>
                                        <input type="password" name="password" id="password" class="form-control" autocomplete="new-password">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm Password</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company_name">Company Name</label>
                                        <input type="text" name="company_name" id="company_name" class="form-control" required
                                            value="{{ old('company_name', $carrier->company_name) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_name">Contact Name</label>
                                        <input type="text" name="contact_name" id="contact_name" class="form-control" required
                                            value="{{ old('contact_name', $carrier->contact_name) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone</label>
                                        <input type="text" name="phone" class="form-control" id="phone" required
                                            value="{{ old('phone', \App\Helpers\PhoneHelper::formatPhoneForDisplay($carrier->phone)) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_phone">Contact Phone</label>
                                        <input type="text" name="contact_phone" class="form-control" id="contact_phone"
                                            value="{{ old('contact_phone', \App\Helpers\PhoneHelper::formatPhoneForDisplay($carrier->contact_phone)) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input type="text" name="address" class="form-control" id="address" required
                                            value="{{ old('address', $carrier->address) }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" name="city" class="form-control" id="city" required
                                            value="{{ old('city', $carrier->city) }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" name="state" class="form-control" id="state" required
                                            value="{{ old('state', $carrier->state) }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="zip">Zip</label>
                                        <input type="text" name="zip" class="form-control" id="zip" required
                                            value="{{ old('zip', $carrier->zip) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <input type="text" name="country" class="form-control" id="country" required
                                            value="{{ old('country', $carrier->country ?? 'US') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="mc">MC</label>
                                        <input type="text" name="mc" class="form-control" id="mc"
                                            value="{{ old('mc', $carrier->mc) }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="dot">DOT</label>
                                        <input type="text" name="dot" class="form-control" id="dot"
                                            value="{{ old('dot', $carrier->dot) }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="ein">EIN</label>
                                        <input type="text" name="ein" class="form-control" id="ein"
                                            value="{{ old('ein', $carrier->ein) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="website">Website</label>
                                        <input type="url" name="website" class="form-control" id="website"
                                            value="{{ old('website', $carrier->website) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="about">About</label>
                                        <textarea name="about" class="form-control" id="about" rows="2">{{ old('about', $carrier->about) }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="trailer_capacity">Trailer Capacity</label>
                                        <input type="number" name="trailer_capacity" class="form-control" id="trailer_capacity"
                                            value="{{ old('trailer_capacity', $carrier->trailer_capacity) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_auto_hauler" id="is_auto_hauler" value="1"
                                            {{ old('is_auto_hauler', $carrier->is_auto_hauler) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_auto_hauler">Auto Hauler</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_towing" id="is_towing" value="1"
                                            {{ old('is_towing', $carrier->is_towing) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_towing">Towing</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_driveaway" id="is_driveaway" value="1"
                                            {{ old('is_driveaway', $carrier->is_driveaway) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_driveaway">Driveaway</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dispatcher_id">Dispatcher</label>
                                        <select name="dispatcher_id" class="form-control" id="dispatcher_id" required>
                                            <option value="">Select Dispatcher</option>
                                            @foreach ($dispatchers as $dispatcher)
                                                <option value="{{ $dispatcher->id }}"
                                                    {{ old('dispatcher_id', $carrier->dispatcher_id) == $dispatcher->id ? 'selected' : '' }}>
                                                    {{ $dispatcher->user ? $dispatcher->user->name : 'N/A' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
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
      <p>Você não tem autorização para editar carriers.</p>
    </div>
  </div>
@endcan

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar máscaras de telefone
    if (typeof initPhoneMask === 'function') {
        initPhoneMask('phone', 'phone_country_code', '+1');
        initPhoneMask('contact_phone', 'contact_phone_country_code', '+1');
    }
});
</script>

@endsection
