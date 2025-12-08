{{-- resources/views/load/partials/kanban-header.blade.php --}}

<div class="card-header">
  <div class="row">
    <div class="col-md-4 my-2">
      <form method="GET" action="{{ route('loads.mode') }}" id="search-form">
        <div class="input-group">
          <input name="search" 
                type="text"
                id="search-input"
                value="{{ request('search') }}"
                placeholder="Search Loads..."
                class="form-control"
                autocomplete="off" />

          <button type="submit" class="btn btn-outline-secondary" title="Search">
            <i class="fa fa-search"></i>
          </button>

          @if(request('search'))
            <a href="{{ route('loads.mode') }}" class="btn btn-outline-secondary" title="Clear Search">
              <i class="fa fa-times"></i>
            </a>
          @endif

          <button type="button" class="ps-4 btn btn-outline-secondary mx-1" title="Filter" data-bs-toggle="modal" data-bs-target="#applyFilter">
            <i class="fa fa-filter"></i>
          </button>

          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle py-3" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Menu">
              <i class="fa fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
              <li>
                <a class="dropdown-item p-3" href="#" data-bs-toggle="modal" data-bs-target="#cardFieldsConfigModal">
                  <i class="fa fa-cog me-2"></i>Configure Board Settings
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a href="#" id="delete-all-loads-kanban" class="p-3 dropdown-item text-danger">
                  <i class="fa fa-trash me-2"></i>Delete All Loads
                </a>
              </li>
            </ul>
          </div>
        </div>
      </form>
    </div>

    <div class="col my-2 d-flex justify-content-md-end align-items-center gap-2">
      <!-- Botão para alternar visualização -->
      <a href="{{ route('loads.index') }}" class="btn btn-info btn-sm">
        <i class="fa fa-table"></i>
        <span class="d-none d-md-inline">Go to Table View</span>
      </a>
      
      <!-- Separador visual -->
      <div style="width: 1px; height: 35px; background-color: #dee2e6; margin: 0 10px;"></div>
      <a href="#" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importLoadsModal">
        <i class="fa fa-upload"></i>
        <span class="d-none d-md-inline">Import</span>
      </a>
      <a href="{{ route('loads.create') }}" class="btn btn-primary btn-sm">
        <i class="fa fa-plus"></i>
        <span class="d-none d-md-inline">Add Load</span>
      </a>
    </div>
  </div>
</div>
