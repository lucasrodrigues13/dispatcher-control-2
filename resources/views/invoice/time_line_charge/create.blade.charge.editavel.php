@extends("layouts.app2")

@section('conteudo')
<div class="container">
    <div class="page-inner">

        {{-- Header --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">Add Time Line Charge</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Time Line Charges</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Add New</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header d-flex align-items-center">
                        <div class="seta-voltar">
                            <a href="{{ route('time_line_charges.index') }}"><i class="fas fa-arrow-left"></i></a>
                        </div>
                        <h4 class="card-title ms-2">Time Line Charge Information</h4>
                    </div>

                    <div class="card-body">

                        {{-- Form para FILTRAR --}}
                        <form id="filter-form" method="GET" action="{{ route('time_line_charges.create') }}" class="mb-4">
                            {{-- Filtros de data --}}
                            <div class="row mb-3 border p-3 rounded">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Date Start</label>
                                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Date End</label>
                                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Carrier <span class="text-danger">*</span></label>
                                    <select id="carrier-select" name="carrier_id" class="form-select" required>
                                        <option value="" disabled selected>Select Carrier</option>
                                        <option value="all" @selected(old('carrier_id', request('carrier_id')) == 'all')>-- All Carriers</option>
                                        @foreach ($carriers as $carrier)
                                            <option value="{{ $carrier->id }}" @selected(old('carrier_id', request('carrier_id')) == $carrier->id)>
                                                {{ $carrier->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Amount Type</label>
                                    <select name="amount_type" class="form-select" required>
                                        <option value="" disabled {{ !request('amount_type') ? 'selected' : '' }}>Select...</option>
                                        <option value="price" @selected(request('amount_type')==='price')>Price</option>
                                        <option value="paid_amount" @selected(request('amount_type')==='paid_amount')>Paid Amount</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                </div>
                            </div>

                            {{-- Checkboxes --}}
                            <div class="row mb-3 border p-3 rounded bg-light">
                                @foreach ([
                                    'actual_delivery_date' => 'Actual Delivery Date',
                                    'actual_pickup_date' => 'Actual Pickup Date',
                                    'creation_date' => 'Creation Date',
                                    'invoice_date' => 'Invoice Date',
                                    'receipt_date' => 'Receipt Date',
                                    'scheduled_pickup_date' => 'Scheduled Pickup Date',
                                    'scheduled_delivery_date' => 'Scheduled Delivery Date'
                                ] as $field => $label)
                                    <div class="col-md-3 col-6 mb-2">
                                        <input type="checkbox" id="filter_{{ $field }}" name="filters[{{ $field }}]" value="1"
                                               @checked(request()->input("filters.$field"))>
                                        <label for="filter_{{ $field }}" class="ms-1">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </form>

                        {{-- Tabela de loads filtrados --}}
                        @if(!empty($loads) && $loads->count())
                            <div class="table-responsive mb-4">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>LOAD ID</th>
                                            <th>CUSTUMER</th>
                                            <th>VEICHLE</th>
                                            <th>PRICE</th>
                                            <!-- <th>Paid Amount</th>
                                            <th>Actual Delivery Date</th>
                                            <th>Actual Pickup Date</th>
                                            <th>Invoice Date</th>
                                            <th>Receipt Date</th>
                                            <th>Scheduled Pickup Date</th>
                                            <th>Scheduled Delivery Date</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($loads as $load)
                                            <tr>
                                                <td>{{ $load->load_id }}</td>
                                                <td>{{ $load->customer_name }}</td>
                                                <td>{{ $load->year_make_model }}</td>
                                                <td>{{ $load->price }}</td>
                                                <!-- <td>{{ $load->paid_amount }}</td>
                                                <td>{{ $load->actual_delivery_date }}</td>
                                                <td>{{ $load->actual_pickup_date }}</td>
                                                <td>{{ $load->invoice_date }}</td>
                                                <td>{{ $load->receipt_date }}</td>
                                                <td>{{ $load->scheduled_pickup_date }}</td>
                                                <td>{{ $load->scheduled_delivery_date }}</td> -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        {{-- Form para SALVAR --}}
                        <form id="save-form">
                            @csrf
                            {{-- Total calculado --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Total Amount</label>
                                    <input type="number" name="total_amount" id="total_amount" class="form-control" readonly value="{{ $totalAmount ?? 0 }}">
                                </div>
                            </div>

                            {{-- Campos TimeLineCharge --}}
                            <div class="row invisible">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dispatcher <span class="text-danger">*</span></label>
                                    <select name="dispatcher_id" class="form-select" required>
                                        @foreach ($dispatchers as $dispatcher)
                                            <option value="{{ $dispatcher->id }}">{{ $dispatcher->user->name ?? $dispatcher->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Botões --}}
                            <div class="row mt-4">
                                <div class="col d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Save Time Line Charge</button>
                                    <button id="open-additional-service" type="button" class="btn btn-success mx-2" data-bs-toggle="modal" data-bs-target="#additionalService">Add Additional Service</button>
                                    <a href="{{ route('time_line_charges.index') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>

                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="additionalService" tabindex="-1" aria-labelledby="additionalServiceLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="additional-service-form" action="{{ route('additional_services.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5 text-dark" id="additionalServiceLabel">Add Additional Service</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          {{-- Form fields --}}
          <div class="mb-3">
            <label for="describe" class="form-label">Description service</label>
            <input type="text" class="form-control" id="describe" name="describe" required>
          </div>

          <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" step="any" class="form-control" id="quantity" name="quantity" required>
          </div>

          <div class="mb-3">
            <label for="value" class="form-label">Unit Value</label>
            <input type="number" step="any" class="form-control" id="value" name="value" required>
          </div>

          <div class="mb-3">
            <label for="total" class="form-label">Total</label>
            <input type="number" step="any" class="form-control" id="total" name="total" readonly>
          </div>

          <!-- <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
              <option value="" disabled selected>Select status</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div> -->

          <!-- <div class="mb-3">
            <label for="carrier_id" class="form-label">Carrier</label>
            <select class="form-select" id="carrier_id" name="carrier_id" required>
              <option value="" disabled selected>Select Carrier</option>
              @foreach($carriers as $carrier)
                <option value="{{ $carrier->id }}">{{ $carrier->user ? $carrier->user->name : $carrier->company_name }}</option>
              @endforeach
            </select>
          </div> -->

          {{-- Tabela PENDING --}}
          <h5 class="mt-4">Pending Services</h5>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Description</th>
                  <th>Quantity</th>
                  <th>Value</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Carrier</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody id="additional-services-table-body">
                <tr>
                  <td><span id="p_describe"></span></td>
                  <td><span id="p_quantity"></span></td>
                  <td><span id="p_value"></span></td>
                  <td><span id="p_total"></span></td>
                  <td><span id="p_status"></span></td>
                  <td><span id="p_carrier_id"></span></td>
                  <td><span id="p_created_at"></span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="charge-now">Charge Now</button>
            <button type="button" class="btn btn-warning mx-2" id="charge-last">Charge Last</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>


{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    // Salva campos do filtro no localStorage
    document.getElementById('filter-form').addEventListener('submit', function () {
        localStorage.setItem('date_start', document.querySelector('input[name="date_start"]').value);
        localStorage.setItem('date_end', document.querySelector('input[name="date_end"]').value);
        localStorage.setItem('amount_type', document.querySelector('select[name="amount_type"]').value);
        localStorage.setItem('carrier_id', document.querySelector('select[name="carrier_id"]').value); // ✅ Adicionado

        let filters = {};
        document.querySelectorAll('input[name^="filters["]:checked').forEach(cb => {
            let name = cb.name.match(/\[(.*?)\]/)[1];
            filters[name] = 1;
        });
        localStorage.setItem('filters', JSON.stringify(filters));
    });

    // Enviar via AJAX no submit do Save-form
    document.getElementById('save-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const carrierId = document.querySelector('select[name="carrier_id"]').value;
        if (!carrierId || carrierId === '') {
            alert('Por favor, selecione um Carrier.');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let payload = {
            _token: token,
            total_amount: document.querySelector('#total_amount').value,
            carrier_id: carrierId,
            dispatcher_id: document.querySelector('select[name="dispatcher_id"]').value,
            date_start: localStorage.getItem('date_start') || '',
            date_end: localStorage.getItem('date_end') || '',
            amount_type: localStorage.getItem('amount_type') || '',
            filters: JSON.parse(localStorage.getItem('filters') || '{}')
        };

        fetch('{{ route('time_line_charges.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        })
        .then(res => {
            if (!res.ok) throw new Error('Erro ao salvar');
            return res.json();
        })
        .then(data => {
            alert(data.message || 'Time Line Charge created successfully.');
            window.location.href = '{{ route('time_line_charges.index') }}';
        })
        .catch(err => {
            alert('Erro ao salvar. Veja o console para detalhes.');
            console.error(err);
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const carrierSelect = document.getElementById('carrier-select');
        const amountTypeSelect = document.querySelector('select[name="amount_type"]');
        const allCheckboxes = document.querySelectorAll('input[type="checkbox"][name^="filters["]');

        // Função para resetar os campos
        function resetFields() {
            allCheckboxes.forEach(checkbox => checkbox.checked = false);
            if (amountTypeSelect) {
                amountTypeSelect.value = "";
            }
        }

        carrierSelect.addEventListener('change', function () {
            const carrierId = this.value;

            if (carrierId && carrierId !== '#') {
                fetch(`/time_line_charges/get-charges-setup/${carrierId}`)
                    .then(response => {
                        if (!response.ok) throw response;
                        return response.json();
                    })
                    .then(data => {
                        if (!data.data) {
                            resetFields(); // Se resposta for nula, reseta os campos
                            return;
                        }

                        const setup = data.data;

                        // 1. Preencher os checkboxes
                        const filters = setup.charges_setup_array || [];
                        allCheckboxes.forEach(checkbox => {
                            const name = checkbox.name.match(/\[([^\]]+)\]/)[1];
                            checkbox.checked = filters.includes(name);
                        });

                        // 2. Preencher o campo amount_type
                        if (amountTypeSelect) {
                            amountTypeSelect.value = setup.price === 'paid amount' ? 'paid_amount' : 'price';
                        }
                    })
                    .catch(async error => {
                        if (error.status === 404) {
                            resetFields(); // Se 404, limpa os campos
                        } else {
                            console.error('Erro ao buscar setup:', await error.text());
                        }
                    });
            }
        });
    });
</script>

<!-- Salvar serviços adicionais -->
<script>
$(document).ready(function () {

  // Função comum para enviar os dados com o tipo de ação
  function submitAdditionalService(actionType) {
    let formData = $('#additional-service-form').serializeArray();

    // Pega carrier_id do localStorage
    const carrierId = localStorage.getItem('carrier_id');
    formData.push({ name: 'carrier_id', value: carrierId });

    // Passa a ação no payload (se precisar usar depois)
    formData.push({ name: 'action_type', value: actionType });

    $.ajax({
      url: '{{ route("additional_services.store") }}',
      type: 'POST',
      data: $.param(formData),
      dataType: 'json',

      success: function (response) {
        if (response.success) {
          alert(response.message);
          $('#additionalService').modal('hide');
          $('#additional-service-form')[0].reset();
        }
      },

      error: function (xhr) {
        if (xhr.status === 422) {
          let errors = xhr.responseJSON.errors;
          let messages = Object.values(errors).map(msgArray => msgArray.join(', ')).join('\n');
          alert("Erros de validação:\n" + messages);
        } else {
          alert("Erro ao salvar. Tente novamente.");
        }
      }
    });
  }

  // Clique no botão "Charge Now"
  $('#charge-now').on('click', function () {
    submitAdditionalService('now');

  });

  // Clique no botão "Charge Last"
  $('#charge-last').on('click', function () {
    submitAdditionalService('last');
  });

});
</script>

<!-- Calcular total de serviços adicionais -->
<script>
  $(document).ready(function () {
    function calcularTotal() {
      const quantity = parseFloat($('#quantity').val()) || 0;
      const value = parseFloat($('#value').val()) || 0;
      const total = quantity * value;

      $('#total').val(total.toFixed(2));
    }

    // Atualiza ao digitar
    $('#quantity, #value').on('input', calcularTotal);
  });
</script>

<!-- Listar serviços adicionais -->
<script>
$(document).ready(function () {
  $('#open-additional-service').on('click', function () {
    $.ajax({
      url: '{{ route("additional_services.index") }}',
      type: 'GET',
      dataType: 'json',

      success: function (response) {
        if (response.success) {
          let tbody = $('#additional-services-table-body');
          tbody.empty(); // Limpa conteúdo anterior

          response.data.forEach(service => {
            tbody.append(`
              <tr>
                <td>${service.describe}</td>
                <td>${service.quantity}</td>
                <td>${service.value}</td>
                <td>${service.total}</td>
                <td>${service.status}</td>
                <td>${service.carrier?.user?.name || '-'}</td>
                <td>${service.created_at}</td>
              </tr>
            `);
          });
        }
      },

      error: function (xhr) {
        alert("Erro ao carregar serviços adicionais.");
        console.error(xhr);
      }
    });
  });
});
</script>

@endsection
