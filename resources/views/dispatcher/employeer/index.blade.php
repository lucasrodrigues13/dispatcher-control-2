@extends("layouts.app")

@section('conteudo')
@can('pode_visualizar_employees')

<div class="container">
  <div class="page-inner">
    <div class="page-header">
      <h3 class="fw-bold mb-3">Employees</h3>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Employees</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">List</a></li>
      </ul>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card">

          <!-- Cabeçalho: Pesquisa + Botão "Novo" -->
          <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="col-md-4 mb-2 m-md-0">
              <form method="GET" action="{{ route('employees.index') }}" class="d-flex flex-grow-1 me-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2" placeholder="Pesquisar...">
                <button class="btn btn-outline-primary" type="submit"><i class="fa fa-search"></i></button>
              </form>
            </div>

            @can('pode_registrar_employees')
            <a href="{{ route('employees.create') }}" class="btn btn-primary">
              <i class="fa fa-plus"></i> New
            </a>
            @endcan
          </div>

          <!-- Tabela -->
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped align-middle">
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
                  @forelse($employeers as $item)
                    <tr>
                      <td>{{ $item->id }}</td>
                      <td>{{ $item->user ? $item->user->name : 'N/A' }}</td>
                      <td>{{ $item->user->email }}</td>
                      <td>{{ $item->phone ?? 'N/A' }}</td>
                      <td>{{ $item->position ?? 'N/A' }}</td>
                      <td>{{ $item->ssn_tax_id ?? 'N/A' }}</td>
                      <td>{{ $item->dispatcher->user->name ?? 'N/A' }}</td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                          @can('pode_editar_employees')
                          <a href="{{ route('employees.edit', $item->id) }}" class="btn btn-sm btn-primary" title="Editar">
                            <i class="fa fa-edit"></i>
                          </a>
                          @endcan

                          @can('pode_eliminar_employees')
                          <form action="{{ route('employees.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este funcionário?')" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                              <i class="fa fa-times"></i>
                            </button>
                          </form>
                          @endcan
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="8" class="text-center text-muted">Nenhum funcionário encontrado.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <!-- Rodapé com paginação -->
          <div class="card-footer d-flex justify-content-start">
            {{ $employeers->appends(request()->query())->links('pagination::bootstrap-4') }}
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
      <p>Você não tem autorização para acessar a lista de funcionários.</p>
    </div>
  </div>
@endcan
@endsection
