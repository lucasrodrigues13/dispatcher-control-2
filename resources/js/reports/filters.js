// Usando a biblioteca Chart.js global
const reportCharts = window.reportCharts;

// Classe para gerenciar os filtros e atualizações dos relatórios
export class ReportFilters {
    constructor() {
        this.form = document.getElementById('reportForm');
        this.periodSelect = document.getElementById('period');
        this.customDateRange = document.getElementById('customDateRange');
        this.refreshButton = document.getElementById('refreshCharts');
        this.statusMessage = document.getElementById('statusMessage');
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes cache
        this.pendingRequests = new Map();

        this.initializeEventListeners();
    }

    // Inicializa os event listeners
    initializeEventListeners() {
        // Atualiza a visibilidade do range de datas customizado
        this.periodSelect?.addEventListener('change', () => {
            this.toggleCustomDateRange();
        });

        // Atualiza os gráficos quando os filtros mudam
        this.form?.addEventListener('change', (e) => {
            if (e.target.tagName === 'SELECT' || e.target.type === 'date') {
                this.updateCharts();
            }
        });

        // Botão de atualizar
        this.refreshButton?.addEventListener('click', () => {
            this.updateCharts();
        });
    }

    // Toggle da visibilidade do range de datas customizado
    toggleCustomDateRange() {
        if (this.periodSelect && this.customDateRange) {
            this.customDateRange.style.display = 
                this.periodSelect.value === 'custom' ? 'block' : 'none';
        }
    }

    // Obtém os valores dos filtros
    getFilterValues() {
        const formData = new FormData(this.form);
        const filters = {};

        for (const [key, value] of formData.entries()) {
            if (value && value !== 'all') {
                filters[key] = value;
            }
        }

        return filters;
    }

    // Atualiza os gráficos com os dados filtrados
    async updateCharts() {
        try {
            this.showLoading();
            const filters = this.getFilterValues();

            // Faz as requisições em paralelo
            const [
                revenueData,
                commissionData,
                carrierRevenueData,
                forecastData,
                paymentsData
            ] = await Promise.all([
                this.fetchChartData('revenue', filters),
                this.fetchChartData('commission', filters),
                this.fetchChartData('carrier-revenue', filters),
                this.fetchChartData('forecast', filters),
                this.fetchChartData('payments', filters)
            ]);

            // Atualiza cada gráfico com seus dados
            this.updateRevenueChart(revenueData);
            this.updateCommissionChart(commissionData);
            this.updateCarrierRevenueChart(carrierRevenueData);
            this.updateForecastChart(forecastData);
            this.updatePaymentsChart(paymentsData);

            this.showSuccess('Charts updated successfully');
        } catch (error) {
            console.error('Error updating charts:', error);
            this.showError('Error updating charts. Please try again.');
        }
    }

    // Busca dados para um gráfico específico
    async fetchChartData(type, filters) {
        const queryString = new URLSearchParams(filters).toString();
        const response = await fetch(`/api/reports/${type}/chart?${queryString}`);
        
        if (!response.ok) {
            throw new Error(`Error fetching ${type} data`);
        }

        return response.json();
    }

    // Atualiza o gráfico de receita
    updateRevenueChart(data) {
        const chartData = {
            labels: data.monthly_breakdown.map(item => item.month),
            datasets: [{
                label: 'Revenue',
                data: data.monthly_breakdown.map(item => item.value),
                borderColor: reportCharts.chartColors.primary,
                backgroundColor: reportCharts.chartColors.primary + '20',
                fill: true
            }]
        };

        reportCharts.updateChartData('revenue', chartData);
    }

    // Atualiza o gráfico de comissões
    updateCommissionChart(data) {
        const chartData = {
            labels: data.by_employee.map(item => item.name),
            datasets: [{
                label: 'Commission',
                data: data.by_employee.map(item => item.value),
                backgroundColor: reportCharts.chartColors.success
            }]
        };

        reportCharts.updateChartData('commission', chartData);
    }

    // Atualiza o gráfico de receita por transportadora
    updateCarrierRevenueChart(data) {
        const chartData = {
            labels: data.by_carrier.map(item => item.name),
            datasets: [{
                data: data.by_carrier.map(item => item.value),
                backgroundColor: Object.values(reportCharts.chartColors)
            }]
        };

        reportCharts.updateChartData('carrierRevenue', chartData);
    }

    // Atualiza o gráfico de previsão
    updateForecastChart(data) {
        const chartData = {
            labels: ['To Be Invoiced', 'Awaiting Delivery'],
            datasets: [{
                label: 'Forecasted Revenue',
                data: [data.to_be_invoiced, data.awaiting_delivery],
                backgroundColor: reportCharts.chartColors.info
            }]
        };

        reportCharts.updateChartData('forecast', chartData);
    }

    // Atualiza o gráfico de pagamentos
    updatePaymentsChart(data) {
        const chartData = {
            labels: ['Upcoming', 'Overdue', 'Paid'],
            datasets: [{
                data: [
                    data.total_upcoming,
                    data.total_overdue,
                    data.total_paid
                ],
                backgroundColor: [
                    reportCharts.chartColors.warning,
                    reportCharts.chartColors.danger,
                    reportCharts.chartColors.success
                ]
            }]
        };

        reportCharts.updateChartData('payments', chartData);
    }

    // Exibe mensagem de carregamento
    showLoading() {
        if (this.statusMessage) {
            this.statusMessage.className = 'alert alert-info';
            this.statusMessage.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating charts...';
            this.statusMessage.style.display = 'block';
        }
    }

    // Exibe mensagem de sucesso
    showSuccess(message) {
        if (this.statusMessage) {
            this.statusMessage.className = 'alert alert-success';
            this.statusMessage.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + message;
            setTimeout(() => {
                this.statusMessage.style.display = 'none';
            }, 3000);
        }
    }

    // Exibe mensagem de erro
    showError(message) {
        if (this.statusMessage) {
            this.statusMessage.className = 'alert alert-danger';
            this.statusMessage.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + message;
        }
    }
}

// Exporta uma instância da classe
export const reportFilters = new ReportFilters();