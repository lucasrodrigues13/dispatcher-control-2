<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Welcome</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="/assets/assets/img/kaiadmin/favicon.ico"
      type="image/x-icon"
    />

     <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


     <style>

        .board-header {
            background-color: var(--header-bg);
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .container-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        .board-container {
            display: flex;
            overflow-x: auto;
            gap: 15px;
            padding-bottom: 20px;
        }

        .container-column {
            background-color: #ebecf0;
            border-radius: 6px;
            min-width: 300px;
            max-width: 300px;
            padding: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .container-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 5px;
        }

        .container-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--primary-color);
            cursor: pointer;
        }

        .container-actions button {
            background: none;
            border: none;
            color: var(--secondary-color);
            font-size: 1rem;
            cursor: pointer;
        }

        .card-list {
            min-height: 50px;
            margin: 10px 0;
        }

        .task-card {
            background-color: var(--card-bg);
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 10px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.2s;
            background-color: #f8f9fa;
        }

        .task-card:hover {
            background-color: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .task-card .card-title {
            font-weight: 500;
            margin-bottom: 8px;
            color: #172b4d;
        }

        .task-card .card-description {
            font-size: 0.9rem;
            color: var(--secondary-color);
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: var(--secondary-color);
        }

        .add-card-btn {
            width: 100%;
            background: rgba(0,0,0,0.05);
            border: none;
            border-radius: 4px;
            padding: 10px;
            color: var(--secondary-color);
            text-align: left;
            transition: background 0.2s;
        }

        .add-card-btn:hover {
            background: rgba(0,0,0,0.1);
            color: var(--primary-color);
        }

        .add-container-btn {
            background: rgba(0,0,0,0.05);
            border: none;
            border-radius: 4px;
            padding: 12px 20px;
            color: var(--secondary-color);
            min-width: 300px;
            transition: background 0.2s;
        }

        .add-container-btn:hover {
            background: rgba(0,0,0,0.1);
            color: var(--primary-color);
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
        }

        .task-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-right: 5px;
        }

        .tag-design {
            background-color: #e3fcef;
            color: #064e3b;
        }

        .tag-dev {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .tag-bug {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .tag-feature {
            background-color: #ede9fe;
            color: #5b21b6;
        }

        .task-details-section {
            margin-bottom: 20px;
        }

        .task-details-section h6 {
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .ui-sortable-helper {
            transform: rotate(2deg);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .container-placeholder {
            border: 2px dashed #ccc;
            border-radius: 6px;
            min-height: 100px;
            margin: 10px 0;
            background: rgba(0,0,0,0.02);
        }

        .task-priority {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .priority-high {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .priority-medium {
            background-color: #fef3c7;
            color: #92400e;
        }

        .priority-low {
            background-color: #d1fae5;
            color: #065f46;
        }
    </style>

    <style>
      .seta-voltar {
        margin-left: 10px;
        margin-right: 10px;
        font-size: 10px;
        cursor: pointer;
      }
      .seta-voltar i {
        color: #000;
      }
      .btn-add-new {
        position: fixed; right: 20px !important; bottom: 30px;
        z-index: 99;
      }
      
      /* Garantir que flash messages apareçam abaixo do header fixo */
      /* O main-header tem position: fixed e z-index: 1001, altura ~70px */
      .flash-messages-container {
          margin-top: 70px; /* Compensar altura do header fixo */
          padding-top: 1rem;
          padding-bottom: 0.5rem;
          clear: both;
          position: relative;
          z-index: 1;
      }
    </style>

    <!-- Fonts and icons -->
    <script src="/assets/assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["/assets/assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="/assets/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/assets/css/plugins.min.css" />
    <link rel="stylesheet" href="/assets/assets/css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="/assets/assets/css/demo.css" />
  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->


    @include('layouts.sidebar')


      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="dark">
              <a href="/dashboard" class="logo">
                @php
                  $owner = auth()->user()->is_owner ? auth()->user() : auth()->user()->owner;
                  $logoUrl = $owner && $owner->logo ? asset('storage/' . $owner->logo) : null;
                @endphp
                @if($logoUrl)
                  <img src="{{ $logoUrl }}" alt="Logo" style="max-height: 30px; max-width: 150px; object-fit: contain;">
                @else
                  Logo
                @endif
              </a>
              <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                  <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                  <i class="gg-menu-left"></i>
                </button>
              </div>
              <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
              </button>
            </div>
            <!-- End Logo Header -->
          </div>
          <!-- Navbar Header -->
          <nav
            class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom"
          >
            <div class="container-fluid d-flex align-items-center">
              {{-- ⭐ NOVO: Dropdown de seleção de tenant para admins - TOTALMENTE À ESQUERDA --}}
              @if(auth()->user()->isAdmin())
              <div class="me-auto">
                @php
                    $adminTenantService = app(\App\Services\AdminTenantService::class);
                    $viewingTenantId = $adminTenantService->getViewingTenantId();
                    $viewingTenant = $viewingTenantId ? \App\Models\User::find($viewingTenantId) : null;
                    $owners = \App\Models\User::getAvailableOwners();
                @endphp
                <div class="dropdown">
                    <a
                        class="btn dropdown-toggle admin-tenant-selector"
                        href="#"
                        id="tenantDropdown"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                        style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #000; font-weight: 600; padding: 10px 20px; border-radius: 8px; min-width: 220px; box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3); border: 2px solid #ffc107; transition: all 0.3s ease; display: flex; align-items: center; justify-content: space-between; gap: 12px;"
                        onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(255, 193, 7, 0.5)';"
                        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 2px 8px rgba(255, 193, 7, 0.3)';"
                    >
                        <div style="display: flex; align-items: center; flex: 1; min-width: 0; gap: 12px;">
                            <i class="fas fa-shield-alt" style="flex-shrink: 0; font-size: 1rem; line-height: 1;"></i>
                            <span class="text-truncate" style="flex: 1; text-align: left; line-height: 1.5; display: flex; align-items: center;">
                                @if($viewingTenant)
                                    {{ $viewingTenant->name }}
                                @else
                                    All Tenants
                                @endif
                            </span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-start animated fadeIn" aria-labelledby="tenantDropdown" style="min-width: 280px; margin-top: 8px; border: 1px solid #ffc107;">
                        <li>
                            <div class="dropdown-title" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #000; padding: 12px 16px; font-weight: 600; border-radius: 4px 4px 0 0;">
                                <i class="fas fa-shield-alt me-2"></i>
                                <strong>Admin Master Mode</strong>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li>
                            <form method="POST" action="{{ route('admin.switch-tenant') }}" class="d-inline w-100">
                                @csrf
                                <input type="hidden" name="tenant_id" value="all">
                                @php
                                    $fontWeight = !$viewingTenant ? '600' : '400';
                                @endphp
                                <button type="submit" class="dropdown-item {{ !$viewingTenant ? 'active bg-warning text-dark' : '' }} w-100 text-start" style="font-weight: {{ $fontWeight }}; padding: 10px 16px;">
                                    <i class="fas fa-globe me-2"></i>
                                    <strong>All Tenants</strong>
                                    <small class="text-muted d-block ms-4" style="font-size: 0.85em;">View all data without filters</small>
                                </button>
                            </form>
                        </li>
                        @foreach($owners as $owner)
                        <li>
                            <form method="POST" action="{{ route('admin.switch-tenant') }}" class="d-inline w-100">
                                @csrf
                                <input type="hidden" name="tenant_id" value="{{ $owner->id }}">
                                @php
                                    $isActive = $viewingTenant && $viewingTenant->id == $owner->id;
                                    $fontWeight = $isActive ? '600' : '400';
                                @endphp
                                <button type="submit" class="dropdown-item {{ $isActive ? 'active bg-warning text-dark' : '' }} w-100 text-start" style="font-weight: {{ $fontWeight }}; padding: 10px 16px;">
                                    <i class="fas fa-user-tie me-2"></i>
                                    <strong>{{ $owner->name }}</strong>
                                    <small class="text-muted d-block ms-4" style="font-size: 0.85em;">{{ $owner->email }}</small>
                                </button>
                            </form>
                        </li>
                        @endforeach
                    </ul>
                </div>
              </div>
              @endif
              
              <nav
                class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex"
              >
                <!-- <div class="input-group">
                  <div class="input-group-prepend">
                    <button type="submit" class="btn btn-search pe-1">
                      <i class="fa fa-search search-icon"></i>
                    </button>
                  </div>
                  <input
                    type="text"
                    placeholder="Search ..."
                    class="form-control"
                  />
                </div> epsaço vago -->
              </nav>

              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li
                  class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none"
                >
                  <a
                    class="nav-link dropdown-toggle"
                    data-bs-toggle="dropdown"
                    href="#"
                    role="button"
                    aria-expanded="false"
                    aria-haspopup="true"
                  >
                    <i class="fa fa-search"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-search animated fadeIn">
                    <form class="navbar-left navbar-form nav-search">
                      <div class="input-group">
                        <input
                          type="text"
                          placeholder="Search ..."
                          class="form-control"
                        />
                      </div>
                    </form>
                  </ul>
                </li>
                <li class="nav-item topbar-user dropdown hidden-caret">
                  <a
                    class="dropdown-toggle profile-pic"
                    data-bs-toggle="dropdown"
                    href="#"
                    aria-expanded="false"
                  >
                    <div class="avatar-sm">
                      @php
                        $user = auth()->user();
                        $photoUrl = $user->photo ? asset('storage/' . $user->photo) . '?v=' . time() . '&r=' . rand(1000, 9999) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=45&background=013d81&color=fff';
                      @endphp
                      <img
                        src="{{ $photoUrl }}"
                        alt="{{ $user->name }}"
                        class="avatar-img rounded-circle"
                        id="header-avatar-small"
                        onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=45&background=013d81&color=fff'"
                      />
                    </div>
                    <span class="profile-username">
                      <span class="op-7">{{ auth()->user()->name }}</span>
                      <!-- <span class="fw-bold">Hizrian</span> -->
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                      <li>
                        <div class="user-box">
                          <div class="avatar-lg">
                            @php
                              $user = auth()->user();
                              $photoUrlLarge = $user->photo ? asset('storage/' . $user->photo) . '?v=' . time() . '&r=' . rand(1000, 9999) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=150&background=013d81&color=fff';
                            @endphp
                            <img
                              src="{{ $photoUrlLarge }}"
                              alt="{{ $user->name }}"
                              class="avatar-img rounded"
                              id="header-avatar-large"
                              onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=150&background=013d81&color=fff'"
                            />
                          </div>
                          <div class="u-text">
                            <h4>{{ auth()->user()->name }}</h4>
                            <p class="text-muted">{{ auth()->user()->email }}</p>
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">My Profile</a>
                        <a class="dropdown-item" href="#" id="logout-link">Logout</a>
                        <!--
                        <a class="dropdown-item" href="#">My Balance</a>
                        <a class="dropdown-item" href="#">Inbox</a> -->
                        <!-- <div class="dropdown-divider"></div> -->
                        <!-- <a class="dropdown-item" href="#">Account Setting</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" id="logout-link">Logout</a> -->
                      </li>
                    </div>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- End Navbar -->
        </div>
        <!-- End Main Header -->

        {{-- Flash Messages - aparecem abaixo da navbar, antes do conteúdo --}}
        <div class="container-fluid px-4 flash-messages-container">
            <x-flash-messages />
        </div>
        
        {{-- Generic Alert Modal Component --}}
        @include('components.modal-alert')

        <style>
          .table>tbody>tr>td, .table>tbody>tr>th {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
          }
        </style>

        @yield("conteudo")

        <!-- <footer class="footer">
          <div class="container-fluid d-flex justify-content-between">
            <nav class="pull-left">
              <ul class="nav">
                <li class="nav-item">
                  <a class="nav-link" href="https://www.devaholic.ao">
                    DevAholic
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#"> Help </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#"> Licenses </a>
                </li>
              </ul>
            </nav>
            <div class="copyright">
              2024, made with <i class="fa fa-heart heart text-danger"></i> by
              <a href="http://www.devaholic.ao">DevAholic</a>
            </div>
            <div>
              Distributed by
              <a target="_blank" href="https://www.devaholic.ao/">DevAholic</a>.
            </div>
          </div>
        </footer>
      </div> -->


    </div>


    <!--   Core JS Files   -->
     <script src="/assets/assets/js/core/jquery-3.7.1.min.js"></script> -->
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <script src="/assets/assets/js/core/popper.min.js"></script>

    <script src="/assets/assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="/assets/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Chart JS -->
    <script src="/assets/assets/js/plugin/chart.js/chart.min.js"></script>

    <!-- jQuery Sparkline -->
    <script src="/assets/assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <!-- Chart Circle -->
    <script src="/assets/assets/js/plugin/chart-circle/circles.min.js"></script>

    <!-- Datatables -->
    <script src="/assets/assets/js/plugin/datatables/datatables.min.js"></script>

    <!-- Bootstrap Notify -->
    <script src="/assets/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

    <!-- jQuery Vector Maps -->
    <script src="/assets/assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="/assets/assets/js/plugin/jsvectormap/world.js"></script>

    <!-- Sweet Alert -->
    <script src="/assets/assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="/assets/assets/js/kaiadmin.min.js"></script>

    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src="/assets/assets/js/setting-demo.js"></script>
    <!--<script src="/assets/assets/js/demo.js"></script>-->

    <script>
      $(document).ready(function () {
        $('#logout-link').on('click', function (e) {
          e.preventDefault();

          $.ajax({
            url: '{{ route("logout") }}',
            type: 'POST',
            data: {
              _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
              window.location.href = '/'; // redireciona após logout
            },
            error: function () {
              if (typeof showAlertModal === 'function') {
                showAlertModal('Error', 'Error logging out.', 'error');
              } else {
                alert('Erro ao fazer logout.');
              }
            }
          });
        });
      });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('logout-link')?.addEventListener('click', function (e) {
            e.preventDefault();

            if (!confirm('Do you really want to logout?')) return;

            fetch('{{ route('logout') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    window.location.href = '/login1';
                } else {
                    return response.json().then(data => {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('Error', data.message || 'Logout failed.', 'error');
                        } else {
                            alert(data.message || 'Logout failed.');
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                if (typeof showAlertModal === 'function') {
                    showAlertModal('Error', 'Something went wrong.', 'error');
                } else {
                    alert('Something went wrong.');
                }
            });
        });
    });
    </script>


  </body>
</html>
