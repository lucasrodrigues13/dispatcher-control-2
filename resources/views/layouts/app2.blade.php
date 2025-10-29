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
              <a href="index.html" class="logo">
                <!-- <img
                  src="/assets/assets/img/kaiadmin/logo_light.svg"
                  alt="navbar brand"
                  class="navbar-brand"
                  height="20"
                /> --> Logo
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
            <div class="container-fluid">
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
                <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    id="messageDropdown"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    <i class="fa fa-envelope"></i>
                  </a>
                  <ul
                    class="dropdown-menu messages-notif-box animated fadeIn"
                    aria-labelledby="messageDropdown"
                  >
                    <li>
                      <div
                        class="dropdown-title d-flex justify-content-between align-items-center"
                      >
                        Messages
                        <a href="#" class="small">Mark all as read</a>
                      </div>
                    </li>
                    <li>
                      <div class="message-notif-scroll scrollbar-outer">
                        <div class="notif-center">
                          <a href="#">
                            <div class="notif-img">
                              <img
                                src="/assets/assets/img/jm_denis.jpg"
                                alt="Img Profile"
                              />
                            </div>
                            <div class="notif-content">
                              <span class="subject">Jimmy Denis</span>
                              <span class="block"> How are you ? </span>
                              <span class="time">5 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-img">
                              <img
                                src="/assets/assets/img/chadengle.jpg"
                                alt="Img Profile"
                              />
                            </div>
                            <div class="notif-content">
                              <span class="subject">Chad</span>
                              <span class="block"> Ok, Thanks ! </span>
                              <span class="time">12 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-img">
                              <img
                                src="/assets/assets/img/mlane.jpg"
                                alt="Img Profile"
                              />
                            </div>
                            <div class="notif-content">
                              <span class="subject">Jhon Doe</span>
                              <span class="block">
                                Ready for the meeting today...
                              </span>
                              <span class="time">12 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-img">
                              <img
                                src="/assets/assets/img/talha.jpg"
                                alt="Img Profile"
                              />
                            </div>
                            <div class="notif-content">
                              <span class="subject">Talha</span>
                              <span class="block"> Hi, Apa Kabar ? </span>
                              <span class="time">17 minutes ago</span>
                            </div>
                          </a>
                        </div>
                      </div>
                    </li>
                    <li>
                      <a class="see-all" href="javascript:void(0);"
                        >See all messages<i class="fa fa-angle-right"></i>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    id="notifDropdown"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    <i class="fa fa-bell"></i>
                    <span class="notification">4</span>
                  </a>
                  <ul
                    class="dropdown-menu notif-box animated fadeIn"
                    aria-labelledby="notifDropdown"
                  >
                    <li>
                      <div class="dropdown-title">
                        You have 4 new notification
                      </div>
                    </li>
                    <li>
                      <div class="notif-scroll scrollbar-outer">
                        <div class="notif-center">
                          <a href="#">
                            <div class="notif-icon notif-primary">
                              <i class="fa fa-user-plus"></i>
                            </div>
                            <div class="notif-content">
                              <span class="block"> New user registered </span>
                              <span class="time">5 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-icon notif-success">
                              <i class="fa fa-comment"></i>
                            </div>
                            <div class="notif-content">
                              <span class="block">
                                Rahmad commented on Admin
                              </span>
                              <span class="time">12 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-img">
                              <img
                                src="/assets/assets/img/profile2.jpg"
                                alt="Img Profile"
                              />
                            </div>
                            <div class="notif-content">
                              <span class="block">
                                Reza send messages to you
                              </span>
                              <span class="time">12 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-icon notif-danger">
                              <i class="fa fa-heart"></i>
                            </div>
                            <div class="notif-content">
                              <span class="block"> Farrah liked Admin </span>
                              <span class="time">17 minutes ago</span>
                            </div>
                          </a>
                        </div>
                      </div>
                    </li>
                    <li>
                      <a class="see-all" href="javascript:void(0);"
                        >See all notifications<i class="fa fa-angle-right"></i>
                      </a>
                    </li>
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
                      <img
                        src="/assets/assets/img/profile.jpg"
                        alt="..."
                        class="avatar-img rounded-circle"
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
                            <img
                              src="/assets/assets/img/profile.jpg"
                              alt="image profile"
                              class="avatar-img rounded"
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
                        <a class="dropdown-item" href="#">My Profile</a>
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

        <style>
          .table>tbody>tr>td, .table>tbody>tr>th {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
          }
        </style>

        @yield("conteudo")

        <footer class="footer">
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
      </div>


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
              alert('Erro ao fazer logout.');
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
                        alert(data.message || 'Logout failed.');
                    });
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                alert('Something went wrong.');
            });
        });
    });
    </script>


  </body>
</html>
