@extends("layouts.app")

@section('conteudo')
@can('pode_visualizar_dispatchers')

<style>
  /* Opcional: para evitar que o texto quebre */
  .no-wrap-table th, .no-wrap-table td {
      white-space: nowrap;
  }

  /* limitar Caracteres */
  .truncate-cell {
    max-width: 200px; /* ou o valor que desejar */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
  }

  /* Expandir conteudo */
  .truncate-cell.expanded {
    white-space: normal;
    overflow: visible;
    text-overflow: unset;
    max-width: none;
  }
</style>

<div class="container">
  <div class="page-inner">
    <div class="page-header">
      <h3 class="fw-bold mb-3">Dispatchers</h3>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Dispatchers</a></li>
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
              <form method="GET" action="{{ route('dispatchers.index') }}" class="d-flex flex-grow-1 me-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2" placeholder="Pesquisar...">
                <button class="btn btn-outline-primary" type="submit"><i class="fa fa-search"></i></button>
              </form>
            </div>

            @can('pode_registrar_dispatchers')
            <a href="{{ route('dispatchers.create') }}" class="btn btn-primary">
              <i class="fa fa-plus"></i> New
            </a>
            @endcan
          </div>

          <!-- Tabela -->
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped align-middle no-wrap-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Departament</th>
                    <th>Type</th>
                    <th>Company</th>
                    <th>EIN/Tax ID</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($dispatchers as $dispatcher)
                    <tr>
                      <td>{{ $dispatcher->id }}</td>
                      <td>{{ $dispatcher->user->name ?? 'N/A' }}</td>
                      <td>{{ $dispatcher->user->email ?? 'N/A' }}</td>
                      <td>{{ $dispatcher->departament ?? 'N/A' }}</td>
                      <td>{{ $dispatcher->type ?? 'N/A' }}</td>
                      <td>{{ $dispatcher->company_name ?? 'N/A' }}</td>
                      <td>{{ $dispatcher->ein_tax_id ?? 'N/A' }}</td>
                      <td class="truncate-cell">{{ $dispatcher->address ?? 'N/A' }}</td>
                      <td>{{ $dispatcher->phone ?? 'N/A' }}</td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                          @can('pode_editar_dispatchers')
                          <a href="{{ route('dispatchers.edit', $dispatcher->id) }}" class="btn btn-sm btn-primary" title="Editar">
                            <i class="fa fa-edit"></i>
                          </a>
                          @endcan

                          @can('pode_eliminar_dispatchers')
                          <form action="{{ route('dispatchers.destroy', $dispatcher->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este dispatcher?')" style="display:inline;">
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
                      <td colspan="10" class="text-center text-muted">Nenhum dispatcher encontrado.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <!-- Rodapé com paginação à esquerda -->
          <div class="card-footer d-flex justify-content-start">
            {{ $dispatchers->appends(request()->query())->links('pagination::bootstrap-4') }}
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".truncate-cell").forEach(function (cell) {
      cell.addEventListener("click", function () {
        this.classList.toggle("expanded");
      });
    });
  });
</script>

@else
  <div class="container py-5">
    <div class="alert alert-warning text-center">
      <h4>Sem permissão</h4>
      <p>Você não tem autorização para acessar a lista de dispatchers.</p>
    </div>
  </div>
@endcan
@endsection
