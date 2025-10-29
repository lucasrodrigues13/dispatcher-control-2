@extends("layouts.app2")

@section('conteudo')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Edit Broker</h3>
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
                    Edit
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

                        <form method="POST" action="{{ route('brokers.update', $broker->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                {{-- Nome (User) --}}
                                <div class="mb-3 col-md-6">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter name" value="{{ old('name', $broker->user->name ?? '') }}" required>
                                </div>

                                {{-- Email (User) --}}
                                <div class="mb-3 col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter email" value="{{ old('email', $broker->user->email ?? '') }}" required>
                                </div>

                                {{-- Senha (opcional) --}}
                                <div class="mb-3 col-md-6">
                                    <label for="password" class="form-label">Password <small>(Leave blank to keep current)</small></label>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password if changing">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm new password">
                                </div>

                                {{-- License Number --}}
                                <div class="mb-3 col-md-6">
                                    <label for="license_number" class="form-label">License Number</label>
                                    <input type="text" name="license_number" id="license_number" class="form-control" placeholder="Enter license number" value="{{ old('license_number', $broker->license_number) }}">
                                </div>

                                {{-- Company Name --}}
                                <div class="mb-3 col-md-6">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Enter company name" value="{{ old('company_name', $broker->company_name) }}">
                                </div>

                                {{-- Phone --}}
                                <div class="mb-3 col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter phone" value="{{ old('phone', $broker->phone) }}">
                                </div>

                                {{-- Address --}}
                                <div class="mb-3 col-md-6">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" name="address" id="address" class="form-control" placeholder="Enter address" value="{{ old('address', $broker->address) }}">
                                </div>

                                {{-- Notes --}}
                                <div class="mb-3 col-12">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Additional notes">{{ old('notes', $broker->notes) }}</textarea>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <a href="{{ route('brokers.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
