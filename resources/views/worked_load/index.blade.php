@extends("layouts.app")

@section('conteudo')
<div class="container">
  <div class="page-inner">
    <div class="page-header">
      <h3 class="fw-bold mb-3">Deals</h3>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Deals</a></li>
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
                <form method="GET" action="{{ route('deals.index') }}">
                  <div class="input-group position-relative">
                    <input name="search" type="text" value="{{ request('search') }}" placeholder="Search ..." class="form-control" />
                    <div class="input-group-prepend" style="position: absolute; top: 0; right: -10px;">
                      <button type="submit" class="btn btn-search">
                        <i class="fa fa-search search-icon"></i>
                      </button>
                    </div>
                  </div>
                </form>
              </div>

              <div class="col my-2 d-flex justify-content-end align-items-center gap-2">
                <select class="form-select form-select-sm" style="width: 70px;" onchange="location = this.value;">
                  <option {{ request('perPage') == 10 ? 'selected' : '' }} value="{{ route('deals.index', array_merge(request()->all(), ['perPage' => 10])) }}">10</option>
                  <option {{ request('perPage') == 25 ? 'selected' : '' }} value="{{ route('deals.index', array_merge(request()->all(), ['perPage' => 25])) }}">25</option>
                  <option {{ request('perPage') == 50 ? 'selected' : '' }} value="{{ route('deals.index', array_merge(request()->all(), ['perPage' => 50])) }}">50</option>
                  <option {{ request('perPage') == 100 ? 'selected' : '' }} value="{{ route('deals.index', array_merge(request()->all(), ['perPage' => 100])) }}">100</option>
                </select>
                <span class="mx-2 text-muted">{{ $deals->firstItem() ?? 0 }}-{{ $deals->lastItem() ?? 0 }} de {{ $deals->total() }}</span>
                {{ $deals->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
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
                    <th>Dispatcher</th>
                    <th>Carrier</th>
                    <th>Value</th>
                    <th>Commissions</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($deals as $deal)
                    <tr>
                      <td>{{ $deal->id }}</td>
                      <td>{{ $deal->dispatcher->user->name ?? 'N/A' }}</td>
                      <td>{{ $deal->carrier->company_name ?? 'N/A' }}</td>
                      <td>{{ number_format($deal->value, 0, ',', '.') }}%</td>
                      <td><a href="{{ route('deals.commissions', $deal->id) }}">Employees</a></td>
                      <td class="text-center">
                        <div class="form-button-action">
                          <a href="{{ route('deals.edit', $deal->id) }}" class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip" title="Edit">
                            <i class="fa fa-edit"></i>
                          </a>
                          <form action="{{ route('deals.destroy', $deal->id) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link btn-danger" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this deal?')">
                              <i class="fa fa-times"></i>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center text-muted">Nenhum deal encontrado.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<div class="btn-add-new">
  <a href="{{ route('deals.create') }}" class="btn btn-primary btn-sm">
    <i class="fa fa-plus"></i> Add
  </a>
</div>
@endsection
