import Chart from 'chart.js/auto';

// Configurações globais do Chart.js
Chart.defaults.font.family = "'Inter', 'Helvetica', 'Arial', sans-serif";
Chart.defaults.color = '#6B7280';
Chart.defaults.responsive = true;
Chart.defaults.maintainAspectRatio = false;

class ReportCharts {
    constructor() {
        this.charts = new Map();
        this.initializeCharts();
    }

    initializeCharts() {
        // Configuração comum para todos os gráficos
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            }
        };

        // Inicializar gráfico de receita
        this.charts.set('revenue', new Chart(
            document.getElementById('revenue-chart'),
            {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Valor (R$)'
                            }
                        }
                    }
                }
            }
        ));

        // Inicializar gráfico de comissões
        this.charts.set('commission', new Chart(
            document.getElementById('commission-chart'),
            {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Valor (R$)'
                            }
                        }
                    }
                }
            }
        ));

        // Inicializar gráfico de receita por transportadora
        this.charts.set('carrier-revenue', new Chart(
            document.getElementById('carrier-revenue-chart'),
            {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Valor (R$)'
                            }
                        }
                    }
                }
            }
        ));

        // Inicializar gráfico de previsão
        this.charts.set('forecast', new Chart(
            document.getElementById('forecast-chart'),
            {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Valor (R$)'
                            }
                        },
                        x: {
                            stacked: true
                        }
                    }
                }
            }
        ));

        // Inicializar gráfico de pagamentos
        this.charts.set('payments', new Chart(
            document.getElementById('payments-chart'),
            {
                type: 'doughnut',
                data: {
                    labels: ['A Vencer', 'Vencido', 'Pago'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: [
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(75, 192, 192, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 206, 86, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    ...commonOptions,
                    cutout: '70%'
                }
            }
        ));
    }

    updateRevenueChart(data) {
        const chart = this.charts.get('revenue');
        if (!chart || !data) return;
        
        chart.data.labels = data.labels || [];
        chart.data.datasets = [{
            label: 'Receita (R$)',
            data: data.values || [],
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderColor: 'rgba(102, 126, 234, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }];
        chart.update('active');
    }

    updateCommissionChart(data) {
        const chart = this.charts.get('commission');
        if (!chart || !data) return;
        
        chart.data.labels = data.labels || [];
        chart.data.datasets = [{
            label: 'Comissões (R$)',
            data: data.values || [],
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }];
        chart.update('active');
    }

    updateCarrierRevenueChart(data) {
        const chart = this.charts.get('carrier_revenue');
        if (!chart || !data) return;
        
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
            '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
        ];
        
        chart.data.labels = data.labels || [];
        chart.data.datasets = [{
            label: 'Receita por Transportadora',
            data: data.values || [],
            backgroundColor: colors.slice(0, data.labels?.length || 0),
            borderWidth: 2,
            borderColor: '#fff'
        }];
        chart.update('active');
    }

    updateForecastChart(data) {
        const chart = this.charts.get('forecast');
        if (!chart || !data) return;
        
        chart.data.labels = data.labels || [];
        chart.data.datasets = [{
            label: 'Previsão de Receita (R$)',
            data: data.values || [],
            backgroundColor: 'rgba(255, 159, 64, 0.1)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }];
        chart.update('active');
    }

    updatePaymentsChart(data) {
        const chart = this.charts.get('payments');
        if (!chart || !data) return;
        
        chart.data.labels = data.labels || [];
        chart.data.datasets = [
            {
                label: 'Recebidos (R$)',
                data: data.received || [],
                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            },
            {
                label: 'Pendentes (R$)',
                data: data.pending || [],
                backgroundColor: 'rgba(255, 99, 132, 0.8)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }
        ];
        chart.update('active');
    }

    destroy() {
        this.charts.forEach(chart => chart.destroy());
        this.charts.clear();
    }
}

class ReportFilters {
    constructor() {
        this.form = document.getElementById('report-form');
        this.customDateRange = document.getElementById('custom-date-range');
        this.refreshButton = document.getElementById('refresh-charts');
        this.reportCharts = window.reportCharts;
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutos
        this.pendingRequests = new Map();

        this.initializeEventListeners();
    }

    initializeEventListeners() {
        this.form.addEventListener('change', () => this.handleFormChange());
        this.refreshButton.addEventListener('click', () => this.refreshData());
    }

    handleFormChange() {
        const periodSelect = document.getElementById('period');
        this.toggleCustomDateRange(periodSelect.value === 'custom');
    }

    toggleCustomDateRange(show) {
        this.customDateRange.style.display = show ? 'block' : 'none';
    }

    getCacheKey(endpoint, filters) {
        return `${endpoint}:${JSON.stringify(filters)}`;
    }

    setCacheData(key, data) {
        this.cache.set(key, {
            data,
            timestamp: Date.now()
        });
    }

    getCacheData(key) {
        const cached = this.cache.get(key);
        if (!cached) return null;

        const isExpired = Date.now() - cached.timestamp > this.cacheTimeout;
        if (isExpired) {
            this.cache.delete(key);
            return null;
        }

        return cached.data;
    }

    async fetchWithCache(reportType, filters) {
        const cacheKey = this.getCacheKey(reportType, filters);
        const cachedData = this.getCacheData(cacheKey);
        if (cachedData) return cachedData;

        // Verificar se já existe uma requisição pendente para este endpoint
        if (this.pendingRequests.has(cacheKey)) {
            return this.pendingRequests.get(cacheKey);
        }

        const promise = new Promise(async (resolve, reject) => {
            try {
                const params = new URLSearchParams({...filters, reportType});
                const response = await fetch('/api/reports/chart-data?' + params);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                this.setCacheData(cacheKey, data);
                this.pendingRequests.delete(cacheKey);
                resolve(data);
            } catch (error) {
                this.pendingRequests.delete(cacheKey);
                reject(error);
            }
        });

        this.pendingRequests.set(cacheKey, promise);
        return promise;
    }

    async refreshData() {
        this.showLoading();
        
        try {
            const formData = new FormData(this.form);
            const filters = Object.fromEntries(formData.entries());
            
            // Validar filtros antes de fazer requisições
            if (!this.validateFilters(filters)) {
                throw new Error('Filtros inválidos. Verifique as datas selecionadas.');
            }

            // Agrupar requisições similares com timeout
            const timeout = new Promise((_, reject) => 
                setTimeout(() => reject(new Error('Timeout: Requisição demorou muito para responder')), 30000)
            );
            
            const dataPromises = Promise.all([
                this.fetchWithCache('revenue', filters),
                this.fetchWithCache('commission', filters),
                this.fetchWithCache('carrier_revenue', filters),
                this.fetchWithCache('forecast', filters),
                this.fetchWithCache('payments', filters)
            ]);
            
            const [revenueData, commissionData, carrierData, forecastData, paymentsData] = 
                await Promise.race([dataPromises, timeout]);

            // Validar dados recebidos
            const validData = this.validateChartData({
                revenue: revenueData,
                commission: commissionData,
                carrier: carrierData,
                forecast: forecastData,
                payments: paymentsData
            });
            
            if (!validData) {
                throw new Error('Dados recebidos são inválidos ou estão corrompidos');
            }

            // Atualizar gráficos em lote com animação suave
            requestAnimationFrame(() => {
                try {
                    this.reportCharts.updateRevenueChart(revenueData);
                    this.reportCharts.updateCommissionChart(commissionData);
                    this.reportCharts.updateCarrierRevenueChart(carrierData);
                    this.reportCharts.updateForecastChart(forecastData);
                    this.reportCharts.updatePaymentsChart(paymentsData);
                } catch (chartError) {
                    console.error('Error updating charts:', chartError);
                    throw new Error('Error rendering charts');
                }
            });

            this.showSuccess();
        } catch (error) {
            console.error('Error refreshing data:', error);
            
            let errorMessage = 'Error loading data';
            
            if (error.message.includes('Timeout')) {
                errorMessage = 'Timeout: Server took too long to respond';
            } else if (error.message.includes('Network')) {
                errorMessage = 'Connection error. Please check your internet';
            } else if (error.message.includes('inválidos')) {
                errorMessage = error.message;
            } else if (error.message.includes('renderizar')) {
                errorMessage = 'Error displaying charts';
            } else if (error.status === 404) {
                errorMessage = 'Endpoint não encontrado';
            } else if (error.status === 500) {
                errorMessage = 'Erro interno do servidor';
            } else if (error.status === 403) {
                errorMessage = 'Acesso negado';
            }
            
            this.showError(errorMessage);
        }
    }
    
    validateFilters(filters) {
        // Validar datas
        if (filters.date_range === 'custom') {
            const startDate = new Date(filters.start_date);
            const endDate = new Date(filters.end_date);
            
            if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                return false;
            }
            
            if (startDate > endDate) {
                return false;
            }
            
            // Verificar se o período não é muito longo (mais de 2 anos)
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (diffDays > 730) {
                return false;
            }
        }
        
        return true;
    }
    
    validateChartData(data) {
        // Verificar se todos os dados são objetos válidos
        for (const [key, value] of Object.entries(data)) {
            if (!value || typeof value !== 'object') {
                console.warn(`Dados inválidos para ${key}:`, value);
                return false;
            }
            
            // Verificar estrutura básica dos dados
            if (key !== 'payments' && (!value.labels || !value.values)) {
                console.warn(`Estrutura inválida para ${key}:`, value);
                return false;
            }
        }
        
        return true;
    }

    showLoading() {
        this.refreshButton.disabled = true;
        this.refreshButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Atualizando...';
        this.refreshButton.classList.add('loading');
        
        // Adicionar indicadores de carregamento nos gráficos
        document.querySelectorAll('.chart-container').forEach(container => {
            container.classList.add('loading');
        });
    }

    showSuccess() {
        this.refreshButton.disabled = false;
        this.refreshButton.innerHTML = '<i class="fas fa-check text-green-500"></i> Atualizado';
        this.refreshButton.classList.remove('loading');
        this.refreshButton.classList.add('success');
        
        // Remover indicadores de carregamento
        document.querySelectorAll('.chart-container').forEach(container => {
            container.classList.remove('loading');
        });
        
        // Mostrar notificação de sucesso
        this.showToast('Gráficos atualizados com sucesso!', 'success');
        
        setTimeout(() => {
            this.refreshButton.innerHTML = '<i class="fas fa-sync"></i> Atualizar Gráficos';
            this.refreshButton.classList.remove('success');
        }, 2000);
    }

    showError(message = 'Error loading data') {
        this.refreshButton.disabled = false;
        this.refreshButton.innerHTML = '<i class="fas fa-exclamation-triangle text-red-500"></i> Error updating';
        this.refreshButton.classList.remove('loading');
        this.refreshButton.classList.add('error');
        
        // Remover indicadores de carregamento
        document.querySelectorAll('.chart-container').forEach(container => {
            container.classList.remove('loading');
        });
        
        // Mostrar notificação de erro
        this.showToast(message, 'error');
        
        setTimeout(() => {
            this.refreshButton.innerHTML = '<i class="fas fa-sync"></i> Atualizar Gráficos';
            this.refreshButton.classList.remove('error');
        }, 3000);
    }
    
    showToast(message, type = 'info') {
        // Remover toast anterior se existir
        const existingToast = document.querySelector('.toast-notification');
        if (existingToast) {
            existingToast.remove();
        }
        
        const toast = document.createElement('div');
        toast.className = `toast-notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
        
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            warning: 'bg-yellow-500 text-white',
            info: 'bg-blue-500 text-white'
        };
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        
        toast.classList.add(...colors[type].split(' '));
        toast.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="${icons[type]}"></i>
                <span>${message}</span>
                <button class="ml-2 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Animar entrada
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remover após 5 segundos
        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }
}

// Inicializar os módulos quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.reportCharts = new ReportCharts();
    window.reportFilters = new ReportFilters();
});