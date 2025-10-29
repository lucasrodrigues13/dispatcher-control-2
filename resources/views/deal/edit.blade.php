@extends("layouts.app2")

@section('conteudo')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Edit Deal</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('deals.index') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ route('deals.index') }}">Deals</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    Edit
                </li>
            </ul>
        </div>

        <div class="">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div class="seta-voltar">
                        <a href="{{ route('deals.index') }}"><i class="fas fa-arrow-left"></i></a>
                    </div>
                    <h4 class="card-title ms-2 mb-0">Deal Information</h4>
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

                    <form method="POST" action="{{ route('deals.update', $deal->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dispatcher_id" class="form-label">Dispatcher</label>
                                <select name="dispatcher_id" id="dispatcher_id" class="form-select" required>
                                    <option value="">Select a dispatcher</option>
                                    @foreach ($dispatchers as $dispatcher)
                                        <option value="{{ $dispatcher->id }}"
                                            {{ old('dispatcher_id', $deal->dispatcher_id) == $dispatcher->id ? 'selected' : '' }}>
                                            {{ $dispatcher->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="carrier_id" class="form-label">Carrier</label>
                                <select name="carrier_id" id="carrier_id" class="form-select" required>
                                    <option value="">Select a carrier</option>
                                    @foreach ($carriers as $carrier)
                                        <option value="{{ $carrier->id }}"
                                            {{ old('carrier_id', $deal->carrier_id) == $carrier->id ? 'selected' : '' }}>
                                            {{ $carrier->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="value" class="form-label">Value</label>
                                <input type="number" name="value" id="value" class="form-control" placeholder="Enter value"
                                    value="{{ old('value', $deal->value) }}" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('deals.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
