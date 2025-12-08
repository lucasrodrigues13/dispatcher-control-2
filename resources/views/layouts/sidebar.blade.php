<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="dark">
      <a href="/dashboard" class="logo text-white">
        @php
          $owner = auth()->user()->is_owner ? auth()->user() : auth()->user()->owner;
          $logoUrl = $owner && $owner->logo ? asset('storage/' . $owner->logo) : null;
        @endphp
        @if($logoUrl)
          <img src="{{ $logoUrl }}" alt="Logo" style="max-height: 40px; max-width: 150px; object-fit: contain;">
        @else
          Logo
        @endif
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-left"></i></button>
        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
      </div>
      <button class="topbar-toggler more">
        @php
          $user = auth()->user();
          $photoUrlSidebar = $user->photo ? asset('storage/' . $user->photo) . '?v=' . time() : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=45&background=013d81&color=fff';
        @endphp
        <img src="{{ $photoUrlSidebar }}" 
             alt="{{ $user->name }}" 
             class="avatar-img rounded-circle" 
             style="width: 45px; height: 45px; object-fit: cover;"
             id="sidebar-avatar"
             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=45&background=013d81&color=fff'" />
      </button>
    </div>
  </div>

  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">

        {{-- Dashboard --}}
        @can('pode_visualizar_dashboard')
        <li class="nav-item {{ request()->is('dashboard') || request()->routeIs('dashboard*') ? 'active' : '' }}">
          <a href="/dashboard">
            <i class="fas fa-home"></i><p>Dashboard</p>
          </a>
        </li>
        @endcan

        <li class="nav-section">
          <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
          <h4 class="text-section">Management</h4>
        </li>

        {{-- Users --}}
        @php
            $isUsersActive = request()->is('dispatchers*') || request()->is('employees*') || request()->is('carriers*') || request()->is('drivers*') || request()->is('brokers*');
        @endphp
        <li class="nav-item {{ $isUsersActive ? 'active' : '' }}">
          <a data-bs-toggle="collapse" href="#users" class="{{ $isUsersActive ? '' : 'collapsed' }}" aria-expanded="{{ $isUsersActive ? 'true' : 'false' }}">
            <i class="fas fa-users"></i><p>Users</p><span class="caret"></span>
          </a>
          <div class="collapse {{ $isUsersActive ? 'show' : '' }}" id="users">
            <ul class="nav nav-collapse">
              @can('pode_visualizar_dispatchers')
              <li class="{{ request()->is('dispatchers*') ? 'active' : '' }}"><a href="/dispatchers"><span class="sub-item">Dispatchers</span></a></li>
              @endcan
              @can('pode_visualizar_employees')
              <li class="{{ request()->is('employees*') ? 'active' : '' }}"><a href="/employees"><span class="sub-item">Employees</span></a></li>
              @endcan
              @can('pode_visualizar_carriers')
              <li class="{{ request()->is('carriers*') ? 'active' : '' }}"><a href="/carriers"><span class="sub-item">Carriers</span></a></li>
              @endcan
              @can('pode_visualizar_drivers')
              <li class="{{ request()->is('drivers*') ? 'active' : '' }}"><a href="/drivers"><span class="sub-item">Drivers</span></a></li>
              @endcan
              @can('pode_visualizar_brokers')
              <li class="{{ request()->is('brokers*') ? 'active' : '' }}"><a href="/brokers"><span class="sub-item">Brokers</span></a></li>
              @endcan
            </ul>
          </div>
        </li>

        {{-- Agreements (Only for Owners, Subowners, and Admins) --}}
        @if(auth()->user()->is_owner || auth()->user()->is_subowner || auth()->user()->is_admin)
        @php
            $isAgreementsActive = request()->is('deals*') || request()->is('commissions*');
        @endphp
        <li class="nav-item {{ $isAgreementsActive ? 'active' : '' }}">
          <a data-bs-toggle="collapse" href="#agreements" class="{{ $isAgreementsActive ? '' : 'collapsed' }}" aria-expanded="{{ $isAgreementsActive ? 'true' : 'false' }}">
            <i class="fas fa-handshake"></i><p>Agreements</p><span class="caret"></span>
          </a>
          <div class="collapse {{ $isAgreementsActive ? 'show' : '' }}" id="agreements">
            <ul class="nav nav-collapse">
              @can('pode_visualizar_deals')
              <li class="{{ request()->is('deals*') ? 'active' : '' }}"><a href="/deals"><span class="sub-item">Deals</span></a></li>
              @endcan
              @can('pode_visualizar_commissions')
              <li class="{{ request()->is('commissions*') ? 'active' : '' }}"><a href="/commissions"><span class="sub-item">Commissions</span></a></li>
              @endcan
            </ul>
          </div>
        </li>
        @endif

        {{-- Loads --}}
        @can('pode_visualizar_loads')
        <li class="nav-item {{ request()->is('loads*') ? 'active' : '' }}">
          <a href="/loads"><i class="fas fa-th-list"></i><p>Loads</p></a>
        </li>
        @endcan

        {{-- Invoices --}}
        @php
            $isInvoicesActive = request()->is('invoices*') || request()->is('charges_setups*');
        @endphp
        <li class="nav-item {{ $isInvoicesActive ? 'active' : '' }}">
          <a data-bs-toggle="collapse" href="#invoices" class="{{ $isInvoicesActive ? '' : 'collapsed' }}" aria-expanded="{{ $isInvoicesActive ? 'true' : 'false' }}">
            <i class="fas fa-file-invoice"></i><p>Invoices</p><span class="caret"></span>
          </a>
          <div class="collapse {{ $isInvoicesActive ? 'show' : '' }}" id="invoices">
            <ul class="nav nav-collapse">
              @can('pode_visualizar_invoices.create')
              <li class="{{ request()->is('invoices/add*') || request()->is('invoices/create*') ? 'active' : '' }}"><a href="/invoices/add"><span class="sub-item">New Invoice</span></a></li>
              @endcan
              @can('pode_visualizar_invoices.index')
              <li class="{{ request()->is('invoices/list*') || (request()->is('invoices*') && !request()->is('invoices/add*') && !request()->is('invoices/create*')) ? 'active' : '' }}"><a href="/invoices/list"><span class="sub-item">Time Line Charges</span></a></li>
              @endcan
              @can('pode_visualizar_charges_setups.index')
              <li class="{{ request()->is('charges_setups*') ? 'active' : '' }}"><a href="/charges_setups/list"><span class="sub-item">Charge Setup</span></a></li>
              @endcan
            </ul>
          </div>
        </li>

        {{-- Reports --}}
        @php
            $isReportsActive = request()->is('reports*') || request()->is('report*');
        @endphp
        <li class="nav-item {{ $isReportsActive ? 'active' : '' }}">
            <a data-bs-toggle="collapse" href="#reports" class="{{ $isReportsActive ? '' : 'collapsed' }}" aria-expanded="{{ $isReportsActive ? 'true' : 'false' }}">
                <i class="fas fa-chart-bar"></i><p>Reports and Graphics</p><span class="caret"></span>
            </a>
            <div class="collapse {{ $isReportsActive ? 'show' : '' }}" id="reports">
                <ul class="nav nav-collapse">
                    @can('pode_visualizar_invoices.create')
                    <li class="{{ request()->is('reports*') || request()->is('report*') ? 'active' : '' }}"><a href="/reports"><span class="sub-item">Reports</span></a></li>
                    @endcan
                    {{-- outros relatórios... --}}
                </ul>
            </div>
        </li>

        <li class="nav-section">
          <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
          <h4 class="text-section">Administration</h4>
        </li>

        {{-- Subscription Management (NOVO) --}}
        @php
            $isSubscriptionsActive = request()->is('admin/subscriptions*');
        @endphp
        @if(auth()->user()->is_admin ?? false || auth()->user()->roles()->where('name', 'admin')->exists() || in_array(auth()->user()->email, ['alex@abbrtransportandshipping.com']))
        <li class="nav-item {{ $isSubscriptionsActive ? 'active' : '' }}">
          <a data-bs-toggle="collapse" href="#subscriptions" class="{{ $isSubscriptionsActive ? '' : 'collapsed' }}" aria-expanded="{{ $isSubscriptionsActive ? 'true' : 'false' }}">
            <i class="fas fa-credit-card"></i><p>Subscription Management</p><span class="caret"></span>
          </a>
          <div class="collapse {{ $isSubscriptionsActive ? 'show' : '' }}" id="subscriptions">
            <ul class="nav nav-collapse">
              <li class="{{ request()->is('admin/subscriptions') && !request()->has('status') ? 'active' : '' }}">
                <a href="{{ route('admin.subscriptions.index') }}">
                  <i class="fas fa-list"></i>
                  <span class="sub-item">All Subscriptions</span>
                </a>
              </li>
              <li class="{{ request()->is('admin/subscriptions') && request()->get('status') == 'active' ? 'active' : '' }}">
                <a href="{{ route('admin.subscriptions.index', ['status' => 'active']) }}">
                  <i class="fas fa-check-circle text-success"></i>
                  <span class="sub-item">Active Users</span>
                </a>
              </li>
              <li class="{{ request()->is('admin/subscriptions') && request()->get('status') == 'trial' ? 'active' : '' }}">
                <a href="{{ route('admin.subscriptions.index', ['status' => 'trial']) }}">
                  <i class="fas fa-clock text-warning"></i>
                  <span class="sub-item">Trial Users</span>
                </a>
              </li>
              <li class="{{ request()->is('admin/subscriptions') && request()->get('status') == 'blocked' ? 'active' : '' }}">
                <a href="{{ route('admin.subscriptions.index', ['status' => 'blocked']) }}">
                  <i class="fas fa-ban text-danger"></i>
                  <span class="sub-item">Blocked Users</span>
                </a>
              </li>
              <li class="{{ request()->is('admin/subscriptions') && request()->get('status') == 'expired' ? 'active' : '' }}">
                <a href="{{ route('admin.subscriptions.index', ['status' => 'expired']) }}">
                  <i class="fas fa-times-circle text-secondary"></i>
                  <span class="sub-item">Expired Users</span>
                </a>
              </li>
              <li class="{{ request()->is('admin/subscriptions/export*') ? 'active' : '' }}">
                <a href="{{ route('admin.subscriptions.export') }}">
                  <i class="fas fa-download text-info"></i>
                  <span class="sub-item">Export Data</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        @endif

        {{-- Administrator (Permissions & Roles) --}}
        @php
            $isAdministratorActive = request()->is('permissions_roles*') || request()->is('roles_users*');
        @endphp
        @can('pode_visualizar_permissions_roles')
        <li class="nav-item {{ $isAdministratorActive ? 'active' : '' }}">
          <a class="nav-main-link nav-main-link-submenu {{ $isAdministratorActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#administrator" aria-expanded="{{ $isAdministratorActive ? 'true' : 'false' }}">
            <i class="fa fa-paste"></i><p>Administrator</p><span class="caret"></span>
          </a>
          <div class="collapse {{ $isAdministratorActive ? 'show' : '' }}" id="administrator">
            <ul class="nav nav-collapse">
              @can('pode_visualizar_permissions_roles')
              <li class="{{ request()->is('permissions_roles*') ? 'active' : '' }}">
                <a href="/permissions_roles">
                  <i class="fa fa-lock"></i><span class="sub-item">Permissions and Roles</span>
                </a>
              </li>
              @endcan
              @can('pode_visualizar_roles_users')
              @if(auth()->user()->is_admin || auth()->user()->is_owner || auth()->user()->is_subowner)
              <li class="{{ request()->is('roles_users*') ? 'active' : '' }}">
                <a href="/roles_users">
                  <i class="fa fa-user-lock"></i><span class="sub-item">Roles and Users</span>
                </a>
              </li>
              @endif
              @endcan
            </ul>
          </div>
        </li>
        @endcan

        {{-- Logout --}}
        <li class="nav-item">
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="text-decoration: none; display: flex; align-items: center;">
              <i class="fas fa-sign-out-alt"></i><p style="margin: 0; margin-left: 10px;">Logout</p>
            </a>
          </form>
        </li>

      </ul>
    </div>
  </div>
</div>
<!-- End Sidebar -->
