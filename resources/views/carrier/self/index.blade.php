@extends("layouts.app2")

@section('conteudo')
@can('pode_visualizar_carriers')

<div class="container">
 <div class="page-inner">
   <div class="page-header">
      <h3 class="fw-bold mb-3">Carriers</h3>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Carriers</a></li>
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
              <form method="GET" action="{{ route('carriers.index') }}" class="d-flex flex-grow-1 me-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2" placeholder="Pesquisar...">
                <button class="btn btn-outline-primary" type="submit"><i class="fa fa-search"></i></button>
              </form>
            </div>

            @can('pode_registrar_carriers')
            <a href="{{ route('carriers.create') }}" class="btn btn-primary">
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
                    <th>Company name</th>
                    <th>Email</th>
                    <th>Phone number</th>
                    <th>Address</th>
                    <th>MC</th>
                    <th>DOT</th>
                    <th>EIN</th>
                    <th>Dispatcher</th>
                    <th>Attachments</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($carriers as $carrier)
                    <tr>
                      <td>{{ $carrier->company_name }}</td>
                      <td>{{ $carrier->user->email }}</td>
                      <td>{{ $carrier->phone }}</td>
                      <td>{{ $carrier->address }}</td>
                      <td>{{ $carrier->mc }}</td>
                      <td>{{ $carrier->dot }}</td>
                      <td>{{ $carrier->ein }}</td>
                      <td>{{ optional($carrier->dispatchers->user)->name ?? 'N/A' }}</td>
                      <td><a href="/attachments/list/{{ $carrier->user_id }}">Attachments</a></td>
                      <td>
                        <x-user-status-badge :user="$carrier->user" />
                      </td>
                      <td class="text-center">
                        <div class="dropdown">
                          <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Actions
                          </button>
                          <ul class="dropdown-menu">
                            @can('pode_editar_carriers')
                            <li>
                              <a class="dropdown-item" href="{{ route('carriers.edit', $carrier) }}">
                                <i class="fa fa-edit me-2"></i>Edit
                              </a>
                            </li>
                            @endcan
                            
                            @if(auth()->user()->is_owner || auth()->user()->is_admin)
                              <li><hr class="dropdown-divider"></li>
                              <li>
                                <x-toggle-status-button 
                                  :user="$carrier->user" 
                                  :route="route('carriers.toggle-status', $carrier->id)" 
                                />
                              </li>
                            @endif
                            
                            @can('pode_eliminar_carriers')
                            <li><hr class="dropdown-divider"></li>
                            <li>
                              <form action="{{ route('carriers.destroy', $carrier) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este carrier?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger">
                                  <i class="fa fa-times me-2"></i>Delete
                                </button>
                              </form>
                            </li>
                            @endcan
                          </ul>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="11" class="text-center text-muted">Nenhum carrier encontrado.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <!-- Rodapé com paginação -->
          <div class="card-footer d-flex justify-content-start">
            {{ $carriers->appends(request()->query())->links('pagination::bootstrap-4') }}
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
      <p>Você não tem autorização para acessar a lista de carriers.</p>
    </div>
  </div>
@endcan
@endsection
