@extends('layouts.app2')

@section('conteudo')

<style>
  .pagination {
    display: flex;
    list-style: none;
    padding: 0;
    justify-content: center;
    gap: 5px;
  }
  
  .page-item {
    margin: 0 2px;
  }
  
  .page-link {
    display: block;
    padding: 8px 15px;
    text-decoration: none;
    color: #2c3e50;
    border: 1px solid #3498db;
    border-radius: 4px;
    transition: all 0.3s ease;
  }
  
  .page-link:hover {
    background-color: #e6f2ff;
  }
  
  .active .page-link {
    background-color: #3498db;
    color: white;
    border-color: #3498db;
  }
  
  .page-link:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.3);
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
                          <a class="dropdown-item p-3" href="/loads"  id="toggle-mode-btn">Change View Mode</a>
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
                <!-- <a href="#" id="delete-selected" class="btn btn-danger btn-sm">
                  <i class="fa fa-trash"></i>
                  <span class="d-none d-md-inline">Delete</span>
                </a> -->
                <!-- <a href="#" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#selectColums">
                  <i class="fa fa-eye"></i> 
                  <span class="d-none d-md-inline">
                    Show/Hide Columns
                  </span>
                </a> -->
                <!-- <div class="d-flex align-items-start">
                        <button id="new-container-btn" class="add-container-btn">
                            <i class="fas fa-plus me-2"></i>New list
                        </button>
                </div> -->
                <a href="#" id="new-container-btn" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importLoadsModal">
                  <i class="fa fa-upload"></i> 
                  <span class="d-none d-md-inline">
                    Import
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
        </div>
        
        <div class="container0">
        <!-- Área dos Containers -->
        <div class="board-container" id="board-container">
            <!-- Containers serão adicionados aqui via jQuery -->
        </div>
    </div>
    
    <div class="pagination-container">
      <ul class="pagination justify-content-start">
        {{-- Botão Anterior --}}
        <li class="page-item {{ $loads->onFirstPage() ? 'disabled' : '' }}">
          <a class="page-link" href="{{ $loads->previousPageUrl() }}" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>

        {{-- Links das páginas --}}
        @foreach ($loads->getUrlRange(1, $loads->lastPage()) as $page => $url)
          <li class="page-item {{ $loads->currentPage() == $page ? 'active' : '' }}">
            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
          </li>
        @endforeach

        {{-- Botão Próximo --}}
        <li class="page-item {{ !$loads->hasMorePages() ? 'disabled' : '' }}">
          <a class="page-link" href="{{ $loads->nextPageUrl() }}" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- Modal for Shipment Details -->
    <div class="modal fade" id="shipmentDetailModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shipmentModalTitle">Shipment Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="shipmentDetailForm">
                        <input type="hidden" id="currentShipmentId">
                        
                        <!-- Basic Information Section -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Basic Information</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary toggle-section" data-target="basicInfoSection">
                                    <span class="expand-text">Expand</span> <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="card-body section-content" id="basicInfoSection">
                                <div class="row">
                                    <!-- <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">ID:</label>
                                        <div class="form-control-plaintext" id="idDisplay"></div>
                                    </div> -->
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Load ID:</label>
                                        <div class="form-control-plaintext" id="load_idDisplay"></div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Internal Load ID:</label>
                                        <div class="form-control-plaintext" id="internal_load_idDisplay"></div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Creation Date:</label>
                                        <div class="form-control-plaintext" id="creation_dateDisplay"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Dispatcher:</label>
                                        <div class="form-control-plaintext" id="dispatcherDisplay"></div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Trip:</label>
                                        <div class="form-control-plaintext" id="tripDisplay"></div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Year/Make/Model:</label>
                                        <div class="form-control-plaintext" id="year_make_modelDisplay"></div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">VIN:</label>
                                        <div class="form-control-plaintext" id="vinDisplay"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pickup Information Section -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Pickup Information</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary toggle-section" data-target="pickupInfoSection">
                                    <span class="expand-text">Expand</span> <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="card-body section-content" id="pickupInfoSection" style="display: none;">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Pickup Name:</label>
                                        <div class="form-control-plaintext" id="pickup_nameDisplay"></div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Pickup Address:</label>
                                        <div class="form-control-plaintext" id="pickup_addressDisplay"></div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">City/State/Zip:</label>
                                        <div class="form-control-plaintext" id="pickup_city_state_zipDisplay"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Scheduled Date:</label>
                                        <div class="form-control-plaintext" id="scheduled_pickup_dateDisplay"></div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Actual Date:</label>
                                        <div class="form-control-plaintext" id="actual_pickup_dateDisplay"></div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Phone:</label>
                                        <div class="form-control-plaintext" id="pickup_phoneDisplay"></div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Mobile:</label>
                                        <div class="form-control-plaintext" id="pickup_mobileDisplay"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 mb-2">
                                        <label class="form-label fw-bold">Notes:</label>
                                        <div class="form-control-plaintext" id="pickup_notesDisplay"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Information Section -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Delivery Information</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary toggle-section" data-target="deliveryInfoSection">
                                    <span class="expand-text">Expand</span> <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="card-body section-content" id="deliveryInfoSection" style="display: none;">
                                <div class="row">
                                  <div class="col-md-4 mb-2">
                                      <label class="form-label fw-bold">Delivery Name:</label>
                                      <div class="form-control-plaintext" id="delivery_nameDisplay"></div>
                                  </div>
                                  <div class="col-md-4 mb-2">
                                      <label class="form-label fw-bold">Delivery Address:</label>
                                      <div class="form-control-plaintext" id="delivery_addressDisplay"></div>
                                  </div>
                                  <div class="col-md-4 mb-2">
                                      <label class="form-label fw-bold">City/State/Zip:</label>
                                      <div class="form-control-plaintext" id="delivery_city_state_zipDisplay"></div>
                                  </div>
                              </div>
                              <div class="row">
                                  <div class="col-md-4 mb-2">
                                      <label class="form-label fw-bold">Scheduled Date:</label>
                                      <div class="form-control-plaintext" id="scheduled_delivery_dateDisplay"></div>
                                  </div>
                                  <div class="col-md-4 mb-2">
                                      <label class="form-label fw-bold">Actual Date:</label>
                                      <div class="form-control-plaintext" id="actual_delivery_dateDisplay"></div>
                                  </div>
                                  <div class="col-md-4 mb-2">
                                      <label class="form-label fw-bold">Phone:</label>
                                      <div class="form-control-plaintext" id="delivery_phoneDisplay"></div>
                                  </div>
                              </div>
                              <div class="row">
                                  <div class="col-md-6 mb-2">
                                      <label class="form-label fw-bold">Mobile:</label>
                                      <div class="form-control-plaintext" id="delivery_mobileDisplay"></div>
                                  </div>
                                  <div class="col-md-6 mb-2">
                                      <label class="form-label fw-bold">Notes:</label>
                                      <div class="form-control-plaintext" id="delivery_notesDisplay"></div>
                                  </div>
                              </div>
                            </div>
                        </div>

                        <!-- Financial Information Section -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Financial Information</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary toggle-section" data-target="financialInfoSection">
                                    <span class="expand-text">Expand</span> <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="card-body section-content" id="financialInfoSection" style="display: none;">
                                <!-- Similar structure as pickup section -->
                                 <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Price:</label>
                                        <div class="form-control-plaintext" id="priceDisplay"></div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Expenses:</label>
                                        <div class="form-control-plaintext" id="expensesDisplay"></div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Driver Pay:</label>
                                        <div class="form-control-plaintext" id="driver_payDisplay"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Broker Fee:</label>
                                        <div class="form-control-plaintext" id="broker_feeDisplay"></div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Paid Amount:</label>
                                        <div class="form-control-plaintext" id="paid_amountDisplay"></div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Payment Method:</label>
                                        <div class="form-control-plaintext" id="payment_methodDisplay"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Paid Method:</label>
                                        <div class="form-control-plaintext" id="paid_methodDisplay"></div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Payment Terms:</label>
                                        <div class="form-control-plaintext" id="payment_termsDisplay"></div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Payment Status:</label>
                                        <div class="form-control-plaintext" id="payment_statusDisplay"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

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
                <option value="" selected>Select Employee</option>
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
        <form action="{{ route('loads.mode') }}" method="GET" class="mb-4">
          <div class="row g-2">
            <div class="col-md-3">
              <input type="text" name="load_id" class="form-control" placeholder="Load ID" value="{{ request('load_id') }}">
            </div>
            <div class="col-md-3">
              <input type="text" name="internal_load_id" class="form-control" placeholder="Internal Load ID" value="{{ request('internal_load_id') }}">
            </div>
            <div class="col-md-3">
              <input type="text" name="dispatcher" class="form-control" placeholder="Dispatcher" value="{{ request('dispatcher') }}">
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
              <a href="{{ route('loads.mode') }}" class="btn btn-secondary ms-2">Clear</a>
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
        <form method="GET" action="{{ route('loads.mode') }}" class="mb-4">
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
              <a href="{{ route('loads.mode') }}" class="btn btn-secondary ms-2">Clear</a>
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

<!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const loads = @json($loads);
const containersFromServer = @json($containers);

let loadsArray = Object.values(loads.data);

// Mapeia todos os cards para o container fixo "Loads"
const cards = loadsArray.map((item) => ({
    id: `card-${item.id}`,
    cardId: item.id,
    title: item.load_id ? `Load ${item.load_id}` : "Load without ID",
    description: item.dispatcher ? `Dispatcher: ${item.dispatcher}` : "Dispatcher not provided",
    priority: (item.load_id && item.dispatcher) ? "normal" : "low",
    label: "logistics",
    dueDate: item.creation_date || null,
    comments: [],
    shipmentData: item,
    has_terminal: item.has_terminal || false,
    pickup_name: item.pickup_name || '',
    pickup_city: item.pickup_city || '',
    pickup_state: item.pickup_state || '',
    delivery_name: item.delivery_name || '',
    scheduled_pickup_date: item.scheduled_pickup_date || null,
    scheduled_delivery_date: item.scheduled_delivery_date || null
}));

// Mapeia os containers com seus respectivos cards ordenados por posição
const dynamicContainers = containersFromServer.map(container => {
    const orderedCards = (container.container_loads || [])
        .filter(relation => relation.load_item) // Garante que o load está presente
        .sort((a, b) => a.position - b.position)
        .map(relation => {
            const load = relation.load_item;

            return {
                id: `card-${load.id}`,
                cardId: load.id,
                title: load.load_id ? `Load ${load.load_id}` : "Load without ID",
                description: load.dispatcher ? `Dispatcher: ${load.dispatcher}` : "Dispatcher not provided",
                priority: (load.load_id && load.dispatcher) ? "normal" : "low",
                label: "logistics",
                dueDate: load.creation_date || null,
                comments: [],
                shipmentData: load,
                has_terminal: load.has_terminal || false,
                pickup_name: load.pickup_name || '',
                pickup_city: load.pickup_city || '',
                pickup_state: load.pickup_state || '',
                delivery_name: load.delivery_name || '',
                scheduled_pickup_date: load.scheduled_pickup_date || null,
                scheduled_delivery_date: load.scheduled_delivery_date || null
            };
        });

    return {
        id: `container-${container.id}`,
        name: container.name,
        cards: orderedCards
    };
});

// Junta o container fixo "Loads" com os containers dinâmicos
const initialData = {
    containers: [
        {
            id: "container-0",
            name: "Loads",
            cards: cards
        },
        ...dynamicContainers
    ]
};


        $(document).ready(function() {

            // Variáveis globais
            let containers = [...initialData.containers];
            let cardCounter = 4;
            let containerCounter = containers.length;

            // Inicializar o quadro
            function initializeBoard() {
                renderBoard();
                setupDragAndDrop();
            }

            function renderBoard() {
                const boardContainer = $('#board-container');
                boardContainer.empty();

                containers.forEach(container => {
                    const containerElement = createContainerElement(container);
                    boardContainer.append(containerElement);

                    container.cards.forEach(card => {
                        const cardElement = createCardElement(card);
                        $(`#${container.id} .card-list`).append(cardElement);
                    });
                });

                // Adiciona o botão de novo container
                // boardContainer.append(`
                //     <div class="d-flex align-items-start">
                //         <button id="new-container-btn" class="add-container-btn">
                //             <i class="fas fa-plus me-2"></i>New list
                //         </button>
                //     </div>
                // `);

                // Reconfigura os eventos
                $('.container-name').off('click').click(editContainerName);
                $('.delete-container').off('click').click(deleteContainer);
                // $('.add-card').off('click').click(addCard);
                $('.task-card').off('click').click(openShipmentDetails);
                $('#new-container-btn').off('click').click(createNewContainer);

                // 🟢 Reaplica drag and drop após renderização
                setupDragAndDrop();
            }


            // Criar elemento container
            function createContainerElement(container) {
                return `
                    <div class="container-column" id="${container.id}" data-container-id="${container.id}">
                        <div class="container-header">
                            <div class="container-name" data-container-id="${container.id}">${container.name}</div>
                            <div class="container-actions">
                                <!-- button class="add-card" data-container-id="${container.id}">
                                    <i class="fas fa-plus"></i>
                                </button -->
                                 ${
                                      container.id === "container-0"
                                          ? ""
                                          : `<button class="delete-container" data-container-id="${container.id}">
                                                <i class="fas fa-trash"></i>
                                            </button>`
                                  }
                            </div>
                        </div>
                        <div class="card-list px-2 overflow-auto" id="card-list-${container.id}" style="max-height: 350px;"></div>
                        <!-- button class="add-card-btn" data-container-id="${container.id}">
                            <i class="fas fa-plus me-1"></i> Add card
                        </button -->
                    </div>
                `;
            }

            // Criar elemento card
            function createCardElement(card) {
                let priorityClass = "";
                let priorityText = "";

                switch(card.priority) {
                    case "high":
                        priorityClass = "priority-high";
                        priorityText = "Alta";
                        break;
                    case "medium":
                        priorityClass = "priority-medium";
                        priorityText = "Média";
                        break;
                    case "low":
                        priorityClass = "priority-low";
                        priorityText = "Baixa";
                        break;
                }

                let tagClass = "";
                let tagText = "";

                switch(card.label) {
                    case "design":
                        tagClass = "tag-design";
                        tagText = "Design";
                        break;
                    case "dev":
                        tagClass = "tag-dev";
                        tagText = "Dev";
                        break;
                    case "bug":
                        tagClass = "tag-bug";
                        tagText = "Bug";
                        break;
                    case "feature":
                        tagClass = "tag-feature";
                        tagText = "Feature";
                        break;
                }

                // And the card template with proper variables:
                return ` <!-- this is the card -->
                    <div class="task-card ui-draggable ui-draggable-handle" data-card-id="${card.id}" 
                        data-load-id="${card.shipmentData.load_id || ''}" 
                        data-dispatcher="${card.shipmentData.dispatcher || ''}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="task-tag ${card.label === 'logistics' ? 'bg-logistics' : 'bg-secondary'}">
                                    ${card.label === 'logistics' ? 'Logistics' : 'Other'}
                                </span>
                                <span class="task-priority ${card.priority === 'high' ? 'text-danger' : card.priority === 'medium' ? 'text-warning' : 'text-success'}">
                                    ${card.priority === 'high' ? 'High' : card.priority === 'medium' ? 'Medium' : 'Low'}
                                </span>
                            </div>
                            <div class="text-muted small">
                                <i class="far fa-calendar me-1"></i>${formatDate(card.dueDate) || 'No date'}
                            </div>
                        </div>
                        <div class="card-title">${card.title}</div>
                        <div class="card-description">${card.description}</div>
                        <div class="card-footer">
                            <div>
                                <span class="me-3">
                                    <i class="fas fa-truck me-1"></i>${card.shipmentData.trip || 'N/A'}
                                </span>
                                <span class="me-3">
                                    <i class="fas fa-map-marker-alt me-1"></i>${card.shipmentData.pickup_city || ''} to ${card.shipmentData.delivery_city || ''}
                                </span>
                                <!-- span>
                                    <i class="far fa-comment me-1"></i>${card.comments.length}
                                </span -->
                            </div>
                            <!-- button class="btn btn-sm btn-outline-primary view-details-btn">
                                <i class="fas fa-eye"></i> View Details
                            </button -->
                        </div>
                    </div>
                `;
            }

            // Formatar data
            function formatDate(dateString) {
                if (!dateString) return "";
                const date = new Date(dateString);
                return date.toLocaleDateString('pt-BR');
            }

            // Configurar drag and drop
            function setupDragAndDrop() {
              $(".card-list").sortable({
                  connectWith: ".card-list",
                  placeholder: "container-placeholder",

                  receive: function(event, ui) {
                      const rawCardId = ui.item.data("card-id"); // exemplo: "card-62"
                      const rawContainerId = $(this).closest(".container-column").data("container-id"); // exemplo: "container-2"

                      const cardId = String(rawCardId).split("-")[1];         // "62"
                      const containerId = String(rawContainerId).split("-")[1]; // "2"
                      const position = $(this).children().index(ui.item);     // posição do card

                      let movedCard = null;

                      // Remover o card do container antigo
                      containers.forEach(container => {
                          const cardIndex = container.cards.findIndex(card => card.cardId == cardId);
                          if (cardIndex !== -1) {
                              movedCard = container.cards.splice(cardIndex, 1)[0];
                          }
                      });

                      // Adicionar ao novo container
                      if (movedCard) {
                          const targetContainer = containers.find(c => c.id == `container-${containerId}`);
                          if (targetContainer) {
                              targetContainer.cards.push(movedCard);
                          }
                      }

                      // Enviar via AJAX
                      fetch("/mode/container_loads/store", {
                          method: "POST",
                          headers: {
                              "Content-Type": "application/json",
                              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                          },
                          body: JSON.stringify({
                              container_id: containerId,
                              load_id: cardId,
                              position: position,
                              moved_at: new Date().toISOString(),
                          }),
                      })
                      .then(response => {
                          if (!response.ok) throw new Error("Erro ao salvar movimentação.");
                          return response.json();
                      })
                      .then(data => {
                          console.log("Movimentação salva com sucesso:", data);
                      })
                      .catch(error => {
                          console.error("Erro na requisição AJAX:", error);
                          alert("Erro ao salvar movimentação.");
                      });

                      console.log(`Moveu card ID ${cardId} para o container ID ${containerId}, posição ${position}`);
                  }
              }).disableSelection();
          }

          // Editar nome do container
          function editContainerName() {
            let containerId = $(this).data("container-id"); // Ex: "container-1"
            const containerNumber = containerId.replace("container-", ""); // Só o número
            const container = containers.find(c => c.id === containerId);

            const newName = prompt("Edit list name", container.name);
            if (newName && newName.trim() !== "") {
                // Requisição AJAX para atualizar no backend
                $.ajax({
                    url: `/mode/container/${containerNumber}`,
                    type: 'PUT',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        name: newName.trim()
                    },
                    success: function(response) {
                        // Atualiza no frontend
                        container.name = newName.trim();
                        renderBoard();
                        alert("Update Succefully!");
                    },
                    error: function(xhr) {
                        console.error("Update Error", xhr);
                        alert("Update Container name Error.");
                    }
                });
            }
        }


        // Excluir container
        function deleteContainer() {
            let containerId = $(this).data("container-id"); // Ex: "container-1"

            // Extrair apenas o número
            containerId = containerId.replace("container-", ""); // Ex: "1"

            if (confirm("Tem certeza que deseja excluir este container? Todos os cards dentro dele serão removidos.")) {
                // Requisição AJAX para deletar no backend
                $.ajax({
                    url: `/mode/container/${containerId}`,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') // Necessário para Laravel CSRF
                    },
                    success: function(response) {
                        // Remove do array local e atualiza a interface
                        containers = containers.filter(container => container.id !== `container-${containerId}`);
                        renderBoard();
                        alert("Container removido com sucesso.");
                    },
                    error: function(xhr) {
                        console.error("Erro ao excluir container:", xhr);
                        alert("Erro ao excluir o container. Tente novamente.");
                    }
                });
            }
        }


            // Cria um novo container via AJAX
            function createNewContainer() {
                const name = prompt("Add new list name:");

                if (name && name.trim() !== "") {
                    fetch("/mode/container/store", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken
                        },
                        body: JSON.stringify({
                            name: name.trim()
                        })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error("Erro ao criar container");
                        return response.json();
                    })
                    .then(data => {
                        const newContainer = {
                            id: 'container-' + data.data.id,
                            name: data.data.name,
                            cards: []
                        };
                        containers.push(newContainer);
                        renderBoard();
                    })
                    .catch(error => {
                        console.error(error);
                        alert("Erro ao criar container.");
                    });
                }
            }

            // Botão externo para adicionar container
            // $('#add-container').on('click', function () {
            //     containerCounter++;
            //     const newContainer = {
            //         id: 'container-' + containerCounter,
            //         name: 'Novo Container ' + containerCounter,
            //         cards: []
            //     };

            //     containers.push(newContainer);
            //     renderBoard();
            // });

            // Open Details
           function openShipmentDetails() {
            const cardElement = $(this).closest('.task-card');
            const shipmentId = cardElement.data('card-id')?.toString().replace('card-', '');

            console.log('ID do card clicado:', shipmentId);

            $.ajax({
                url: `/loads/show/${shipmentId}`,
                method: 'GET',
                success: function (data) {
                    console.log('Resposta recebida:', data);

                    // Preencher seções do modal
                    $('#idDisplay').text(data.id || 'N/A');
                    $('#load_idDisplay').text(data.load_id || 'N/A');
                    $('#internal_load_idDisplay').text(data.internal_load_id || 'N/A');
                    $('#creation_dateDisplay').text(data.creation_date || 'N/A');
                    $('#dispatcherDisplay').text(data.dispatcher || 'N/A');
                    $('#tripDisplay').text(data.trip || 'N/A');
                    $('#year_make_modelDisplay').text(data.year_make_model || 'N/A');
                    $('#vinDisplay').text(data.vin || 'N/A');

                    // Pickup Information
                    $('#pickup_nameDisplay').text(data.pickup_name || 'N/A');
                    $('#pickup_addressDisplay').text(data.pickup_address || 'N/A');
                    $('#pickup_city_state_zipDisplay').text(
                        `${data.pickup_city || ''}, ${data.pickup_state || ''}, ${data.pickup_zip || ''}`.trim() || 'N/A'
                    );
                    $('#scheduled_pickup_dateDisplay').text(data.scheduled_pickup_date || 'N/A');
                    $('#actual_pickup_dateDisplay').text(data.actual_pickup_date || 'N/A');
                    $('#pickup_phoneDisplay').text(data.pickup_phone || 'N/A');
                    $('#pickup_mobileDisplay').text(data.pickup_mobile || 'N/A');
                    $('#pickup_notesDisplay').text(data.pickup_notes || 'N/A');

                    // Atualiza título
                    $('#shipmentModalTitle').text(`Shipment Details: ${data.load_id || data.id}`);

                    // Delivery Info
                    $('#delivery_nameDisplay').text(data.delivery_name || 'N/A');
                    $('#delivery_addressDisplay').text(data.delivery_address || 'N/A');
                    $('#delivery_city_state_zipDisplay').text(
                        `${data.delivery_city || ''}, ${data.delivery_state || ''}, ${data.delivery_zip || ''}`.trim() || 'N/A'
                    );
                    $('#scheduled_delivery_dateDisplay').text(data.scheduled_delivery_date || 'N/A');
                    $('#actual_delivery_dateDisplay').text(data.actual_delivery_date || 'N/A');
                    $('#delivery_phoneDisplay').text(data.delivery_phone || 'N/A');
                    $('#delivery_mobileDisplay').text(data.delivery_mobile || 'N/A');
                    $('#delivery_notesDisplay').text(data.delivery_notes || 'N/A');

                    // Financial Info
                    $('#priceDisplay').text(data.price || 'N/A');
                    $('#expensesDisplay').text(data.expenses || 'N/A');
                    $('#driver_payDisplay').text(data.driver_pay || 'N/A');
                    $('#broker_feeDisplay').text(data.broker_fee || 'N/A');
                    $('#paid_amountDisplay').text(data.paid_amount || 'N/A');
                    $('#payment_methodDisplay').text(data.payment_method || 'N/A');
                    $('#paid_methodDisplay').text(data.paid_method || 'N/A');
                    $('#payment_termsDisplay').text(data.payment_terms || 'N/A');
                    $('#payment_statusDisplay').text(data.payment_status || 'N/A');


                    // Exibe o modal
                    const shipmentModal = new bootstrap.Modal(document.getElementById('shipmentDetailModal'));
                    shipmentModal.show();
                },
                error: function (xhr) {
                    console.error('Erro ao buscar os detalhes:', xhr.responseText);
                    alert('Erro ao buscar os detalhes do carregamento.');
                }
            });
        }

          // Helper function to format dates
          function formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleDateString();
            }

            // Make sure to update your event listener
            $(document).on('click', '.shipment-card .view-details-btn', openShipmentDetails);

            // Toggle section visibility
            $(document).on('click', '.toggle-section', function() {
                const target = $(this).data('target');
                const section = $('#' + target);
                const icon = $(this).find('i');
                
                section.slideToggle(200, function() {
                    if (section.is(':visible')) {
                        $(this).prev().find('.expand-text').text('Collapse');
                        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    } else {
                        $(this).prev().find('.expand-text').text('Expand');
                        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    }
                });
            });

            // Inicializar o board
            initializeBoard();
        });
    </script>
@endsection