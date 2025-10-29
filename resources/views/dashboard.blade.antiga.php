@extends("layouts.app")

@section('conteudo')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
            color: var(--dark-color);
        }

        .stat-card {
            text-align: center;
            padding: 25px 15px;
            border-left: 4px solid var(--secondary-color);
        }

        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
            margin: 10px 0;
        }

        .stat-card .label {
            color: #6c757d;
            font-size: 14px;
        }

        .stat-card.revenue {
            border-left-color: var(--success-color);
        }

        .stat-card.commission {
            border-left-color: var(--warning-color);
        }

        .stat-card.forecast {
            border-left-color: var(--danger-color);
        }

        .filter-bar {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .chart-container {
            height: 300px;
            position: relative;
            margin: 20px 0;
        }

        .tabs-container {
            margin-bottom: 20px;
        }

        .tabs-container .nav-tabs .nav-link {
            padding: 10px 20px;
            border: none;
            color: var(--dark-color);
            font-weight: 500;
        }

        .tabs-container .nav-tabs .nav-link.active {
            border-bottom: 3px solid var(--secondary-color);
            color: var(--secondary-color);
            background: transparent;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .data-table th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            font-weight: 600;
            color: var(--dark-color);
            border-top: 1px solid #dee2e6;
        }

        .data-table td {
            padding: 12px 15px;
            border-top: 1px solid #dee2e6;
        }

        .data-table tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-paid {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .status-pending {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        .status-overdue {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }
</style>


            @can('pode_visualizar_dashboard')



        <div class="container">
          <div class="page-inner">
            <!-- Stats Section -->
             <h2 class="py-3">Dashboard</h2>
            <div class="row">
                <!-- Gestão -->
                <div class="col-md-6 col-lg-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="bi bi-people fs-1 text-primary"></i>
                            <div class="number">{{ $total_carriers }}</div>
                            <div class="label">Costumers</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="bi bi-person-badge fs-1 text-primary"></i>
                            <div class="number">{{ $total_employes }}</div>
                            <div class="label">Employees</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="bi bi-truck fs-1 text-primary"></i>
                            <div class="number">{{ $total_drivers }}</div>
                            <div class="label">Drivers</div>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-md-6 col-lg-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="bi bi-box-seam fs-1 text-primary"></i>
                            <div class="number">{{ $total_loads }}.00</div>
                            <div class="label">Load med/Day</div>
                        </div>
                    </div>
                </div> -->
            </div>


<div class="mt-3">
    <div class="card p-4">
        <h5 class="card-title mb-4">Load Average</h5>

        <div class="row">
            <!-- Média por Dia -->
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h6 class="card-subtitle mb-2 text-muted">Daily</h6>
                        <h3 class="text-primary">24</h3>
                        <p class="small text-muted">Loads/Dayle</p>
                    </div>
                </div>
            </div>

            <!-- Média por Semana -->
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h6 class="card-subtitle mb-2 text-muted">Week</h6>
                        <h3 class="text-success">168</h3>
                        <p class="small text-muted">Loads/Week</p>
                    </div>
                </div>
            </div>

            <!-- Média por Companhia -->
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h6 class="card-subtitle mb-2 text-muted">For Company</h6>
                        <h3 class="text-info">12</h3>
                        <p class="small text-muted">Loads/Company</p>
                    </div>
                </div>
            </div>

            <!-- Média por Motorista -->
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h6 class="card-subtitle mb-2 text-muted">For Driver</h6>
                        <h3 class="text-warning">8</h3>
                        <p class="small text-muted">Loads/Driver</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- <div class="mt-3 text-end">
            <small class="text-muted">Atualizado em: 11/07/2025</small>
        </div> -->
    </div>
</div>

<div class="row mb-4">
  <div class="col-md-12">
    <div class="card shadow-sm">
      <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <!-- Título -->
        <h5 class="mb-0">Report View</h5>

        <!-- Filtro por Cliente -->
        <form method="GET" action="#" class="d-flex align-items-center gap-2">
          <label for="carrier_id" class="mb-0">Carrier: </label>
          <select name="carrier_id" id="carrier_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">-- All Carrier</option>
            @foreach($carriers as $carrier)
              <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                {{ $carrier->user->name ?? 'Cliente sem nome' }}
              </option>
            @endforeach
          </select>
        </form>
      </div>
    </div>
  </div>
</div>



            <!-- Revenue Section -->
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Receita (Dispatcher Fee)</span>
                            <div class="d-flex">
                                <select class="form-select form-select-sm me-2">
                                    <option>Price</option>
                                    <option selected>Paid</option>
                                </select>
                                <select class="form-select form-select-sm">
                                    <option selected>-- All Costumers</option>
                                    <option>Cliente A</option>
                                    <option>Cliente B</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tabs-container">
                                <ul class="nav nav-tabs" id="revenueTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="gross-tab" data-bs-toggle="tab" data-bs-target="#gross" type="button" role="tab">Receita Bruta</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="last-month-tab" data-bs-toggle="tab" data-bs-target="#last-month" type="button" role="tab">Mês Anterior</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="current-month-tab" data-bs-toggle="tab" data-bs-target="#current-month" type="button" role="tab">Mês Atual</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="custom-tab" data-bs-toggle="tab" data-bs-target="#custom" type="button" role="tab">Personalizado</button>
                                    </li>
                                </ul>
                                <div class="tab-content mt-3" id="revenueTabContent">
                                    <div class="tab-pane fade show active" id="gross" role="tabpanel">
                                        <div class="chart-container">
                                            <canvas id="grossRevenueChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="last-month" role="tabpanel">
                                        <div class="chart-container">
                                            <canvas id="lastMonthRevenueChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="current-month" role="tabpanel">
                                        <div class="chart-container">
                                            <canvas id="currentMonthRevenueChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="custom" role="tabpanel">
                                        <div class="d-flex mb-3">
                                            <select class="form-select me-2">
                                                <option>Última Semana</option>
                                                <option>Últimos 15 Dias</option>
                                                <option selected>Últimos 30 Dias</option>
                                                <option>Últimos 60 Dias</option>
                                                <option>Últimos 90 Dias</option>
                                                <option>Período Personalizado</option>
                                            </select>
                                            <select class="form-select">
                                                <option selected>Todos os Clientes</option>
                                                <option>Cliente A</option>
                                                <option>Cliente B</option>
                                            </select>
                                        </div>
                                        <div class="chart-container">
                                            <canvas id="customRevenueChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">Commissions for Employees</div>
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <select class="form-select me-2">
                                    <option>Última Semana</option>
                                    <option>Últimos 15 Dias</option>
                                    <option selected>Últimos 30 Dias</option>
                                </select>
                                <select class="form-select">
                                    <option selected>-- All Employees</option>
                                    <option>Funcionário A</option>
                                    <option>Funcionário B</option>
                                </select>
                            </div>
                            <div class="chart-container">
                                <canvas id="commissionChart"></canvas>
                            </div>
                            <div class="mt-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>João Silva</span>
                                    <span class="fw-bold">R$ 8.240,00</span>
                                </div>
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 75%"></div>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Maria Oliveira</span>
                                    <span class="fw-bold">R$ 6.150,00</span>
                                </div>
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 60%"></div>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Carlos Santos</span>
                                    <span class="fw-bold">R$ 4.890,00</span>
                                </div>
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 45%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forecast Section -->
            <div class="row mt-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Previsão (Forecast)</div>
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="btn-group me-2" role="group">
                                    <input type="radio" class="btn-check" name="forecast-type" id="invoiced" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="invoiced">Invoiced</label>

                                    <input type="radio" class="btn-check" name="forecast-type" id="toBeInvoiced" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="toBeInvoiced">To Be Invoiced</label>
                                </div>
                                <select class="form-select">
                                    <option selected>-- All Costumers</option>
                                    <option>Costumer 1</option>
                                    <option>Costumer 2</option>
                                </select>
                            </div>
                            <div class="chart-container">
                                <canvas id="forecastChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Receita por Transportadora/Cliente</div>
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <select class="form-select me-2">
                                    <option selected>Price</option>
                                    <option>Paid</option>
                                </select>
                                <select class="form-select me-2">
                                    <option selected>-- All Costumers</option>
                                    <option>Cliente A</option>
                                    <option>Cliente B</option>
                                </select>
                                <select class="form-select">
                                    <option selected>-- All Drivers</option>
                                    <option>Driver 1</option>
                                    <option>Driver 2</option>
                                </select>
                            </div>
                            <div class="chart-container">
                                <canvas id="carrierRevenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments Section -->
            <div class="row mt-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Pagamentos Futuros (Upcoming Payments)</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Nº Fatura</th>
                                            <th>Cliente</th>
                                            <th>Valor</th>
                                            <th>Vencimento</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>INV-2023-0456</td>
                                            <td>Empresa Logística Ltda</td>
                                            <td>R$ 12.450,00</td>
                                            <td>15/08/2023</td>
                                            <td><span class="status-badge status-pending">Pendente</span></td>
                                        </tr>
                                        <tr>
                                            <td>INV-2023-0457</td>
                                            <td>Transportes Rápido S.A.</td>
                                            <td>R$ 8.670,00</td>
                                            <td>18/08/2023</td>
                                            <td><span class="status-badge status-pending">Pendente</span></td>
                                        </tr>
                                        <tr>
                                            <td>INV-2023-0458</td>
                                            <td>Cargas Express</td>
                                            <td>R$ 15.320,00</td>
                                            <td>20/08/2023</td>
                                            <td><span class="status-badge status-pending">Pendente</span></td>
                                        </tr>
                                        <tr>
                                            <td>INV-2023-0459</td>
                                            <td>Global Transport</td>
                                            <td>R$ 9.850,00</td>
                                            <td>22/08/2023</td>
                                            <td><span class="status-badge status-pending">Pendente</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Faturas Vencidas (Invoice Past Due Date)</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Nº Fatura</th>
                                            <th>Cliente</th>
                                            <th>Valor</th>
                                            <th>Vencimento</th>
                                            <th>Dias Atraso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>INV-2023-0421</td>
                                            <td>Logística Nacional</td>
                                            <td>R$ 7.890,00</td>
                                            <td>05/07/2023</td>
                                            <td><span class="badge bg-danger">32 dias</span></td>
                                        </tr>
                                        <tr>
                                            <td>INV-2023-0435</td>
                                            <td>Transportes Veloz</td>
                                            <td>R$ 11.230,00</td>
                                            <td>15/07/2023</td>
                                            <td><span class="badge bg-danger">22 dias</span></td>
                                        </tr>
                                        <tr>
                                            <td>INV-2023-0442</td>
                                            <td>Cargas & Entregas</td>
                                            <td>R$ 5.670,00</td>
                                            <td>20/07/2023</td>
                                            <td><span class="badge bg-danger">17 dias</span></td>
                                        </tr>
                                        <tr>
                                            <td>INV-2023-0448</td>
                                            <td>Expresso Norte</td>
                                            <td>R$ 9.120,00</td>
                                            <td>25/07/2023</td>
                                            <td><span class="badge bg-danger">12 dias</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>


        @else
  {{-- bloco exibido caso o usuário NÃO tenha a permissão --}}
  <div class="container py-5">
    <div class="alert alert-warning text-center">
      <h4>Sem permissão</h4>
      <p>Você não tem autorização para acessar este dashboard.</p>
    </div>
  </div>
@endcan

    <!-- ============ SCRIPTS ============== -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dados para gráficos (dados de exemplo)
        document.addEventListener('DOMContentLoaded', function() {
            // Configuração comum para gráficos
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            };

            // Gráfico de Receita Bruta
            const grossCtx = document.getElementById('grossRevenueChart').getContext('2d');
            new Chart(grossCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul'],
                    datasets: [{
                        label: 'Receita Bruta',
                        data: [125000, 132000, 141000, 148000, 156000, 163000, 172000],
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            });

            // Gráfico de Receita do Mês Anterior
            const lastMonthCtx = document.getElementById('lastMonthRevenueChart').getContext('2d');
            new Chart(lastMonthCtx, {
                type: 'line',
                data: {
                    labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
                    datasets: [{
                        label: 'Receita do Mês Anterior',
                        data: [38000, 41000, 36500, 39500],
                        fill: false,
                        backgroundColor: 'rgba(46, 204, 113, 0.7)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        tension: 0.1
                    }]
                },
                options: chartOptions
            });

            // Gráfico de Receita do Mês Atual
            const currentMonthCtx = document.getElementById('currentMonthRevenueChart').getContext('2d');
            new Chart(currentMonthCtx, {
                type: 'bar',
                data: {
                    labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
                    datasets: [{
                        label: 'Receita do Mês Atual',
                        data: [42000, 38500, 45000, 41000],
                        backgroundColor: 'rgba(155, 89, 182, 0.7)',
                        borderColor: 'rgba(155, 89, 182, 1)',
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            });

            // Gráfico de Receita Personalizada
            const customCtx = document.getElementById('customRevenueChart').getContext('2d');
            new Chart(customCtx, {
                type: 'line',
                data: {
                    labels: ['01/07', '05/07', '10/07', '15/07', '20/07', '25/07', '30/07'],
                    datasets: [{
                        label: 'Receita Personalizada',
                        data: [5500, 7200, 6800, 8900, 10500, 9800, 11200],
                        fill: false,
                        backgroundColor: 'rgba(241, 196, 15, 0.7)',
                        borderColor: 'rgba(241, 196, 15, 1)',
                        tension: 0.1
                    }]
                },
                options: chartOptions
            });

            // Gráfico de Comissões
            const commissionCtx = document.getElementById('commissionChart').getContext('2d');
            new Chart(commissionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['João Silva', 'Maria Oliveira', 'Carlos Santos', 'Outros'],
                    datasets: [{
                        label: 'Comissões',
                        data: [35, 25, 20, 20],
                        backgroundColor: [
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(241, 196, 15, 0.7)'
                        ],
                        borderColor: [
                            'rgba(46, 204, 113, 1)',
                            'rgba(52, 152, 219, 1)',
                            'rgba(155, 89, 182, 1)',
                            'rgba(241, 196, 15, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                    }
                }
            });

            // Gráfico de Previsão
            const forecastCtx = document.getElementById('forecastChart').getContext('2d');
            new Chart(forecastCtx, {
                type: 'bar',
                data: {
                    labels: ['Ago', 'Set', 'Out', 'Nov', 'Dez'],
                    datasets: [{
                        label: 'Invoiced',
                        data: [142000, 148000, 155000, 162000, 168000],
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                    }, {
                        label: 'To Be Invoiced',
                        data: [85000, 92000, 105000, 112000, 125000],
                        backgroundColor: 'rgba(46, 204, 113, 0.7)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de Receita por Transportadora
            const carrierCtx = document.getElementById('carrierRevenueChart').getContext('2d');
            new Chart(carrierCtx, {
                type: 'polarArea',
                data: {
                    labels: ['Transportes Rápido', 'Logística Nacional', 'Cargas Express', 'Expresso Norte', 'Global Transport'],
                    datasets: [{
                        label: 'Receita',
                        data: [185000, 162000, 148000, 135000, 120000],
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(241, 196, 15, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                    }
                }
            });
        });
    </script>
@endsection
