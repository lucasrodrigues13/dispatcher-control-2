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
          <!-- Cabeçalho com botão de novo -->
          <div class="card-header d-flex justify-content-end">
            @can('pode_registrar_attachments')
            <a href="{{ route('attachments.create') }}" class="btn btn-primary btn-sm">
              <i class="fa fa-plus"></i> Novo Anexo
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
                    <th>User Name</th>
                    <th>User Email</th>
                    <th>Void Check</th>
                    <th>W9 Form</th>
                    <th>COI</th>
                    <th>Proof FMCSA</th>
                    <th>Driver's License</th>
                    <th>Truck Pic 1</th>
                    <th>Truck Pic 2</th>
                    <th>Truck Pic 3</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th class="text-center">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($attachments as $attachment)
                    <tr>
                      <td>{{ $attachment->id }}</td>
                      <td>{{ $attachment->user->name ?? 'N/A' }}</td>
                      <td>{{ $attachment->user->email ?? 'N/A' }}</td>

                      {{-- Para cada campo de arquivo exibe link se houver --}}
                      @foreach([
                        'void_check_path'      => 'Void Check',
                        'w9_path'              => 'W9 Form',
                        'coi_path'             => 'COI',
                        'proof_fmcsa_path'     => 'Proof FMCSA',
                        'drivers_license_path' => "Driver's License",
                        'truck_picture_1_path' => 'Truck Pic 1',
                        'truck_picture_2_path' => 'Truck Pic 2',
                        'truck_picture_3_path' => 'Truck Pic 3',
                      ] as $field => $label)
                        <td>
                          @if(!empty($attachment->$field))
                            <a href="{{ $attachment->$field }}"
                              target="_blank"
                              class="text-primary text-decoration-underline">
                              {{ $label }}
                            </a>
                          @else
                            <span class="text-muted">—</span>
                          @endif
                        </td>
                      @endforeach

                      <td>{{ $attachment->created_at->format('d/m/Y H:i') }}</td>
                      <td>{{ $attachment->updated_at->format('d/m/Y H:i') }}</td>
                      <td class="text-center">
                        <div class="form-button-action">
                          @can('pode_visualizar_attachments')
                          <a href="{{ route('attachments.show', $attachment->id) }}"
                            class="btn btn-link btn-info btn-sm" title="Visualizar">
                            <i class="fa fa-eye"></i>
                          </a>
                          @endcan

                          @can('pode_editar_attachments')
                          <a href="{{ route('attachments.edit', $attachment->id) }}"
                            class="btn btn-link btn-primary btn-sm" title="Editar">
                            <i class="fa fa-edit"></i>
                          </a>
                          @endcan

                          @can('pode_eliminar_attachments')
                          <form action="{{ route('attachments.destroy', $attachment->id) }}"
                                method="POST"
                                style="display:inline-block"
                                onsubmit="return confirm('Deseja mesmo excluir este anexo?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-link btn-danger btn-sm"
                                    title="Excluir">
                              <i class="fa fa-trash"></i>
                            </button>
                          </form>
                          @endcan
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="15" class="text-center text-muted">Nenhum anexo encontrado.</td>
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
