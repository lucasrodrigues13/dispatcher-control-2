@extends("layouts.app2")

@section('conteudo')
@can('pode_visualizar_attachments')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Attachments</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Attachments</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">List</a></li>
            </ul>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="card">

              <!-- Cabeçalho com botão "Novo" -->
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <div class="col-md-4 mb-2 m-md-0">
                  {{-- Espaço para busca futura --}}
                </div>
                @can('pode_registrar_attachments')
                <a href="{{ route('attachments.create2', ['id' => request()->segment(3)]) }}" class="btn btn-primary">
                  <i class="fa fa-plus"></i> Novo
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
                        <th>Nome do Usuário</th>
                        <th>Email do Usuário</th>
                        <th>Void Check</th>
                        <th>W9 Form</th>
                        <th>COI</th>
                        <th>Proof FMCSA</th>
                        <th>CNH</th>
                        <th>Truck Pic 1</th>
                        <th>Truck Pic 2</th>
                        <th>Truck Pic 3</th>
                        <th class="text-center">Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($attachments as $attachment)
                        <tr>
                          <td>{{ $attachment->id }}</td>
                          <td>{{ $attachment->user->name ?? 'N/A' }}</td>
                          <td>{{ $attachment->user->email ?? 'N/A' }}</td>

                          {{-- Arquivos --}}
                          @foreach([
                            'void_check_path'      => 'Void Check',
                            'w9_path'              => 'W9 Form',
                            'coi_path'             => 'COI',
                            'proof_fmcsa_path'     => 'Proof FMCSA',
                            'drivers_license_path' => 'CNH',
                            'truck_picture_1_path' => 'Truck Pic 1',
                            'truck_picture_2_path' => 'Truck Pic 2',
                            'truck_picture_3_path' => 'Truck Pic 3',
                          ] as $field => $label)
                            <td>
                              @if(!empty($attachment->$field))
                                <a href="{{ $attachment->$field }}" target="_blank" class="text-primary text-decoration-underline">
                                  {{ $label }}
                                </a>
                              @else
                                <span class="text-muted">—</span>
                              @endif
                            </td>
                          @endforeach

                          <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                              @can('pode_visualizar_attachments')
                              <a href="{{ route('attachments.show', $attachment->id) }}" class="btn btn-sm btn-info" title="Visualizar">
                                <i class="fa fa-eye"></i>
                              </a>
                              @endcan

                              @can('pode_editar_attachments')
                              <a href="{{ route('attachments.edit', $attachment->id) }}" class="btn btn-sm btn-primary" title="Editar">
                                <i class="fa fa-edit"></i>
                              </a>
                              @endcan

                              @can('pode_eliminar_attachments')
                              <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST" onsubmit="return confirm('Deseja mesmo excluir este anexo?')" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                  <i class="fa fa-trash"></i>
                                </button>
                              </form>
                              @endcan
                            </div>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="12" class="text-center text-muted">Nenhum anexo encontrado.</td>
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

@else
  <div class="container py-5">
    <div class="alert alert-warning text-center">
      <h4>Sem permissão</h4>
      <p>Você não tem autorização para acessar os anexos.</p>
    </div>
  </div>
@endcan
@endsection
