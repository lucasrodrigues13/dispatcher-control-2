class ReportCharts {
    constructor() {
        this.charts = {
            revenue: null,
            commission: null,
            carrierRevenue: null,
            forecast: null,
            payments: null
        };

        this.commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    padding: 10,
                    cornerRadius: 4,
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#2c3e50',
                    bodyColor: '#2c3e50',
                    borderColor: '#e9ecef',
                    borderWidth: 1
                }
            }
        };

        this.initializeCharts();
    }

    initializeCharts() {
        // Inicializa gráfico de receita
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            this.charts.revenue = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Receita Mensal',
                        data: [],
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    ...this.commonOptions,
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
        }

        // Inicializa gráfico de comissão
        const commissionCtx = document.getElementById('commissionChart');
        if (commissionCtx) {
            this.charts.commission = new Chart(commissionCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Comissão',
                        data: [],
                        backgroundColor: '#2ecc71',
                        borderColor: '#27ae60',
                        borderWidth: 1
                    }]
                },
                options: {
                    ...this.commonOptions,
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
        }

        // Inicializa gráfico de receita por transportadora
        const carrierRevenueCtx = document.getElementById('carrierRevenueChart');
        if (carrierRevenueCtx) {
            this.charts.carrierRevenue = new Chart(carrierRevenueCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            '#3498db',
                            '#e74c3c',
                            '#f39c12',
                            '#9b59b6',
                            '#1abc9c',
                            '#34495e'
                        ]
                    }]
                },
                options: this.commonOptions
            });
        }

        // Inicializa gráfico de previsão
        const forecastCtx = document.getElementById('forecastChart');
        if (forecastCtx) {
            this.charts.forecast = new Chart(forecastCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Previsão',
                        data: [],
                        borderColor: '#f39c12',
                        backgroundColor: 'rgba(243, 156, 18, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: true
                    }]
                },
                options: {
                    ...this.commonOptions,
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
        }

        // Inicializa gráfico de pagamentos
        const paymentsCtx = document.getElementById('paymentsChart');
        if (paymentsCtx) {
            this.charts.payments = new Chart(paymentsCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Pagamentos Realizados',
                        data: [],
                        backgroundColor: '#e74c3c',
                        borderColor: '#c0392b',
                        borderWidth: 1
                    }]
                },
                options: {
                    ...this.commonOptions,
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
        }
    }

    updateRevenueChart(data) {
        if (this.charts.revenue && data) {
            this.charts.revenue.data.labels = data.labels || [];
            this.charts.revenue.data.datasets[0].data = data.values || [];
            this.charts.revenue.update();
        }
    }

    updateCommissionChart(data) {
        if (this.charts.commission && data) {
            this.charts.commission.data.labels = data.labels || [];
            this.charts.commission.data.datasets[0].data = data.values || [];
            this.charts.commission.update();
        }
    }

    updateCarrierRevenueChart(data) {
        if (this.charts.carrierRevenue && data) {
            this.charts.carrierRevenue.data.labels = data.labels || [];
            this.charts.carrierRevenue.data.datasets[0].data = data.values || [];
            this.charts.carrierRevenue.update();
        }
    }

    updateForecastChart(data) {
        if (this.charts.forecast && data) {
            this.charts.forecast.data.labels = data.labels || [];
            this.charts.forecast.data.datasets[0].data = data.values || [];
            this.charts.forecast.update();
        }
    }

    updatePaymentsChart(data) {
        if (this.charts.payments && data) {
            this.charts.payments.data.labels = data.labels || [];
            this.charts.payments.data.datasets[0].data = data.values || [];
            this.charts.payments.update();
        }
    }

    destroyCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart) {
                chart.destroy();
            }
        });
    }
}

// Exporta a classe globalmente
window.ReportCharts = ReportCharts;
