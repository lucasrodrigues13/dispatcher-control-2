@extends('layouts.app')

@section('conteudo')

<style>
  .no-wrap-table td,
  .no-wrap-table th {
    white-space: nowrap;
  }


    /* Adicione isso no seu arquivo CSS */
  .table-responsive {
      position: relative;
      max-height: 400px; /* Ajuste esta altura conforme necessário */
      overflow: auto;
  }

  .no-wrap-table thead th {
      position: sticky;
      top: 0;
      z-index: 10;
      background-color: #343a40; /* Cor do table-dark */
      color: white;
  }

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

  .see-hover {
    opacity: 0.7;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .see-hover:hover {
    opacity: 1;
    /* text-decoration: underline; */
  }
</style>


<div class="container">
  <div class="page-inner">
    <div class="page-header">
      <h3 class="fw-bold mb-3">Loads</h3>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Loads</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">List</a></li>
      </ul>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card">

          <!-- Cabeçalho com filtros (se houver) e possibilidade de adicionar novo Load -->
          <div class="card-header">
            <div class="row">
              <div class="col-md-4 my-2">
                <form method="GET" action="{{ route('loads.index') }}">
                  <div class="input-group">
                    <!-- Campo de busca -->
                    <input name="search" type="text"
                          value="{{ request('search') }}"
                          placeholder="Search Loads..."
                          class="form-control"
                          data-bs-toggle="modal" data-bs-target="#searchData" />

                    <!-- Botões alinhados -->
                    <button type="submit" class="btn btn-outline-secondary" title="Search">
                      <i class="fa fa-search"></i>
                    </button>

                    <button type="button" class="ps-4 btn btn-outline-secondary mx-1" title="Filter" data-bs-toggle="modal" data-bs-target="#applyFilter">
                      <i class="fa fa-filter"></i>
                    </button>

                    <!-- Dropdown com botão de pontinhos -->
                    <div class="dropdown">
                      <button class="btn btn-outline-secondary dropdown-toggle py-3" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Menu">
                        <i class="fa fa-ellipsis-v"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
                        <li class="mb-1">
                          <a class="dropdown-item p-3" href="/loads/mode"  id="toggle-mode-btn">Change View Mode</a>
                        </li>
                        <li>
                          <a href="#" id="delete-all-loads" class="p-3 dropdown-item text-danger">Delete All Loads</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                </form>
              </div>


              <div class="col my-2 d-flex justify-content-md-end align-items-center gap-2">
                <a href="#" id="delete-selected" class="btn btn-danger btn-sm">
                  <i class="fa fa-trash"></i>
                  <span class="d-none d-md-inline">Delete</span>
                </a>
                <a href="#" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#selectColums">
                  <i class="fa fa-eye"></i> 
                  <span class="d-none d-md-inline">
                    Show/Hide Columns
                  </span>
                </a>
                <a href="#" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importLoadsModal">
                  <i class="fa fa-upload"></i> 
                  <span class="d-none d-md-inline">
                    Import
                  </span>
                </a>
                <a href="{{ route('loads.create') }}" class="btn btn-primary btn-sm">
                  <i class="fa fa-plus"></i> 
                  <span class="d-none d-md-inline">
                    Add Load
                  </span>
                </a>
              </div>
            </div>
          </div>

          <!-- Tabela de Loads -->
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-bordered no-wrap-table">
                <thead class="table-dark">
                  <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>Load ID</th>
                    <th>Internal Load ID</th>
                    <th>Creation Date</th>
                    <th>Dispatcher</th>
                    <th>Employee</th>
                    <th>Trip</th>
                    <th>Year, Make, Model</th>
                    <th>VIN</th>
                    <th>Lot Number</th>
                    <th>Has Terminal</th>
                    <th>Dispatched to Carrier</th>
                    <th>Pickup Name</th>
                    <th>Pickup Address</th>
                    <th>Pickup City</th>
                    <th>Pickup State</th>
                    <th>Pickup ZIP</th>
                    <th>Scheduled Pickup Date</th>
                    <th>Pickup Phone</th>
                    <th>Pickup Mobile</th>
                    <th>Actual Pickup Date</th>
                    <th>Buyer Number</th>
                    <th>Pickup Notes</th>
                    <th>Delivery Name</th>
                    <th>Delivery Address</th>
                    <th>Delivery City</th>
                    <th>Delivery State</th>
                    <th>Delivery ZIP</th>
                    <th>Scheduled Delivery Date</th>
                    <th>Actual Delivery Date</th>
                    <th>Delivery Phone</th>
                    <th>Delivery Mobile</th>
                    <th>Delivery Notes</th>
                    <th>Shipper Name</th>
                    <th>Shipper Phone</th>
                    <th>Price ($)</th>
                    <th>Expenses ($)</th>
                    <th>Broker Fee ($)</th>
                    <th>Driver Pay ($)</th>
                    <th>Payment Method</th>
                    <th>Paid Amount ($)</th>
                    <th>Paid Method</th>
                    <th>Reference Number</th>
                    <th>Receipt Date</th>
                    <th>Payment Terms</th>
                    <th>Payment Notes</th>
                    <th>Payment Status</th>
                    <th>Invoice Number</th>
                    <th>Invoice Notes</th>
                    <th>Invoice Date</th>
                    <th>Driver</th>
                    <th>Invoiced Fee</th>
                    <th>Dispatcher</th>
                    <th>Carrier</th>
                    <th class="text-center actions">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @if(!empty($loads))
                    @foreach($loads as $load)
                      <tr>
                        <td><input type="checkbox" class="load-checkbox" value="{{ $load->id }}"></td>
                        <td>{{ $load->load_id }}</td>
                        <td>{{ $load->internal_load_id }}</td>
                        <td>{{ $load->creation_date }}</td>
                        <td>{{ $load->dispatcher }}</td>
                        <td class="text-center">
                            <select class="form-select form-select-sm select-employee" data-load-id="{{ $load->id }}" style="width: 150px;">
                                <option value="">Unassigned</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ $load->employee_id == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>{{ $load->trip }}</td>
                        <td>{{ $load->year_make_model }}</td>
                        <td>{{ $load->vin }}</td>
                        <td>{{ $load->lot_number }}</td>
                        <td>{{ $load->has_terminal }}</td>
                        <td>{{ $load->dispatched_to_carrier }}</td>
                        <td>{{ $load->pickup_name }}</td>
                        <td>{{ $load->pickup_address }}</td>
                        <td>{{ $load->pickup_city }}</td>
                        <td>{{ $load->pickup_state }}</td>
                        <td>{{ $load->pickup_zip }}</td>
                        <td>{{ $load->scheduled_pickup_date }}</td>
                        <td>{{ $load->pickup_phone }}</td>
                        <td>{{ $load->pickup_mobile }}</td>
                        <td>{{ $load->actual_pickup_date }}</td>
                        <td>{{ $load->buyer_number }}</td>
                        <td class="truncate-cell">{{ $load->pickup_notes }}</td>
                        <td>{{ $load->delivery_name }}</td>
                        <td>{{ $load->delivery_address }}</td>
                        <td>{{ $load->delivery_city }}</td>
                        <td>{{ $load->delivery_state }}</td>
                        <td>{{ $load->delivery_zip }}</td>
                        <td>{{ $load->scheduled_delivery_date }}</td>
                        <td>{{ $load->actual_delivery_date }}</td>
                        <td>{{ $load->delivery_phone }}</td>
                        <td>{{ $load->delivery_mobile }}</td>
                        <td class="truncate-cell">{{ $load->delivery_notes }}</td>
                        <td>{{ $load->shipper_name }}</td>
                        <td>{{ $load->shipper_phone }}</td>
                        <td>{{ $load->price }}</td>
                        <td>{{ $load->expenses }}</td>
                        <td>{{ $load->broker_fee }}</td>
                        <td>{{ $load->driver_pay }}</td>
                        <td>{{ $load->payment_method }}</td>
                        <td>{{ $load->paid_amount }}</td>
                        <td>{{ $load->paid_method }}</td>
                        <td>{{ $load->reference_number }}</td>
                        <td>{{ $load->receipt_date }}</td>
                        <td>{{ $load->payment_terms }}</td>
                        <td>{{ $load->payment_notes }}</td>
                        <td>{{ $load->payment_status }}</td>
                        <td>{{ $load->invoice_number }}</td>
                        <td>{{ $load->invoice_notes }}</td>
                        <td>{{ $load->invoice_date }}</td>
                        <td>{{ $load->driver }}</td>
                        <td>{{ $load->invoiced_fee }}</td>
                        <td>{{ $load->dispatcher_id }}</td>
                        <td>{{ $load->carrier_id }}</td>
                        <td class="text-center actions">
                          <div class="form-button-action">
                            <a href="{{ route('loads.edit', $load->id) }}" class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip" title="Edit">
                              <i class="fa fa-edit"></i>
                            </a>
                            <form action="{{ route('loads.destroy', $load->id) }}" method="POST" style="display:inline-block">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-link btn-danger" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this broker?')">
                                <i class="fa fa-times"></i>
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  @else
                    <tr>
                      <td colspan="56" class="text-center text-muted">
                        No loders found.
                      </td>
                    </tr>
                  @endif
                </tbody>
              </table>

            </div>
          </div>
        </div>
         {{-- Paginação --}}
              @if(method_exists($loads, 'links'))
                <div class="mt-3">
                  {{ $loads->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                </div>
              @endif
      </div>
    </div>
  </div>  
</div>

<!-- Modal selectColums -->
<!-- Modal Show/Hide Columns-->
<div class="modal fade" id="selectColums" tabindex="-1" aria-labelledby="importLoadsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="importLoadsModalLabel">Select Colums</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

        <div class="modal-body">
          <div class="mb-3">
            <input type="text" id="searchColumnsInput" class="form-control" placeholder="Search columns...">
          </div>
          <div class="col-12">
            <label>
              <input type="checkbox" id="toggle-all-columns" checked>
              Show/Hiden All Columns
            </label>
          </div>
          <div>
            <hr>
          </div>

          <div class="row" style="max-height: 300px; overflow: auto;">
            <!-- Checkbox Geral -->
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Actual Delivery Date" checked> Actual Delivery Date</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Actual Pickup Date" checked> Actual Pickup Date</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Actions" checked> Actions</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Buyer Number" checked> Buyer Number</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Creation Date" checked> Creation Date</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Delivery Address" checked> Delivery Address</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Delivery City" checked> Delivery City</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Delivery Mobile" checked> Delivery Mobile</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Delivery Name" checked> Delivery Name</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Delivery Notes" checked> Delivery Notes</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Delivery Phone" checked> Delivery Phone</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Delivery State" checked> Delivery State</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Delivery ZIP" checked> Delivery ZIP</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Dispatched to Carrier" checked> Dispatched to Carrier</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Dispatcher" checked> Dispatcher</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Broker Fee ($)" checked> Broker Fee ($)</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Driver Pay ($)" checked> Driver Pay ($)</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Driver" checked> Driver</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Expenses ($)" checked> Expenses ($)</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Has Terminal" checked> Has Terminal</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Internal Load ID" checked> Internal Load ID</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Invoice Date" checked> Invoice Date</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Invoice Notes" checked> Invoice Notes</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Invoice Number" checked> Invoice Number</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Load ID" checked> Load ID</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Lot Number" checked> Lot Number</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Paid Amount ($)" checked> Paid Amount ($)</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Paid Method" checked> Paid Method</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Payment Notes" checked> Payment Notes</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Payment Status" checked> Payment Status</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Payment Terms" checked> Payment Terms</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Payment Method" checked> Payment Method</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Price ($)" checked> Price ($)</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Pickup Address" checked> Pickup Address</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Pickup City" checked> Pickup City</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Pickup Mobile" checked> Pickup Mobile</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Pickup Name" checked> Pickup Name</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Pickup Notes" checked> Pickup Notes</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Pickup Phone" checked> Pickup Phone</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Pickup State" checked> Pickup State</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Pickup ZIP" checked> Pickup ZIP</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Receipt Date" checked> Receipt Date</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Reference Number" checked> Reference Number</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Scheduled Pickup Date" checked> Scheduled Pickup Date</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Scheduled Delivery Date" checked> Scheduled Delivery Date</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Shipper Name" checked> Shipper Name</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Shipper Phone" checked> Shipper Phone</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Trip" checked> Trip</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="VIN" checked> VIN</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Year, Make, Model" checked> Year, Make, Model</label></div>
            <div class="col-md-6 mb-4"><label><input type="checkbox" class="toggle-column" data-column="Invoiced Fee" checked> Invoiced Fee</label></div>

          </div>
        </div>

      <div class="modal-footer">
        <!-- <button type="submit" class="btn btn-primary">See Colums</button> -->
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Importat Excel -->
<div class="modal fade" id="importLoadsModal" tabindex="-1" aria-labelledby="importLoadsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="importLoadsModalLabel">Import Excel Loads</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        {{-- Container mais enxuto, sem padding extra --}}
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        @if(session('success'))
          <div class="alert alert-success">
            {{ session('success') }}
          </div>
        @endif

        <form action="{{ route('loads.import') }}" method="POST" enctype="multipart/form-data">
          @csrf

          {{-- Grupo de upload --}}
          <div class="row">
            <!-- Select Dispatcher -->
            <div class="col-md-6 mb-4">
              <label for="dispatcher_id" class="form-label fw-semibold">Dispatcher</label>
              <select name="dispatcher_id" id="dispatcher_id" class="form-select" required>
                <option value="" disabled selected>Select Dispatcher</option>
                @foreach($dispatchers as $item)
                  <option value="{{ $item->id }}">{{ $item->user->name }}</option>
                @endforeach
              </select>
            </div>

            <!-- Select Employee -->
            <div class="col-md-6 mb-4">
              <label for="employee_id" class="form-label fw-semibold">Add Employee</label>
              <select name="employee_id" id="employee_id" class="form-select">
                <option value="">Select Employee</option>
              </select>
            </div>
            <div class="col-md-12 mb-4">
              <label for="arquivo" class="form-label fw-semibold">Carrier</label>
              <select name="carrier_id" id="" class="form-select" required>
                <option value="" disabled selected>Select Carrier</option>
                @foreach($carriers as $item)
                  <option value="{{ $item->id }}">
                    {{ $item->user->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="mb-4">
            <label for="arquivo" class="form-label fw-semibold">Select Excel archive</label>
            <input 
              class="form-control form-control-lg" 
              type="file" 
              id="arquivo" 
              name="arquivo" 
              accept=".xls,.xlsx" 
              required
            >
            <div class="form-text">Allow Formats: .xlsx, .xls</div>
          </div>

          {{-- Botões de ação --}}
          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('loads.index') }}" class="btn btn-outline-secondary">See Registers</a>
            <a href="{{ route('loads.create') }}" class="btn btn-outline-primary">Manual Register</a>
            <button type="submit" class="btn btn-success">Import</button>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Apply Filter -->
<div class="modal fade" id="applyFilter" tabindex="-1" aria-labelledby="importLoadsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="importLoadsModalLabel">Apply Filter</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('loads.index') }}" method="GET" class="mb-4">
          <div class="row g-2">
            <div class="col-md-3">
              <input type="text" name="load_id" class="form-control" placeholder="Load ID" value="{{ request('load_id') }}">
            </div>
            <div class="col-md-3">
              <input type="text" name="internal_load_id" class="form-control" placeholder="Internal Load ID" value="{{ request('internal_load_id') }}">
            </div>

            <!-- aquil 1 -->
            <!-- Select Dispatcher -->
            <div class="col-md-3 mb-4">
              <select name="dispatcher_id" class="form-select">
                <option value="" selected>Filter Dispatcher</option>
                @foreach($dispatchers as $item)
                  <option value="{{ $item->id }}">{{ $item->user->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3 mb-4">
              <select name="carrier_id" class="form-select">
                <option value="" selected>Filter Carrier</option>
                @foreach($carriers as $item)
                  <option value="{{ $item->id }}">
                    {{ $item->company_name }}
                  </option>
                @endforeach
              </select>
            </div>


            <div class="col-md-3">
              <input type="text" name="vin" class="form-control" placeholder="VIN" value="{{ request('vin') }}">
            </div>
            <div class="col-md-3">
              <input type="text" name="pickup_city" class="form-control" placeholder="Pickup City" value="{{ request('pickup_city') }}">
            </div>
            <div class="col-md-3">
              <input type="text" name="delivery_city" class="form-control" placeholder="Delivery City" value="{{ request('delivery_city') }}">
            </div>
            <div class="col-md-3">
              <input type="date" name="scheduled_pickup_date" class="form-control" placeholder="Scheduled Pickup Date" value="{{ request('scheduled_pickup_date') }}">
            </div>
            <div class="col-md-3">
              <input type="text" name="driver" class="form-control" placeholder="Driver" value="{{ request('driver') }}">
            </div>
            <div class="col-md-12 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">Filter</button>
              <a href="{{ route('loads.index') }}" class="btn btn-secondary ms-2">Clear</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Where do your search -->
<div class="modal fade" id="searchData" tabindex="-1" aria-labelledby="importLoadsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="importLoadsModalLabel">Search Data</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="GET" action="{{ route('loads.search') }}" class="mb-4">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Search in Field</label>
              <select name="search_field" class="form-select" style="height: 42px;">
                <option value="">-- Select Field --</option>
                <option value="load_id" {{ request('search_field') == 'load_id' ? 'selected' : '' }}>Load ID</option>
                <option value="internal_load_id" {{ request('search_field') == 'internal_load_id' ? 'selected' : '' }}>Internal Load ID</option>
                <option value="dispatcher" {{ request('search_field') == 'dispatcher' ? 'selected' : '' }}>Dispatcher</option>
                <option value="vin" {{ request('search_field') == 'vin' ? 'selected' : '' }}>VIN</option>
                <option value="pickup_city" {{ request('search_field') == 'pickup_city' ? 'selected' : '' }}>Pickup City</option>
                <option value="delivery_city" {{ request('search_field') == 'delivery_city' ? 'selected' : '' }}>Delivery City</option>
                <option value="driver" {{ request('search_field') == 'driver' ? 'selected' : '' }}>Driver</option>
                <!-- Adicione outros campos relevantes -->
              </select>
            </div>
    
            <div class="col-md-5">
              <label class="form-label">Search Value</label>
              <input name="search" type="text" value="{{ request('search') }}" placeholder="Enter search term..." class="form-control" />
            </div>

            <div class="col-md-4 d-flex align-items-end justify-content-end">
              <button type="submit" class="btn btn-primary">Find</button>
              <a href="{{ route('loads.index') }}" class="btn btn-secondary ms-2">Clear</a>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>


<style>
  .hidden {
    display: none !important;
  }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.getElementById('delete-all-loads').addEventListener('click', function (e) {
    e.preventDefault();

    if (!confirm('Tem certeza que deseja excluir todas as cargas?')) return;

    fetch("{{ route('loads.destroyAll') }}", {
        method: "DELETE",
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Erro ao excluir');
        return response.json();
    })
    .then(data => {
        alert(data.message);
        // Recarrega a tabela ou redireciona
        location.reload(); 
    })
    .catch(error => {
        alert('Erro ao excluir cargas');
        console.error(error);
    });
});
</script>


<script>
  document.addEventListener("DOMContentLoaded", function () {
    const checkboxes = document.querySelectorAll(".toggle-column");
    const toggleAll = document.getElementById("toggle-all-columns");

    function getColumnIndexByName(columnName) {
      const headerCells = document.querySelectorAll("table thead th");
      for (let i = 0; i < headerCells.length; i++) {
        if (headerCells[i].textContent.trim() === columnName.trim()) {
          return i;
        }
      }
      return -1;
    }

    function toggleColumnByName(columnName, show) {
      const colIndex = getColumnIndexByName(columnName);
      if (colIndex === -1) return;
      const table = document.querySelector("table");
      const rows = table.querySelectorAll("tr");
      rows.forEach(row => {
        const cell = row.cells[colIndex];
        if (cell) {
          cell.classList.toggle("hidden", !show);
        }
      });
    }

    function toggleActionsColumn() {
      const actionsColIndex = Array.from(document.querySelectorAll("table thead th"))
        .findIndex(th => th.classList.contains("actions"));
      if (actionsColIndex === -1) return;

      const showActions = Array.from(checkboxes).some(cb => cb.checked);

      const rows = document.querySelectorAll("table tr");
      rows.forEach(row => {
        const cell = row.cells[actionsColIndex];
        if (cell) {
          cell.classList.toggle("hidden", !showActions);
        }
      });
    }

    checkboxes.forEach(checkbox => {
      checkbox.addEventListener("change", () => {
        const columnName = checkbox.dataset.column;
        toggleColumnByName(columnName, checkbox.checked);

        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        toggleAll.checked = allChecked;

        toggleActionsColumn(); // verificar se deve mostrar/esconder coluna "actions"
      });
    });

    toggleAll.addEventListener("change", () => {
      const show = toggleAll.checked;
      checkboxes.forEach(cb => {
        cb.checked = show;
        const columnName = cb.dataset.column;
        toggleColumnByName(columnName, show);
      });

      toggleActionsColumn();
    });

    // Executar ao carregar para garantir consistência inicial
    toggleActionsColumn();
  });
</script>



<script>
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".truncate-cell").forEach(function (cell) {
      cell.addEventListener("click", function () {
        this.classList.toggle("expanded");
      });
    });
  });
</script>

<!-- Pesquisa dinamica -->
<script>
  document.getElementById('searchColumnsInput').addEventListener('input', function () {
    const searchTerm = this.value.toLowerCase();
    const checkboxes = document.querySelectorAll('#selectColums .toggle-column');

    checkboxes.forEach(function (checkbox) {
      const label = checkbox.closest('label');
      const container = checkbox.closest('.col-md-6');

      if (label.textContent.toLowerCase().includes(searchTerm)) {
        container.style.display = 'block';
      } else {
        container.style.display = 'none';
      }
    });
  });
</script>

<!-- Script de seleção e exclusão -->
<script>
  // Checkbox mestre
  document.getElementById('select-all').addEventListener('change', function () {
    document.querySelectorAll('.load-checkbox').forEach(cb => cb.checked = this.checked);
  });

  // Botão de exclusão
  document.getElementById('delete-selected').addEventListener('click', function () {
    const ids = Array.from(document.querySelectorAll('.load-checkbox:checked')).map(cb => cb.value);

    if (ids.length === 0) {
      alert('Selecione pelo menos um registro.');
      return;
    }

    if (!confirm('Are you sure you want to delete the selected records?')) return;

    fetch("{{ route('loads.apagar_varios') }}", {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ ids })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // alert('Registros apagados com sucesso!');
        console.log("Registros apagados com sucesso!")
        location.reload();
      } else {
        alert('Erro ao apagar registros.');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Erro de comunicação com o servidor.');
    });
  });
</script>

<script>
  document.getElementById('toggle-mode-btn').addEventListener('click', function(event) {
    event.preventDefault();  // impede a navegação imediata

    const ok = confirm('Do you really want to change the view mode?');
    if (ok) {
      // Se confirmar, navega para a URL do href
      window.location.href = this.href;
    }
    // Senão, não faz nada
  });
</script>

<!-- Buscar employee -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const dispatcherSelect = document.getElementById('dispatcher_id');
    const employeeSelect = document.getElementById('employee_id');

    dispatcherSelect.addEventListener('change', function () {
      const dispatcherId = this.value;

      if (!dispatcherId) return;

      fetch(`/employees/${dispatcherId}/getEmployee`)
        .then(response => response.json())
        .then(data => {
          // Limpa opções anteriores
          employeeSelect.innerHTML = '<option value="" selected>Select Employee</option>';

          // Popula com os dados recebidos
          data.forEach(employee => {
            const option = document.createElement('option');
            option.value = employee.id; // ou employee.user_id, dependendo do que você precisa salvar
            option.textContent = employee.user.name;
            employeeSelect.appendChild(option);
          });
        })
        .catch(error => {
          console.error('Erro ao carregar os funcionários:', error);
          employeeSelect.innerHTML = '<option value="" selected>Erro ao carregar</option>';
        });
    });
  });
</script>

<script>
    $(document).ready(function () {
        $('.select-employee').on('change', function () {
            const loadId = $(this).data('load-id');
            const employeeId = $(this).val();

            $.ajax({
                url: `/loads/${loadId}/update-employee`,
                method: 'POST',
                data: {
                    employee_id: employeeId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    alert('Employee updated successfully!');
                },
                error: function (xhr) {
                    alert('Error updating employee.');
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>

@endsection