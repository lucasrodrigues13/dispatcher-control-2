@extends("layouts.app2")

@section('conteudo')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add Attachments</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('attachments.index2', ['id' => request()->segment(3)]) }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item">
                    <a href="{{ route('attachments.index2' , ['id' => request()->segment(3)]) }}">Attachments</a>
                </li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item">Add New</li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="seta-voltar">
                            <a href="{{ route('attachments.index2', ['id' => request()->segment(3)]) }}"><i class="fas fa-arrow-left"></i></a>
                        </div>
                        <h4 class="card-title ms-2 mb-0">Attachment Information</h4>
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

                        <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                {{-- Seleção do usuário --}}
                                
                                <div class="mb-3 col-md-6">
                                    <label for="user_id" class="form-label">Select User</label>
                                    <select name="user_id" id="user_id" class="form-select" required>
                                        <option value="">-- Select User --</option>
                                        @foreach ($users as $user)
                                        <option value="{{ $user->id }}" selected>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>


                                {{-- Campos de upload (nomes sem _path) --}}
                                <div class="mb-3 col-md-6">
                                    <label for="void_check" class="form-label">Void Check</label>
                                    <input type="file" name="void_check" id="void_check" class="form-control">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="w9" class="form-label">W9 Form</label>
                                    <input type="file" name="w9" id="w9" class="form-control">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="coi" class="form-label">Certificate of Insurance (COI)</label>
                                    <input type="file" name="coi" id="coi" class="form-control">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="proof_fmcsa" class="form-label">Proof of FMCSA Registration</label>
                                    <input type="file" name="proof_fmcsa" id="proof_fmcsa" class="form-control">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="drivers_license" class="form-label">Driver's License</label>
                                    <input type="file" name="drivers_license" id="drivers_license" class="form-control">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="truck_picture_1" class="form-label">Truck Picture 1</label>
                                    <input type="file" name="truck_picture_1" id="truck_picture_1" class="form-control">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="truck_picture_2" class="form-label">Truck Picture 2</label>
                                    <input type="file" name="truck_picture_2" id="truck_picture_2" class="form-control">
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label for="truck_picture_3" class="form-label">Truck Picture 3</label>
                                    <input type="file" name="truck_picture_3" id="truck_picture_3" class="form-control">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('attachments.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
