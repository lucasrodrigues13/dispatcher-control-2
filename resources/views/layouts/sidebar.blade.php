<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="dark">
      <a href="/dashboard" class="logo text-white">Logo</a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-left"></i></button>
        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
      </div>
      <button class="topbar-toggler more">
        <img src="assets/assets/img/profile.jpg" alt="..." class="avatar-img rounded-circle" style="width: 45px; height: 45px;" />
      </button>
    </div>
  </div>

  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">

        {{-- Dashboard --}}
        @can('pode_visualizar_dashboard')
        <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
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
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#users">
            <i class="fas fa-users"></i><p>Users</p><span class="caret"></span>
          </a>
          <div class="collapse" id="users">
            <ul class="nav nav-collapse">
              @can('pode_visualizar_dispatchers')
              <li><a href="/dispatchers"><span class="sub-item">Dispatchers</span></a></li>
              @endcan
              @can('pode_visualizar_employees')
              <li><a href="/employees"><span class="sub-item">Employees</span></a></li>
              @endcan
              @can('pode_visualizar_carriers')
              <li><a href="/carriers"><span class="sub-item">Carriers</span></a></li>
              @endcan
              @can('pode_visualizar_drivers')
              <li><a href="/drivers"><span class="sub-item">Drivers</span></a></li>
              @endcan
              @can('pode_visualizar_brokers')
              <li><a href="/brokers"><span class="sub-item">Brokers</span></a></li>
              @endcan
            </ul>
          </div>
        </li>

        {{-- Agreements --}}
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#agreements">
            <i class="fas fa-handshake"></i><p>Agreements</p><span class="caret"></span>
          </a>
          <div class="collapse" id="agreements">
            <ul class="nav nav-collapse">
              @can('pode_visualizar_deals')
              <li><a href="/deals"><span class="sub-item">Deals</span></a></li>
              @endcan
              @can('pode_visualizar_commissions')
              <li><a href="/commissions"><span class="sub-item">Commissions</span></a></li>
              @endcan
            </ul>
          </div>
        </li>

        {{-- Loads --}}
        @can('pode_visualizar_loads')
        <li class="nav-item">
          <a href="/loads"><i class="fas fa-th-list"></i><p>Loads</p></a>
        </li>
        @endcan

        {{-- Invoices --}}
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#invoices">
            <i class="fas fa-file-invoice"></i><p>Invoices</p><span class="caret"></span>
          </a>
          <div class="collapse" id="invoices">
            <ul class="nav nav-collapse">
              @can('pode_visualizar_invoices.create')
              <li><a href="/invoices/add"><span class="sub-item">New Invoice</span></a></li>
              @endcan
              @can('pode_visualizar_invoices.index')
              <li><a href="/invoices/list"><span class="sub-item">Time Line Charges</span></a></li>
              @endcan
              @can('pode_visualizar_charges_setups.index')
              <li><a href="/charges_setups/list"><span class="sub-item">Charge Setup</span></a></li>
              @endcan
            </ul>
          </div>
        </li>

        {{-- Reports --}}
        <li class="nav-item">
            <a data-bs-toggle="collapse" href="#reports">
                <i class="fas fa-chart-bar"></i><p>Reports and Graphics</p><span class="caret"></span>
            </a>
            <div class="collapse" id="reports">
                <ul class="nav nav-collapse">
                    @can('pode_visualizar_invoices.create')
                    <li><a href="/reports"><span class="sub-item">Reports</span></a></li>
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
        @if(auth()->user()->is_admin ?? false || auth()->user()->roles()->where('name', 'admin')->exists() || in_array(auth()->user()->email, ['alex@abbrtransportandshipping.com']))
        <li class="nav-item {{ request()->is('admin/subscriptions*') ? 'active' : '' }}">
          <a data-bs-toggle="collapse" href="#subscriptions">
            <i class="fas fa-credit-card"></i><p>Subscription Management</p><span class="caret"></span>
          </a>
          <div class="collapse {{ request()->is('admin/subscriptions*') ? 'show' : '' }}" id="subscriptions">
            <ul class="nav nav-collapse">
              <li class="{{ request()->is('admin/subscriptions') ? 'active' : '' }}">
                <a href="{{ route('admin.subscriptions.index') }}">
                  <i class="fas fa-list"></i>
                  <span class="sub-item">All Subscriptions</span>
                </a>
              </li>
              <li>
                <a href="{{ route('admin.subscriptions.index', ['status' => 'active']) }}">
                  <i class="fas fa-check-circle text-success"></i>
                  <span class="sub-item">Active Users</span>
                </a>
              </li>
              <li>
                <a href="{{ route('admin.subscriptions.index', ['status' => 'trial']) }}">
                  <i class="fas fa-clock text-warning"></i>
                  <span class="sub-item">Trial Users</span>
                </a>
              </li>
              <li>
                <a href="{{ route('admin.subscriptions.index', ['status' => 'blocked']) }}">
                  <i class="fas fa-ban text-danger"></i>
                  <span class="sub-item">Blocked Users</span>
                </a>
              </li>
              <li>
                <a href="{{ route('admin.subscriptions.index', ['status' => 'expired']) }}">
                  <i class="fas fa-times-circle text-secondary"></i>
                  <span class="sub-item">Expired Users</span>
                </a>
              </li>
              <li>
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
        @can('pode_visualizar_permissions_roles')
        <li class="nav-item">
          <a class="nav-main-link nav-main-link-submenu" data-bs-toggle="collapse" href="#administrator" aria-expanded="false">
            <i class="fa fa-paste"></i><p>Administrator</p><span class="caret"></span>
          </a>
          <div class="collapse" id="administrator">
            <ul class="nav nav-collapse">
              @can('pode_visualizar_permissions_roles')
              <li>
                <a href="/permissions_roles">
                  <i class="fa fa-lock"></i><span class="sub-item">Permissions and Roles</span>
                </a>
              </li>
              @endcan
              @can('pode_visualizar_roles_users')
              <li>
                <a href="/roles_users">
                  <i class="fa fa-user-lock"></i><span class="sub-item">Roles and Users</span>
                </a>
              </li>
              @endcan
            </ul>
          </div>
        </li>
        @endcan

        {{-- Logout --}}
        <li class="nav-item">
          <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i><p>Logout</p>
          </a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
        </li>

      </ul>
    </div>
  </div>
</div>
<!-- End Sidebar -->
