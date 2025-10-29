@extends("layouts.app2")

@section('conteudo')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add New Employee</h3>
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
                    <a href="#">Employee</a>
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
                <div class="card">
                    <div class="card-header" style="display: flex; align-items: center;">
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
                                    <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        class="form-control"
                                        value="{{ old('name') }}"
                                        placeholder="Enter full name"
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
                                        class="form-control"
                                        value="{{ old('email') }}"
                                        placeholder="Enter email address"
                                        required
                                    >
                                    @error('email')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control"
                                        placeholder="Enter password"
                                        required
                                    >
                                    @error('password')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        id="password_confirmation"
                                        class="form-control"
                                        placeholder="Repeat password"
                                        required
                                    >
                                </div>
                            </div>

                            {{-- Campos de Employeer --}}
                            <div class="row mt-4">
                                <div class="col-md-4 mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        id="phone"
                                        class="form-control"
                                        value="{{ old('phone') }}"
                                        placeholder="Enter phone number"
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
                                <div class="col">
                                    <div class="form-group">
                                        <label for="dispatcher_id">Dispatcher</label>
                                        <select name="dispatcher_id" class="form-control" id="dispatcher_id" required>
                                            <option value="">Select Dispatcher</option>
                                            @foreach ($dispatchers as $dispatcher)
                                                <option value="{{ $dispatcher->id }}">{{ $dispatcher->user ? $dispatcher->user->name : $dispatcher->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
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

@endsection
