@extends("layouts.app")

@section('conteudo')
@can('pode_visualizar_drivers')

<div class="container">
  <div class="page-inner">
    <div class="page-header">
      <h3 class="fw-bold mb-3">Drivers</h3>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Drivers</a></li>
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
              <form method="GET" action="{{ route('drivers.index') }}" class="d-flex flex-grow-1 me-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2" placeholder="Pesquisar...">
                <button class="btn btn-outline-primary" type="submit"><i class="fa fa-search"></i></button>
              </form>
            </div>

            @can('pode_registrar_drivers')
            <a href="{{ route('drivers.create') }}" class="btn btn-primary">
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
                    <th>Name</th>
                    <th>Email</th>
                    <th>Carrier</th>
                    <th>Phone</th>
                    <th>SSN/Tax ID</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($drivers as $item)
                    <tr>
                      <td>{{ $item->user->name ?? $item->name ?? 'N/A' }}</td>
                      <td>{{ $item->user->email ?? $item->email ?? 'N/A' }}</td>
                      <td>{{ $item->carrier->company_name ?? 'N/A' }}</td>
                      <td>{{ $item->phone ?? 'N/A' }}</td>
                      <td>{{ $item->ssn_tax_id ?? 'N/A' }}</td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                          @can('pode_editar_drivers')
                          <a href="{{ route('drivers.edit', $item->id) }}" class="btn btn-sm btn-primary" title="Editar">
                            <i class="fa fa-edit"></i>
                          </a>
                          @endcan

                          @can('pode_eliminar_drivers')
                          <form action="{{ route('drivers.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este driver?')" style="display:inline;">
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
                      <td colspan="7" class="text-center text-muted">Nenhum driver encontrado.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <!-- Rodapé com paginação -->
          <div class="card-footer d-flex justify-content-start">
            {{ $drivers->appends(request()->query())->links('pagination::bootstrap-4') }}
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
      <p>Você não tem autorização para acessar a lista de drivers.</p>
    </div>
  </div>
@endcan
@endsection
