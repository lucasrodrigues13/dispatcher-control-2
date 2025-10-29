@extends('layouts.app2')

@section('conteudo')
<div class="container">
  <div class="page-inner">
    <div class="page-header">
      <h3 class="fw-bold mb-3">Time Line Charges</h3>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Time Line Charges</a></li>
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
          <form method="GET" action="{{ route('time_line_charges.index') }}" class="d-flex flex-grow-1 me-2">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2" placeholder="Pesquisar...">
            <button class="btn btn-outline-primary" type="submit"><i class="fa fa-search"></i></button>
          </form>
        </div>

        <a href="{{ route('time_line_charges.create') }}" class="btn btn-primary">
          <i class="fa fa-plus"></i> New Invoice
        </a>
      </div>

      <!-- Tabela -->
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Carrier</th>
                <th>Dispatcher</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              @foreach($timeLineCharges as $item)
                <tr>
                  <td>{{ $item->id }}</td>
                  <td>{{ $item->invoice_id }}</td>
                  <td>{{ $item->carrier->company_name ?? 'N/A' }}</td>
                  <td class="font-weight-bold">
                    {{-- ⭐ CORRIGIDO: Usando $item ao invés de $charge --}}
                    ${{ number_format($item->getTotalLoadsAmount(), 2) }}

                    {{-- Mostra quantos loads estão incluídos --}}
                    <small class="text-muted d-block">
                        ({{ count($item->getLoadIdsArrayAttribute()) }} loads)
                    </small>
                </td>
                  <td>
                    <select class="form-select form-select-sm status-selector
                      {{ $item->status_payment === 'paid' ? 'bg-success text-white' :
                          ($item->status_payment === 'Invoiced' ? 'bg-warning text-dark' : 'bg-danger text-white') }}"
                      data-id="{{ $item->id }}" style="width: 100px;">
                        <option value="Invoiced" {{ $item->status_payment === 'Invoiced' ? 'selected' : '' }}>Invoiced</option>
                        <option value="paid" {{ $item->status_payment === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ $item->status_payment === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                  </td>
                  <td>{{ $item->carrier->company_name ?? 'N/A' }}</td>
                  <td>{{ $item->dispatcher->user->name ?? $item->dispatcher->name ?? 'N/A' }}</td>
                  <td class="text-center">
                    <div class="form-button-action d-flex justify-content-center gap-1 pt-3">
                      <a href="{{ route('time_line_charges.show', $item->id) }}" class="btn btn-sm btn-secondary" title="Visualizar" style="height: 27px;">
                        <i class="fa fa-eye"></i>
                      </a>
                      <form action="{{ route('time_line_charges.destroy', $item->id) }}"
                            method="POST"
                            style="display:inline-block"
                            onsubmit="return confirm('Are you sure you want to delete this record?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                          <i class="fa fa-times"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

          @if($timeLineCharges->isEmpty())
            <p class="text-center text-muted mt-3">Nenhum lançamento encontrado.</p>
          @endif
        </div>
      </div>

      <!-- Rodapé com paginação à esquerda -->
      <div class="card-footer d-flex justify-content-start">
        {{ $timeLineCharges->appends(request()->query())->links('pagination::bootstrap-4') }}
      </div>

    </div>
  </div>
</div>

  </div>
</div>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = '{{ csrf_token() }}';

    function updateSelectColor(select, value) {
        select.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'text-white', 'text-dark');
        if (value === 'paid') {
            select.classList.add('bg-success', 'text-white');
        } else if (value === 'Invoiced') {
            select.classList.add('bg-warning', 'text-dark');
        } else {
            select.classList.add('bg-danger', 'text-white');
        }
    }

    document.querySelectorAll('.status-selector').forEach(select => {
        // inicializa cor correta
        updateSelectColor(select, select.value);

        select.addEventListener('change', function () {
            const id = this.getAttribute('data-id');
            const newStatus = this.value;

            fetch(`/invoices/update/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status_payment: newStatus
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Erro ao atualizar o status.');
                return response.json();
            })
            .then(data => {
                updateSelectColor(this, newStatus);
            })
            .catch(error => {
                alert('Erro ao atualizar o status: ' + error.message);
            });
        });
    });
});
</script>
