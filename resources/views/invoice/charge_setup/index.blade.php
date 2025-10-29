@extends("layouts.app2")

@section('conteudo')

<div class="container">
  <div class="page-inner">
    <div class="page-header">
      <h3 class="fw-bold mb-3">Charges Setup</h3>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Charges Setup</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">List</a></li>
      </ul>
    </div>

    <div class="row">
  <div class="col-md-12">
    <div class="card">

      <!-- Cabeçalho: Pesquisa + Botão "Add" -->
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <div class="col-md-4 mb-2 m-md-0">
          <form method="GET" action="{{ route('charges_setups.index') }}" class="d-flex flex-grow-1 me-2">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2" placeholder="Pesquisar...">
            <button class="btn btn-outline-primary" type="submit"><i class="fa fa-search"></i></button>
          </form>
        </div>

        <a href="{{ route('charges_setups.create') }}" class="btn btn-primary">
          <i class="fa fa-plus"></i> New
        </a>
      </div>

      <!-- Tabela -->
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Carrier</th>
                <th>Data Setup</th>
                <th>Value type</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($charges_setup as $item)
                <tr>
                  <td>{{ $item->id }}</td>
                  <td>{{ $item->carrier->company_name ?? 'N/A' }}</td>
                  <td>
                    {{ is_array($item->charges_setup_array)
                        ? implode(', ', $item->charges_setup_array)
                        : $item->charges_setup_array }}
                  </td>
                  <td>{{ $item->price ?? 'N/A' }}</td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <a href="{{ route('charges_setups.edit', $item->id) }}"
                        class="btn btn-sm btn-primary" title="Editar">
                        <i class="fa fa-edit"></i>
                      </a>
                      <form action="{{ route('charges_setups.destroy', $item->id) }}" method="POST"
                            onsubmit="return confirm('Tem certeza que deseja excluir este Charge Setup?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                          <i class="fa fa-times"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted">Nenhum Charge Setup encontrado.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- Rodapé com paginação -->
      <div class="card-footer d-flex justify-content-start">
        {{ $charges_setup->appends(request()->query())->links('pagination::bootstrap-4') }}
      </div>

    </div>
  </div>
</div>


  </div>
</div>
<!-- <div class="btn-add-new">
  <a href="/charges_setups/add" class="btn btn-primary btn-sm">
    <i class="fa fa-plus"></i> Add
  </a>
</div> -->
@endsection
