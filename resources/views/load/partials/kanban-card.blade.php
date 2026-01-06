{{-- resources/views/load/partials/kanban-card.blade.php --}}

<div class="load-card" data-load-id="{{ $load->id }}" draggable="true">
  <div class="card-header-mini">
    <div class="load-id-badge">
      @if(isset($showCheckbox) && $showCheckbox)
      <input type="checkbox" class="load-checkbox" value="{{ $load->id }}" data-load-id="{{ $load->id }}">
      @endif
      <i class="fas fa-hashtag"></i>
      <span>{{ $load->load_id ?? $load->internal_load_id ?? 'N/A' }}</span>
    </div>
    <div class="card-actions">
      <button class="btn-icon btn-edit-load" title="Edit" data-load-id="{{ $load->id }}">
        <i class="fas fa-edit"></i>
      </button>
    </div>
  </div>

  <div class="card-content">
    {{-- Vehicle Info --}}
    <div class="card-field vehicle-info">
      <i class="fas fa-car me-2 text-primary"></i>
      <strong>{{ $load->year_make_model ?? 'Unknown Vehicle' }}</strong>
    </div>

    {{-- VIN --}}
    @if($load->vin)
    <div class="card-field vin-field">
      <small class="text-muted">VIN:</small>
      <span class="vin-text">{{ $load->vin }}</span>
    </div>
    @endif

    {{-- Pickup Location --}}
    <div class="card-field location-field">
      <i class="fas fa-map-marker-alt text-success me-2"></i>
      <div class="location-info">
        <small class="text-muted">From:</small>
        <span>{{ $load->pickup_city ?? 'N/A' }}, {{ $load->pickup_state ?? '' }}</span>
      </div>
    </div>

    {{-- Delivery Location --}}
    <div class="card-field location-field">
      <i class="fas fa-map-marker-alt text-danger me-2"></i>
      <div class="location-info">
        <small class="text-muted">To:</small>
        <span>{{ $load->delivery_city ?? 'N/A' }}, {{ $load->delivery_state ?? '' }}</span>
      </div>
    </div>

    {{-- Carrier --}}
    @if($load->carrier)
    <div class="card-field carrier-field">
      <i class="fas fa-truck me-2 text-info"></i>
      <small>{{ $load->carrier->company_name ?? 'No Carrier' }}</small>
    </div>
    @endif

    {{-- Driver --}}
    @if($load->driver)
    <div class="card-field driver-field">
      <i class="fas fa-user me-2"></i>
      <small>Driver: {{ $load->driver }}</small>
    </div>
    @endif

    {{-- Dates --}}
    <div class="card-dates">
      @if($load->scheduled_pickup_date)
      <div class="pickup-date-row">
        <div class="date-badge pickup-date">
          <i class="fas fa-calendar-alt me-1"></i>
          <small>Pickup: {{ \Carbon\Carbon::parse($load->scheduled_pickup_date)->format('m/d/Y') }}</small>
        </div>
        {{-- Pickup Status Badge --}}
        @if(isset($load->pickup_status))
        <span class="mini-badge pickup-status-badge pickup-status-{{ strtolower($load->pickup_status) }}">
          @if($load->pickup_status === 'READY')
          <i class="fas fa-check-circle"></i> Ready to Pickup
          @elseif($load->pickup_status === 'NOT_READY')
          <i class="fas fa-clock"></i> Not Ready to Pickup
          @else
          <i class="fas fa-hourglass-half"></i> Pending
          @endif
        </span>
        @endif
      </div>
      @endif

      @if($load->scheduled_delivery_date)
      <div class="delivery-date-row">
        <div class="date-badge delivery-date">
          <i class="fas fa-calendar-check me-1"></i>
          <small>Delivery: {{ \Carbon\Carbon::parse($load->scheduled_delivery_date)->format('m/d/Y') }}</small>
        </div>
      </div>
      @endif
    </div>

    {{-- Price --}}
    @if($load->price)
    <div class="card-field price-field">
      <i class="fas fa-dollar-sign me-2 text-warning"></i>
      <strong class="price-text">${{ number_format($load->price, 2) }}</strong>
    </div>
    @endif

    {{-- Status Badges --}}
    <div class="card-badges">
      @if($load->has_terminal)
      <span class="mini-badge terminal-badge">
        <i class="fas fa-building"></i> Terminal
      </span>
      @endif

      @if($load->payment_status === 'paid')
      <span class="mini-badge paid-badge">
        <i class="fas fa-check-circle"></i> Paid
      </span>
      @endif
    </div>
  </div>
</div>

<style>
  /* Load Card */
  .load-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 12px;
    cursor: grab;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }

  .load-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: #007bff;
  }

  .load-card:active {
    cursor: grabbing;
  }

  /* Card Header Mini */
  .card-header-mini {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
  }

  .load-id-badge {
    display: flex;
    align-items: center;
    gap: 4px;
    font-weight: 700;
    font-size: 13px;
    color: #495057;
  }

  .load-id-badge i {
    font-size: 11px;
    color: #6c757d;
  }

  .card-actions {
    display: flex;
    gap: 4px;
  }

  .btn-icon {
    background: none;
    border: none;
    padding: 4px 6px;
    cursor: pointer;
    color: #6c757d;
    border-radius: 4px;
    transition: all 0.2s;
  }

  .btn-icon:hover {
    background: #f8f9fa;
    color: #007bff;
  }

  /* Card Content */
  .card-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .card-field {
    display: flex;
    align-items: center;
    font-size: 12px;
    line-height: 1.4;
  }

  .vehicle-info {
    font-size: 14px;
    color: #212529;
    margin-bottom: 4px;
  }

  .vin-field {
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
  }

  .vin-text {
    font-family: 'Courier New', monospace;
    font-size: 11px;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
  }

  .location-field {
    align-items: flex-start;
  }

  .location-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .location-info small {
    font-size: 10px;
  }

  .location-info span {
    font-size: 12px;
    font-weight: 500;
  }

  .carrier-field,
  .driver-field {
    color: #6c757d;
  }

  .card-dates {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 4px;
  }

  .pickup-date-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .delivery-date-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .date-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    background: #e3f2fd;
    border-radius: 4px;
    font-size: 8px;
    color: #1976d2;
    min-width: 140px;
    justify-content: flex-start;
  }

  .delivery-date {
    background: #fff3e0;
    color: #f57c00;
    max-width: 140px;
  }

  .price-field {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #f0f0f0;
  }

  .price-text {
    font-size: 16px;
    color: #28a745;
  }

  /* Card Badges */
  .card-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 8px;
  }

  .mini-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
  }

  .terminal-badge {
    background: #e8f5e9;
    color: #2e7d32;
  }

  .paid-badge {
    background: #e8f5e9;
    color: #2e7d32;
  }

  .pickup-status-badge {
    font-weight: 600;
  }

  .pickup-status-ready {
    background: #c8e6c9;
    color: #2e7d32;
  }

  .pickup-status-not_ready {
    background: #ffccbc;
    color: #e64a19;
  }

  .pickup-status-pending {
    background: #fff9c4;
    color: #f57f17;
  }

  /* Dragging State */
  .load-card.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
  }
</style>