class ReportCharts {
    constructor() {
        this.charts = new Map();
        this.apiEndpoint = '/api/reports/chart-data';
        
        this.chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#2c3e50',
                    bodyColor: '#2c3e50',
                    borderColor: '#e9ecef',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        };

        this.initializeCharts();
        this.loadInitialData();
    }

    initializeCharts() {
        // Gráfico de Receita
        this.createChart('revenueChart', {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Receita Mensal (R$)',
                    data: [],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                ...this.chartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: value => this.formatCurrency(value)
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });

        // Gráfico de Comissões
        this.createChart('commissionChart', {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Comissão por Funcionário (R$)',
                    data: [],
                    backgroundColor: 'rgba(86, 171, 47, 0.8)',
                    borderColor: '#56ab2f',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                ...this.chartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: value => this.formatCurrency(value)
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico de Receita por Transportadora
        this.createChart('carrierRevenueChart', {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    label: 'Receita por Transportadora',
                    data: [],
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#56ab2f',
                        '#a8e6cf',
                        '#4facfe',
                        '#00f2fe',
                        '#f093fb',
                        '#f5576c'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                ...this.chartOptions,
                plugins: {
                    ...this.chartOptions.plugins,
                    tooltip: {
                        ...this.chartOptions.plugins.tooltip,
                        callbacks: {
                            label: (context) => {
                                const label = context.label || '';
                                const value = this.formatCurrency(context.parsed);
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Previsão
        this.createChart('forecastChart', {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Receita Prevista (R$)',
                    data: [],
                    backgroundColor: 'rgba(79, 172, 254, 0.8)',
                    borderColor: '#4facfe',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                ...this.chartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: value => this.formatCurrency(value)
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico de Pagamentos
        this.createChart('paymentsChart', {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Pagamentos Recebidos (R$)',
                        data: [],
                        borderColor: '#56ab2f',
                        backgroundColor: 'rgba(86, 171, 47, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Pagamentos Pendentes (R$)',
                        data: [],
                        borderColor: '#f5576c',
                        backgroundColor: 'rgba(245, 87, 108, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                ...this.chartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: value => this.formatCurrency(value)
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });
    }

    createChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (canvas) {
            const chart = new Chart(canvas, config);
            this.charts.set(canvasId, chart);
            return chart;
        }
        console.warn(`Canvas element with ID '${canvasId}' not found`);
        return null;
    }

    async loadInitialData() {
        try {
            // Carregar dados iniciais para todos os gráficos
            await this.updateChart('revenue');
            await this.updateChart('commission');
            await this.updateChart('carrier_revenue');
            await this.updateChart('forecast');
            await this.updateChart('payments');
        } catch (error) {
            console.error('Erro ao carregar dados iniciais dos gráficos:', error);
            this.showError('Erro ao carregar dados dos gráficos');
        }
    }

    async updateChart(reportType, filters = {}) {
        try {
            const response = await fetch(`${this.apiEndpoint}?reportType=${reportType}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(filters)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            switch (reportType) {
                case 'revenue':
                    this.updateRevenueChart(data);
                    break;
                case 'commission':
                    this.updateCommissionChart(data);
                    break;
                case 'carrier_revenue':
                    this.updateCarrierRevenueChart(data);
                    break;
                case 'forecast':
                    this.updateForecastChart(data);
                    break;
                case 'payments':
                    this.updatePaymentsChart(data);
                    break;
            }
        } catch (error) {
            console.error(`Error updating ${reportType} chart:`, error);
            this.showError(`Error updating ${reportType} chart`);
        }
    }

    updateRevenueChart(data) {
        const chart = this.charts.get('revenueChart');
        if (chart && data.labels && data.values) {
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.values;
            chart.update('active');
        }
    }

    updateCommissionChart(data) {
        const chart = this.charts.get('commissionChart');
        if (chart && data.labels && data.values) {
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.values;
            chart.update('active');
        }
    }

    updateCarrierRevenueChart(data) {
        const chart = this.charts.get('carrierRevenueChart');
        if (chart && data.labels && data.values) {
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.values;
            chart.update('active');
        }
    }

    updateForecastChart(data) {
        const chart = this.charts.get('forecastChart');
        if (chart && data.labels && data.values) {
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.values;
            chart.update('active');
        }
    }

    updatePaymentsChart(data) {
        const chart = this.charts.get('paymentsChart');
        if (chart && data.labels && data.received && data.pending) {
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.received;
            chart.data.datasets[1].data = data.pending;
            chart.update('active');
        }
    }

    updateAllCharts(filters = {}) {
        const reportTypes = ['revenue', 'commission', 'carrier_revenue', 'forecast', 'payments'];
        reportTypes.forEach(type => {
            this.updateChart(type, filters);
        });
    }

    formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    }

    showError(message) {
        // Mostrar mensagem de erro (pode ser integrado com o sistema de notificações existente)
        console.error(message);
        
        // Se existir uma função showMessage global, usar ela
        if (typeof showMessage === 'function') {
            showMessage(message, 'error');
        }
    }

    destroyCharts() {
        this.charts.forEach(chart => {
            if (chart) {
                chart.destroy();
            }
        });
        this.charts.clear();
    }

    // Método para atualizar gráficos quando filtros mudarem
    onFiltersChange(filters) {
        this.updateAllCharts(filters);
    }
}

// Disponibilizar globalmente
window.ReportCharts = ReportCharts;

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart !== 'undefined') {
        window.reportCharts = new ReportCharts();
        
        // Conectar com o botão de atualizar gráficos se existir
        const updateChartsBtn = document.getElementById('updateCharts');
        if (updateChartsBtn) {
            updateChartsBtn.addEventListener('click', () => {
                const filters = {
                    period: document.getElementById('period')?.value,
                    customer_id: document.getElementById('customer_id')?.value,
                    carrier_id: document.getElementById('carrier_id')?.value,
                    employee_id: document.getElementById('employee_id')?.value,
                    driver_id: document.getElementById('driver_id')?.value
                };
                window.reportCharts.updateAllCharts(filters);
            });
        }
    } else {
        console.error('Chart.js não foi carregado');
    }
});
