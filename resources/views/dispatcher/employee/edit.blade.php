@extends("layouts.app2")

@section('conteudo')
@can('pode_editar_employees')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Edit Employee</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('employees.index') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('employees.index') }}">Employee</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item">Edit</li>
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
                       <form method="POST" action="{{ route('employees.update', $employee->id) }}">
                            @csrf
                            @method('PUT')

                            {{-- Campos de Usuário --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="name"
                                        id="name"
                                        class="form-control"
                                        value="{{ old('name', $employee->name) }}"
                                        placeholder="Enter full name"
                                        required
                                    >
                                    @error('name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email"
                                        name="email"
                                        id="email"
                                        class="form-control"
                                        value="{{ old('email', $employee->email) }}"
                                        placeholder="Enter email address"
                                        required
                                    >
                                    @error('email')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Senha (opcional) --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password <small>(leave blank to keep current)</small></label>
                                    <input type="password"
                                        name="password"
                                        id="password"
                                        class="form-control"
                                        placeholder="Enter new password if changing"
                                    >
                                    @error('password')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password"
                                        name="password_confirmation"
                                        id="password_confirmation"
                                        class="form-control"
                                        placeholder="Repeat new password"
                                    >
                                </div>
                            </div>

                            {{-- Campos do Employee --}}
                            <div class="row mt-4">
                                <div class="col-md-4 mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text"
                                        name="phone"
                                        id="phone"
                                        class="form-control"
                                        value="{{ old('phone', $employee->phone) }}"
                                        placeholder="Enter phone number"
                                    >
                                    @error('phone')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="position" class="form-label">Position</label>
                                    <input type="text"
                                        name="position"
                                        id="position"
                                        class="form-control"
                                        value="{{ old('position', $employee->position) }}"
                                        placeholder="Enter position"
                                    >
                                    @error('position')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="ssn_tax_id" class="form-label">SSN / Tax ID</label>
                                    <input type="text"
                                        name="ssn_tax_id"
                                        id="ssn_tax_id"
                                        class="form-control"
                                        value="{{ old('ssn_tax_id', $employee->ssn_tax_id) }}"
                                        placeholder="Enter SSN or Tax ID"
                                    >
                                    @error('ssn_tax_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="dispatcher_id" class="form-label">Dispatcher <span class="text-danger">*</span></label>
                                    <select name="dispatcher_id" id="dispatcher_id" class="form-control" required>
                                        <option value="">Select Dispatcher</option>
                                        @foreach ($dispatchers as $dispatcher)
                                            <option value="{{ $dispatcher->id }}"
                                                {{ old('dispatcher_id', $employee->dispatcher_id) == $dispatcher->id ? 'selected' : '' }}>
                                                {{ $dispatcher->user ? $dispatcher->user->name : $dispatcher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('dispatcher_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Ações --}}
                            <div class="row mt-4">
                                <div class="col d-flex justify-content-end">
                                    <a href="{{ route('employees.index') }}" class="btn btn-secondary me-2">Cancel</a>
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

@else
  <div class="container py-5">
    <div class="alert alert-warning text-center">
      <h4>Sem permissão</h4>
      <p>Você não tem autorização para editar funcionários.</p>
    </div>
  </div>
@endcan
@endsection
