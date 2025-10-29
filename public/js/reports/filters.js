// Usando a biblioteca Chart.js global

// Classe para gerenciar os filtros e atualizações dos relatórios
class ReportFilters {
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
        if (window.reportCharts) {
            window.reportCharts.updateRevenueChart(data);
        }
    }

    // Atualiza o gráfico de comissões
    updateCommissionChart(data) {
        if (window.reportCharts) {
            window.reportCharts.updateCommissionChart(data);
        }
    }

    // Atualiza o gráfico de receita por transportadora
    updateCarrierRevenueChart(data) {
        if (window.reportCharts) {
            window.reportCharts.updateCarrierRevenueChart(data);
        }
    }

    // Atualiza o gráfico de previsão
    updateForecastChart(data) {
        if (window.reportCharts) {
            window.reportCharts.updateForecastChart(data);
        }
    }

    // Atualiza o gráfico de pagamentos
    updatePaymentsChart(data) {
        if (window.reportCharts) {
            window.reportCharts.updatePaymentsChart(data);
        }
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

// Exporta a classe para uso global
window.ReportFilters = ReportFilters;