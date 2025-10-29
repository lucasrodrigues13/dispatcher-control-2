@extends("layouts.app2")

@section('conteudo')
<div class="container">
  <div class="page-inner">
    <div class="page-header">
      <h3 class="fw-bold mb-3">Additional Services
      </h3>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Additional Services
        </a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">List</a></li>
      </ul>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card">

          <!-- Cabeçalho com filtros e paginação -->
          <div class="card-header">
            <div class="row">
              <div class="col-md-4 my-2">
                <div class="input-group position-relative">
                  <input type="text" placeholder="Search ..." class="form-control" />
                  <div class="input-group-prepend" style="position: absolute; top: 0; right: -10px;">
                    <button type="submit" class="btn btn-search">
                      <i class="fa fa-search search-icon"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="col my-2 d-flex justify-content-end align-items-center gap-2">
                <select class="form-select form-select-sm" style="width: 70px;">
                  <option>10</option>
                  <option>25</option>
                  <option>50</option>
                  <option>100</option>
                </select>
                <span class="mx-2 text-muted">{{ $additional_services->firstItem() }}-{{ $additional_services->lastItem() }} de {{ $additional_services->total() }}</span>
                {{ $additional_services->links('pagination::bootstrap-4') }}
              </div>
            </div>
          </div>

          <!-- Tabela -->
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Position</th>
                    <th>SSN/Tax ID</th>
                    <th>Dispatcher</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($additional_services as $item)
                    <tr>
                      <td>{{ $item->id }}</td>
                      <td>{{ $item->user ? $item->user->name : 'N/A' }}</td>
                      <td>{{ $item->user->email }}</td>
                      <td>{{ $item->phone ?? 'N/A' }}</td>
                      <td>{{ $item->position ?? 'N/A' }}</td>
                      <td>{{ $item->ssn_tax_id ?? 'N/A' }}</td>
                      <td>{{ $item->dispatcher->user->name }}</td>
                      <td class="text-center">
                        <div class="form-button-action">
                          <a href="{{ route('employees.edit', $item->id) }}"
                            class="btn btn-link btn-primary btn-lg"
                            data-bs-toggle="tooltip"
                            title="Edit">
                            <i class="fa fa-edit"></i>
                          </a>
                          <form action="{{ route('employees.destroy', $item->id) }}"
                                method="POST"
                                style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-link btn-danger"
                                    data-bs-toggle="tooltip"
                                    title="Delete"
                                    onclick="return confirm('Are you sure you want to delete this dispatcher?')">
                              <i class="fa fa-times"></i>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>


              @if($additional_services->isEmpty())
                <p class="text-center text-muted mt-3">Nenhum dispatcher encontrado.</p>
              @endif
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<div class="btn-add-new">
  <a href="/employees/add" class="btn btn-primary btn-sm">
    <i class="fa fa-plus"></i> Add
  </a>
</div>
@endsection
