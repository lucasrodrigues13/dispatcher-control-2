@extends("layouts.app")

@section('conteudo')
@can('pode_visualizar_brokers')

<div class="container">
  <div class="page-inner">
    <div class="page-header">
      <h3 class="fw-bold mb-3">Brokers</h3>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Brokers</a></li>
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
              <form method="GET" action="{{ route('brokers.index') }}" class="d-flex flex-grow-1 me-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2" placeholder="Pesquisar...">
                <button class="btn btn-outline-primary" type="submit"><i class="fa fa-search"></i></button>
              </form>
            </div>

            @can('pode_registrar_brokers')
            <a href="{{ route('brokers.create') }}" class="btn btn-primary">
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
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Licença</th>
                    <th>Empresa</th>
                    <th>Telefone</th>
                    <th>Endereço</th>
                    <th>Notas</th>
                    <th>Email Contábil</th>
                    <th>Telefone Contábil</th>
                    <th>Fee %</th>
                    <th>Pagamento</th>
                    <th>Termos</th>
                    <th>Anexos</th>
                    <th class="text-center">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($brokers as $broker)
                    <tr>
                      <td>{{ $broker->id }}</td>
                      <td>{{ $broker->user->name ?? 'N/A' }}</td>
                      <td>{{ $broker->user->email ?? 'N/A' }}</td>
                      <td>{{ $broker->license_number ?? 'N/A' }}</td>
                      <td>{{ $broker->company_name ?? 'N/A' }}</td>
                      <td>{{ $broker->phone ?? 'N/A' }}</td>
                      <td>{{ $broker->address ?? 'N/A' }}</td>
                      <td>{{ $broker->notes ?? 'N/A' }}</td>
                      <td>{{ $broker->accounting_email ?? 'N/A' }}</td>
                      <td>{{ $broker->accounting_phone_number ?? 'N/A' }}</td>
                      <td>{{ $broker->fee_percent ?? 'N/A' }}%</td>
                      <td>{{ $broker->payment_method ?? 'N/A' }}</td>
                      <td>{{ $broker->payment_terms ?? 'N/A' }}</td>
                      <td>
                        <a href="/attachments/list/{{ $broker->user_id }}" class="text-decoration-underline">anexos</a>
                      </td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                          @can('pode_editar_brokers')
                          <a href="{{ route('brokers.edit', $broker->id) }}" class="btn btn-sm btn-primary" title="Editar">
                            <i class="fa fa-edit"></i>
                          </a>
                          @endcan

                          @can('pode_eliminar_brokers')
                          <form action="{{ route('brokers.destroy', $broker->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este broker?')" style="display:inline;">
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
                      <td colspan="15" class="text-center text-muted">Nenhum broker encontrado.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <!-- Rodapé com paginação -->
          <div class="card-footer d-flex justify-content-start">
            {{ $brokers->appends(request()->query())->links('pagination::bootstrap-4') }}
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
      <p>Você não tem autorização para acessar a lista de brokers.</p>
    </div>
  </div>
@endcan
@endsection
