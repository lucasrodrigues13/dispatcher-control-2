@extends("layouts.app2")

@section('conteudo')

<style>
    .readonly-select {
        pointer-events: none;
        background-color: #e9ecef;
    }

    .readonly-wrapper {
        pointer-events: none; /* bloqueia toda a div */
    }

    .readonly-wrapper input,
    .readonly-wrapper label {
        pointer-events: none; /* impede qualquer clique */
        cursor: not-allowed;
    }

    #charge-setup {
      background: #f1f1f1 !important;
      opacity: 0.6 !important;
    }
</style>

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
                        <h4 class="card-title ms-2">Edit Time Line Charge</h4>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('time_line_charges.update', $timeLineCharge->id) }}">
                            @csrf
                            @method('PUT')

                            {{-- Filtros de data --}}
                            <div class="row mb-3 border p-3 rounded">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Date Start</label>
                                    <input type="date" name="date_start" class="form-control"
                                        value="{{ $timeLineCharge->date_start }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Date End</label>
                                    <input type="date" name="date_end" class="form-control"
                                        value="{{ $timeLineCharge->date_end }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Carrier <span class="text-danger">*</span></label>
                                    <select name="carrier_id" class="form-select" required>
                                        <option value="" disabled>Select Carrier</option>
                                        @foreach ($carriers as $carrier)
                                            <option value="{{ $carrier->id }}"
                                                @selected($carrier->id == $timeLineCharge->carrier_id)>
                                                {{ $carrier->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Opções adicionais --}}
                            <div class="row mb-3 border p-3 rounded bg-light">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Charge Setup</label>
                                    <select name="amount_type" class="form-select" required>
                                        <option value="" disabled>Select...</option>
                                        <option value="price" @selected($timeLineCharge->amount_type === 'price')>Price</option>
                                        <option value="paid_amount" @selected($timeLineCharge->amount_type === 'paid_amount')>Paid Amount</option>
                                    </select>
                                </div>

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
                                        <input type="checkbox"
                                            id="filter_{{ $field }}"
                                            name="filters[{{ $field }}]"
                                            value="1"
                                            @checked(isset($arrayTypeDates[$field]) && $arrayTypeDates[$field])>
                                        <label for="filter_{{ $field }}" class="ms-1">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Tabela de Loads --}}
                            @if($filters->count())
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>LOAD ID</th>
                                                <th>CUSTOMER</th>
                                                <th>VEHICLE</th>
                                                <th>PRICE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($filters as $load)
                                                <tr>
                                                    <td>{{ $load->load_id }}</td>
                                                    <td>{{ $load->customer_name }}</td>
                                                    <td>{{ $load->year_make_model }}</td>
                                                    <td>{{ $load->price }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            {{-- Total --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Total Amount</label>
                                    <input type="number" name="total_amount" class="form-control" readonly value="{{ $totalAmount }}">
                                </div>
                            </div>

                            {{-- Dispatcher --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dispatcher</label>
                                    <select name="dispatcher_id" class="form-select" required>
                                        @foreach ($dispatchers as $dispatcher)
                                            <option value="{{ $dispatcher->id }}"
                                                @selected($dispatcher->id == $timeLineCharge->dispatcher_id)>
                                                {{ $dispatcher->user->name ?? $dispatcher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Botões --}}
                            <div class="row mt-4">
                                <div class="col d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                    <a href="{{ route('time_line_charges.index') }}" class="btn btn-secondary ms-2">Cancel</a>
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
            <label for="describe" class="form-label">Service Description</label>
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

          {{-- Campos de Parcelamento --}}
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="is_installment" name="is_installment" value="1">
              <label class="form-check-label" for="is_installment">
                Enable Installment Payment
              </label>
            </div>
          </div>

          <div id="installment-fields" class="d-none">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="installment_type" class="form-label">Period Type</label>
                  <select class="form-select" id="installment_type" name="installment_type">
                    <option value="">Select period</option>
                    <option value="weeks">Weeks</option>
                    <option value="months">Months</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="installment_count" class="form-label">Number of Installments</label>
                  <input type="number" class="form-control" id="installment_count" name="installment_count" min="2" max="12">
                </div>
              </div>
            </div>
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
                  <th>Installment</th>
                  <th>Created At</th>
                  <th>Actions</th>
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
    document.getElementById('save-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const carrierId = document.querySelector('select[name="carrier_id"]').value;
        if (!carrierId || carrierId === '') {
            alert('Por favor, selecione um Carrier.');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // 🔹 Extrair todos os load_id da primeira coluna da tabela (como string)
        let loadIds = [];
        document.querySelectorAll('table tbody tr').forEach(row => {
            const loadId = row.cells[0]?.textContent.trim();
            if (loadId) {
                loadIds.push(loadId); // mantém como string
            }
        });

        const payload = {
            _token: token,
            total_amount: document.querySelector('#total_amount')?.value || '',
            carrier_id: carrierId,
            dispatcher_id: document.querySelector('select[name="dispatcher_id"]')?.value || '',
            date_start: localStorage.getItem('date_start') || '',
            date_end: localStorage.getItem('date_end') || '',
            amount_type: localStorage.getItem('amount_type') || '',
            filters: JSON.parse(localStorage.getItem('filters') || '{}'),
            load_ids: loadIds // ✅ Enviando os load_ids como strings
        };

        fetch('{{ route('time_line_charges.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();

            if (!res.ok) {
                // Se houver mensagem do servidor, exiba ela
                if (data.message) {
                    alert(data.message);
                } else {
                    alert('Erro ao salvar.');
                }
                throw new Error(data.message || 'Erro ao salvar');
            }

            return data;
        })
        .then(data => {
            alert(data.message || 'Time Line Charge created successfully.');
            window.location.href = '{{ route('time_line_charges.index') }}';
        })
        .catch(err => {
            console.error("Erro:", err.message);
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const carrierSelect = document.getElementById('carrier-select');
        const amountTypeSelect = document.querySelector('select[name="amount_type"]');
        const allCheckboxes = document.querySelectorAll('input[type="checkbox"][name^="filters["]');

        // ⭐ INICIALIZAR localStorage se já houver um carrier selecionado
        if (carrierSelect && carrierSelect.value && carrierSelect.value !== '' && carrierSelect.value !== '#') {
            localStorage.setItem('carrier_id', carrierSelect.value);
        }

        // Função para resetar os campos
        function resetFields() {
            allCheckboxes.forEach(checkbox => checkbox.checked = false);
            if (amountTypeSelect) {
                amountTypeSelect.value = "";
            }
        }

        carrierSelect.addEventListener('change', function () {
            const carrierId = this.value;

            // ⭐ ARMAZENAR carrier_id no localStorage para uso nos serviços adicionais
            if (carrierId && carrierId !== '' && carrierId !== '#') {
                localStorage.setItem('carrier_id', carrierId);
            } else {
                localStorage.removeItem('carrier_id');
            }

            if (carrierId && carrierId !== '#') {
                fetch(`/time_line_charges/get-charges-setup/${carrierId}`)
                    .then(response => {
                        if (!response.ok) throw response;

                        return response.json();
                    })
                    .then(data => {
                        if (!data.data) {
                            // alert("User charge setup not found")
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
                            if (!isNaN(Number(carrierId))) {
                              alert("This user has no charge setup")
                            }
                            
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
          alert("Validation errors:\n" + messages);
        } else {
          alert("Error saving. Please try again.");
        }
      }
    });
  }

  // Clique no botão "Charge Now"
  $('#charge-now').on('click', function () {
    submitAdditionalService('now');

  });

  // Botão 'Charge Last' removido conforme solicitado

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

    // Controlar exibição dos campos de parcelamento
    $('#is_installment').on('change', function() {
      if ($(this).is(':checked')) {
        $('#installment-fields').removeClass('d-none');
        $('#installment_type').attr('required', true);
        $('#installment_count').attr('required', true);
      } else {
        $('#installment-fields').addClass('d-none');
        $('#installment_type').removeAttr('required').val('');
        $('#installment_count').removeAttr('required').val('');
      }
    });
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
            // Format installment info
            let installmentInfo = '-';
            if (service.is_installment) {
              installmentInfo = `${service.installment_count} ${service.installment_type}`;
            }
            
            tbody.append(`
              <tr>
                <td>${service.describe}</td>
                <td>${service.quantity}</td>
                <td>${service.value}</td>
                <td>${service.total}</td>
                <td>${service.status}</td>
                <td>${service.carrier?.user?.name || '-'}</td>
                <td>${installmentInfo}</td>
                <td>${service.created_at}</td>
                <td>
                  <button type="button" class="btn btn-danger btn-sm" onclick="deleteService(${service.id})">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                </td>
              </tr>
            `);
          });
        }
      },

      error: function (xhr) {
        alert("Error loading additional services.");
        console.error(xhr);
      }
    });
  });
});
</script>

<script>
    document.querySelectorAll('.readonly-checkbox').forEach(cb => {
        cb.addEventListener('click', e => {
            e.preventDefault(); // impede alteração
        });
    });
</script>

<script>
    document.getElementById('carrier-select').addEventListener('change', function () {
        const selectedValue = this.value;

        if (!isNaN(Number(selectedValue))) {
            document.getElementById('charge-setup').classList.remove("d-none")
        } else {
            document.getElementById('charge-setup').classList.add("d-none")
        }

        // Aqui você pode adicionar lógica com base no tipo de valor
    });

// Function to delete service - Global scope
function deleteService(serviceId) {
  if (confirm('Are you sure you want to delete this service?')) {
    $.ajax({
      url: `/additional_services/${serviceId}`,
      type: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        if (response.success) {
          alert(response.message);
          // Reload the services list
          $('#open-additional-service').click();
        }
      },
      error: function(xhr) {
        alert('Error deleting service. Please try again.');
        console.error(xhr);
      }
    });
  }
}
</script>



@endsection
