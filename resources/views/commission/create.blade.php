@extends("layouts.app2")

@section('conteudo')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add New Commission</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('commissions.index') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ route('commissions.index') }}">Commissions</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    Add New
                </li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="seta-voltar">
                            <a href="{{ route('commissions.index') }}"><i class="fas fa-arrow-left"></i></a>
                        </div>
                        <h4 class="card-title ms-2 mb-0">Commission Information</h4>
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

                        <form method="POST" action="{{ route('commissions.store') }}">
                            @csrf

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label for="dispatcher_id" class="form-label">Dispatcher</label>
                                    <select name="dispatcher_id" id="dispatcher_id" class="form-control" required>
                                        <option value="">Select Dispatcher</option>
                                        @foreach($dispatchers as $dispatcher)
                                            <option value="{{ $dispatcher->id }}" {{ old('dispatcher_id') == $dispatcher->id ? 'selected' : '' }}>
                                                {{ $dispatcher->user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label for="deal_id" class="form-label">Deal</label>
                                    <select name="deal_id" id="deal_id" class="form-control" required>
                                        <option value="">Select Deal</option>
                                        @foreach($deals as $deal)
                                            <option value="{{ $deal->id }}" {{ old('deal_id') == $deal->id ? 'selected' : '' }}>
                                                {{ $deal->value }}% - {{ $deal->carrier ? $deal->carrier->company_name : 'No Carrier' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label for="employee_id" class="form-label">Employee</label>
                                    <select name="employee_id" id="employee_id" class="form-control" required>
                                        <option value="">Select Employee</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->user ? $employee->user->name : 'Employee #' . $employee->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="value" class="form-label">Value commission (%)</label>
                                    <input type="number" step="0.01" name="value" id="value" class="form-control" placeholder="Enter value" value="{{ old('value') }}" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('commissions.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
