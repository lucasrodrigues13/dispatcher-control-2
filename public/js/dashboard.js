/**
 * Optimized Dashboard JavaScript - Memory Leak Fixed
 * Performance optimizations and memory management
 */

class DashboardManager {
    constructor() {
        this.charts = {};
        this.refreshInterval = null;
        this.animationFrames = new Set(); // Track animation frames
        this.eventListeners = new Map(); // Track event listeners
        this.isDestroyed = false;
        this.updateInProgress = false; // Prevent concurrent updates
        this.chartUpdateTimeouts = new Map(); // Debounce chart updates

        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.apiBaseUrl = '/reports'; // Fixed to match your routes

        // Performance settings
        this.settings = {
            updateInterval: 60000, // Increased to 60 seconds to reduce load
            animationDuration: 800, // Reduced animation duration
            maxDataPoints: 50, // Limit data points in charts
            debounceDelay: 300 // Debounce chart updates
        };

        this.init();
    }

    init() {
        if (this.isDestroyed) return;

        try {
            this.setupEventListeners();
            this.initializeCharts();
            this.setupExportHandlers();

            // Only start real-time updates if explicitly enabled
            const realtimeToggle = document.getElementById('realtime-toggle');
            if (realtimeToggle && realtimeToggle.checked) {
                this.startRealTimeUpdates();
            }

            console.log('Dashboard initialized successfully');
        } catch (error) {
            console.error('Dashboard initialization error:', error);
        }
    }

    setupEventListeners() {
        // Clear existing listeners first
        this.removeEventListeners();

        // Filter form handling with debounce
        const filterForm = document.getElementById('dashboard-filters');
        if (filterForm) {
            const handler = this.debounce((e) => {
                e.preventDefault();
                this.updateDashboard();
            }, this.settings.debounceDelay);

            filterForm.addEventListener('submit', handler);
            this.eventListeners.set('filterForm', { element: filterForm, event: 'submit', handler });
        }

        // Period change handling with debounce
        const periodSelect = document.getElementById('period');
        if (periodSelect) {
            const handler = this.debounce((e) => {
                this.handlePeriodChange(e.target.value);
                this.updateCharts();
            }, this.settings.debounceDelay);

            periodSelect.addEventListener('change', handler);
            this.eventListeners.set('periodSelect', { element: periodSelect, event: 'change', handler });
        }

        // Real-time toggle
        const realtimeToggle = document.getElementById('realtime-toggle');
        if (realtimeToggle) {
            const handler = (e) => {
                if (e.target.checked) {
                    this.startRealTimeUpdates();
                } else {
                    this.stopRealTimeUpdates();
                }
            };

            realtimeToggle.addEventListener('change', handler);
            this.eventListeners.set('realtimeToggle', { element: realtimeToggle, event: 'change', handler });
        }

        // Chart actions with event delegation
        const chartHandler = (e) => {
            if (e.target.matches('[data-chart-action]')) {
                this.handleChartAction(e.target);
            }
        };

        document.addEventListener('click', chartHandler);
        this.eventListeners.set('chartActions', { element: document, event: 'click', handler: chartHandler });
    }

    removeEventListeners() {
        this.eventListeners.forEach(({ element, event, handler }) => {
            element.removeEventListener(event, handler);
        });
        this.eventListeners.clear();
    }

    handlePeriodChange(period) {
        const customDates = document.getElementById('custom-dates');
        const customDatesEnd = document.getElementById('custom-dates-end');

        if (customDates && customDatesEnd) {
            const isCustom = period === 'custom';
            customDates.style.display = isCustom ? 'block' : 'none';
            customDatesEnd.style.display = isCustom ? 'block' : 'none';
        }
    }

    async updateDashboard() {
        if (this.updateInProgress || this.isDestroyed) {
            console.log('Update already in progress or dashboard destroyed');
            return;
        }

        this.updateInProgress = true;
        this.showLoading();

        try {
            const filterForm = document.getElementById('dashboard-filters');
            if (!filterForm) {
                throw new Error('Filter form not found');
            }

            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);

            // Add cache busting parameter
            params.set('_t', Date.now());

            const response = await fetch(`${this.apiBaseUrl}?${params}`, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: AbortSignal.timeout(30000) // 30 second timeout
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (!this.isDestroyed) {
                this.updateDashboardContent(data);
                this.debounceChartUpdate();
            }

        } catch (error) {
            console.error('Error updating dashboard:', error);
            if (!this.isDestroyed) {
                this.showError('Failed to update dashboard: ' + error.message);
            }
        } finally {
            this.updateInProgress = false;
            this.hideLoading();
        }
    }

    updateDashboardContent(data) {
        if (this.isDestroyed) return;

        try {
            // Clear existing animations
            this.cancelAllAnimations();

            // Update management stats
            if (data.management_stats) {
                this.updateStatCards(data.management_stats);
            }

            // Update load averages
            if (data.load_averages) {
                this.updateLoadAverages(data.load_averages);
            }

            // Update revenue displays
            if (data.revenue_stats) {
                this.updateRevenueDisplay(data.revenue_stats);
            }

            // Update other sections
            if (data.commission_stats) {
                this.updateCommissionDisplay(data.commission_stats);
            }

            if (data.carrier_revenue_stats) {
                this.updateCarrierRevenueDisplay(data.carrier_revenue_stats);
            }

        } catch (error) {
            console.error('Error updating dashboard content:', error);
        }
    }

    updateStatCards(stats) {
        const mappings = {
            'customer_count': '[data-stat="customer-count"]',
            'driver_count': '[data-stat="driver-count"]',
            'employee_count': '[data-stat="employee-count"]',
            'carrier_count': '[data-stat="carrier-count"]'
        };

        Object.entries(mappings).forEach(([key, selector]) => {
            const element = document.querySelector(selector);
            if (element && stats[key] !== undefined && !isNaN(stats[key])) {
                this.animateNumber(element, stats[key]);
            }
        });
    }

    updateLoadAverages(averages) {
        const mappings = {
            'avg_per_day': '[data-stat="avg-per-day"]',
            'avg_per_week': '[data-stat="avg-per-week"]',
            'avg_per_company': '[data-stat="avg-per-company"]',
            'avg_per_driver': '[data-stat="avg-per-driver"]'
        };

        Object.entries(mappings).forEach(([key, selector]) => {
            const element = document.querySelector(selector);
            if (element && averages[key] !== undefined && !isNaN(averages[key])) {
                this.animateNumber(element, averages[key], 1);
            }
        });
    }

    updateRevenueDisplay(revenue) {
        const mappings = {
            'gross_revenue': '[data-stat="gross-revenue"]',
            'revenue_last_month': '[data-stat="revenue-last-month"]',
            'revenue_this_month': '[data-stat="revenue-this-month"]',
            'custom_revenue': '[data-stat="custom-revenue"]'
        };

        Object.entries(mappings).forEach(([key, selector]) => {
            const element = document.querySelector(selector);
            if (element && revenue[key] !== undefined && !isNaN(revenue[key])) {
                this.animateCurrency(element, revenue[key]);
            }
        });
    }

    updateCommissionDisplay(commission) {
        const mappings = {
            'gross_commission': '[data-stat="gross-commission"]',
            'commission_last_month': '[data-stat="commission-last-month"]',
            'commission_this_month': '[data-stat="commission-this-month"]',
            'custom_commission': '[data-stat="custom-commission"]'
        };

        Object.entries(mappings).forEach(([key, selector]) => {
            const element = document.querySelector(selector);
            if (element && commission[key] !== undefined && !isNaN(commission[key])) {
                this.animateCurrency(element, commission[key]);
            }
        });
    }

    updateCarrierRevenueDisplay(carrierRevenue) {
        const mappings = {
            'gross_revenue_price': '[data-stat="gross-revenue-price"]',
            'gross_revenue_paid': '[data-stat="gross-revenue-paid"]',
            'custom_revenue_price': '[data-stat="custom-revenue-price"]',
            'custom_revenue_paid': '[data-stat="custom-revenue-paid"]'
        };

        Object.entries(mappings).forEach(([key, selector]) => {
            const element = document.querySelector(selector);
            if (element && carrierRevenue[key] !== undefined && !isNaN(carrierRevenue[key])) {
                this.animateCurrency(element, carrierRevenue[key]);
            }
        });
    }

    initializeCharts() {
        if (this.isDestroyed) return;

        try {
            // Destroy existing charts first
            this.destroyAllCharts();

            // Initialize new charts
            this.initRevenueChart();
            this.initCommissionChart();
            this.initCarrierRevenueChart();
            this.initForecastChart();

            console.log('Charts initialized successfully');
        } catch (error) {
            console.error('Error initializing charts:', error);
        }
    }

    destroyAllCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                try {
                    chart.destroy();
                } catch (error) {
                    console.error('Error destroying chart:', error);
                }
            }
        });
        this.charts = {};
    }

    initRevenueChart() {
        const ctx = document.getElementById('revenueChart');
        if (!ctx) return;

        try {
            this.charts.revenue = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Revenue ($)',
                        data: [],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: this.settings.animationDuration
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    return `Revenue: $${context.parsed.y.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Revenue ($)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error creating revenue chart:', error);
        }
    }

    initCommissionChart() {
        const ctx = document.getElementById('commissionChart');
        if (!ctx) return;

        try {
            this.charts.commission = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Last Month', 'This Month'],
                    datasets: [{
                        data: [0, 0],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: this.settings.animationDuration
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    return `${context.label}: $${context.parsed.toLocaleString()}`;
                                }
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error creating commission chart:', error);
        }
    }

    initCarrierRevenueChart() {
        const ctx = document.getElementById('carrierRevenueChart');
        if (!ctx) return;

        try {
            this.charts.carrierRevenue = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Price', 'Paid'],
                    datasets: [{
                        label: 'Total',
                        data: [0, 0],
                        backgroundColor: 'rgba(75, 192, 192, 0.8)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }, {
                        label: 'This Period',
                        data: [0, 0],
                        backgroundColor: 'rgba(255, 159, 64, 0.8)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: this.settings.animationDuration
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error creating carrier revenue chart:', error);
        }
    }

    initForecastChart() {
        const ctx = document.getElementById('forecastChart');
        if (!ctx) return;

        try {
            this.charts.forecast = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['To Be Invoiced', 'Invoiced Not Paid'],
                    datasets: [{
                        data: [0, 0],
                        backgroundColor: [
                            'rgba(255, 205, 86, 0.8)',
                            'rgba(54, 162, 235, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: this.settings.animationDuration
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error creating forecast chart:', error);
        }
    }

    debounceChartUpdate() {
        // Cancel existing timeout
        this.chartUpdateTimeouts.forEach(timeout => clearTimeout(timeout));
        this.chartUpdateTimeouts.clear();

        // Set new timeout
        const timeout = setTimeout(() => {
            if (!this.isDestroyed) {
                this.updateCharts();
            }
        }, this.settings.debounceDelay);

        this.chartUpdateTimeouts.set('update', timeout);
    }

    async updateCharts() {
        if (this.isDestroyed || this.updateInProgress) return;

        try {
            const period = document.getElementById('period')?.value || 'last_30_days';
            const customerId = document.querySelector('[name="customer_id"]')?.value || '';
            const carrierId = document.querySelector('[name="carrier_id"]')?.value || '';

            // Update revenue trend chart
            await this.updateRevenueChart(period, customerId);

            // Update other charts with delays to prevent overwhelming
            setTimeout(() => {
                if (!this.isDestroyed) {
                    this.updateCommissionChartData(period, customerId);
                }
            }, 100);

            setTimeout(() => {
                if (!this.isDestroyed) {
                    this.updateCarrierRevenueChartData(period, carrierId);
                }
            }, 200);

        } catch (error) {
            console.error('Error updating charts:', error);
        }
    }

    async updateRevenueChart(period, customerId) {
        if (!this.charts.revenue || this.isDestroyed) return;

        try {
            const params = new URLSearchParams({
                chart_type: 'revenue_trend',
                period: period,
                customer_id: customerId,
                _t: Date.now()
            });

            const response = await fetch(`${this.apiBaseUrl}/chart-data?${params}`, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: AbortSignal.timeout(15000)
            });

            if (response.ok) {
                const data = await response.json();

                if (data.labels && data.data && !this.isDestroyed) {
                    // Limit data points to prevent memory issues
                    const maxPoints = this.settings.maxDataPoints;
                    const labels = data.labels.slice(-maxPoints);
                    const chartData = data.data.slice(-maxPoints);

                    this.charts.revenue.data.labels = labels;
                    this.charts.revenue.data.datasets[0].data = chartData;
                    this.charts.revenue.update('none'); // Skip animation for better performance
                }
            }
        } catch (error) {
            console.error('Error updating revenue chart:', error);
        }
    }

    updateCommissionChartData(period, customerId) {
        if (!this.charts.commission || this.isDestroyed) return;

        try {
            // Update commission chart with mock data for now
            // Replace with actual API call when needed
            this.charts.commission.update('none');
        } catch (error) {
            console.error('Error updating commission chart:', error);
        }
    }

    updateCarrierRevenueChartData(period, carrierId) {
        if (!this.charts.carrierRevenue || this.isDestroyed) return;

        try {
            // Update carrier revenue chart with mock data for now
            // Replace with actual API call when needed
            this.charts.carrierRevenue.update('none');
        } catch (error) {
            console.error('Error updating carrier revenue chart:', error);
        }
    }

    startRealTimeUpdates() {
        this.stopRealTimeUpdates(); // Clear existing interval

        this.refreshInterval = setInterval(() => {
            if (!this.isDestroyed && !this.updateInProgress) {
                this.updateRealTimeData();
            }
        }, this.settings.updateInterval);

        console.log(`Real-time updates started (${this.settings.updateInterval / 1000}s interval)`);
    }

    stopRealTimeUpdates() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
            console.log('Real-time updates stopped');
        }
    }

    async updateRealTimeData() {
        if (this.isDestroyed || this.updateInProgress) return;

        try {
            const response = await fetch('/api/dashboard/realtime', {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: AbortSignal.timeout(10000)
            });

            if (response.ok) {
                const data = await response.json();
                this.updateRealTimeIndicators(data);
            }
        } catch (error) {
            console.error('Error fetching real-time data:', error);
        }
    }

    updateRealTimeIndicators(data) {
        if (this.isDestroyed) return;

        const indicators = {
            'loads-today': data.total_loads_today,
            'revenue-today': data.revenue_today,
            'pending-invoices': data.pending_invoices,
            'overdue-count': data.overdue_count
        };

        Object.entries(indicators).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element && !isNaN(value)) {
                if (id.includes('revenue')) {
                    this.animateCurrency(element, value);
                } else {
                    this.animateNumber(element, value);
                }
            }
        });
    }

    setupExportHandlers() {
        window.exportData = (format, type) => {
            this.exportWithProgress(format, type);
        };
    }

    async exportWithProgress(format, type) {
        const button = event?.target;
        if (!button) return;

        const originalText = button.innerHTML;
        const originalDisabled = button.disabled;

        try {
            // Show loading state
            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Exporting...';
            button.disabled = true;

            const params = new URLSearchParams(window.location.search);
            params.set('format', format);
            params.set('type', type);
            params.set('_t', Date.now());

            // Create download
            const link = document.createElement('a');
            link.href = `/reports/export?${params.toString()}`;
            link.download = `dashboard_${type}_${new Date().toISOString().split('T')[0]}.${format}`;

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            this.showNotification('Export completed successfully!', 'success');

        } catch (error) {
            console.error('Export error:', error);
            this.showNotification('Export failed. Please try again.', 'error');
        } finally {
            // Restore button state
            setTimeout(() => {
                if (button && !this.isDestroyed) {
                    button.innerHTML = originalText;
                    button.disabled = originalDisabled;
                }
            }, 2000);
        }
    }

    // Utility functions with memory management
    animateNumber(element, targetValue, decimals = 0) {
        if (this.isDestroyed || !element || isNaN(targetValue)) return;

        const startValue = parseFloat(element.textContent.replace(/[^0-9.-]/g, '')) || 0;
        const duration = this.settings.animationDuration;
        const startTime = performance.now();

        const animate = (currentTime) => {
            if (this.isDestroyed) return;

            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / duration, 1);

            const currentValue = startValue + (targetValue - startValue) * this.easeOutQuart(progress);
            element.textContent = currentValue.toLocaleString(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });

            if (progress < 1) {
                const frameId = requestAnimationFrame(animate);
                this.animationFrames.add(frameId);
            }
        };

        const frameId = requestAnimationFrame(animate);
        this.animationFrames.add(frameId);
    }

    animateCurrency(element, targetValue) {
        if (this.isDestroyed || !element || isNaN(targetValue)) return;

        const startValue = parseFloat(element.textContent.replace(/[^0-9.-]/g, '')) || 0;
        const duration = this.settings.animationDuration;
        const startTime = performance.now();

        const animate = (currentTime) => {
            if (this.isDestroyed) return;

            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / duration, 1);

            const currentValue = startValue + (targetValue - startValue) * this.easeOutQuart(progress);
            element.textContent = '$' + currentValue.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            if (progress < 1) {
                const frameId = requestAnimationFrame(animate);
                this.animationFrames.add(frameId);
            }
        };

        const frameId = requestAnimationFrame(animate);
        this.animationFrames.add(frameId);
    }

    cancelAllAnimations() {
        this.animationFrames.forEach(frameId => {
            cancelAnimationFrame(frameId);
        });
        this.animationFrames.clear();
    }

    easeOutQuart(t) {
        return 1 - Math.pow(1 - t, 4);
    }

    // Utility functions
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    showLoading() {
        let loader = document.getElementById('dashboard-loader');
        if (!loader) {
            loader = this.createLoader();
        }
        loader.style.display = 'flex';
    }

    hideLoading() {
        const loader = document.getElementById('dashboard-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    createLoader() {
        const loader = document.createElement('div');
        loader.id = 'dashboard-loader';
        loader.innerHTML = `
            <div class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
                 style="background: rgba(255,255,255,0.8); z-index: 9999;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        document.body.appendChild(loader);
        return loader;
    }

    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 10000; max-width: 350px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            if (toast && toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }

    showError(message) {
        this.showNotification(message, 'danger');
    }

    handleChartAction(element) {
        if (this.isDestroyed) return;

        const action = element.dataset.chartAction;
        const chartId = element.dataset.chartId;

        switch (action) {
            case 'fullscreen':
                this.toggleChartFullscreen(chartId);
                break;
            case 'refresh':
                this.refreshChart(chartId);
                break;
            case 'download':
                this.downloadChart(chartId);
                break;
        }
    }

    toggleChartFullscreen(chartId) {
        if (this.isDestroyed) return;

        const chartContainer = document.querySelector(`#${chartId}`)?.closest('.card');
        if (!chartContainer) return;

        if (chartContainer.classList.contains('fullscreen')) {
            chartContainer.classList.remove('fullscreen');
            document.body.classList.remove('chart-fullscreen');
        } else {
            chartContainer.classList.add('fullscreen');
            document.body.classList.add('chart-fullscreen');
        }
    }

    refreshChart(chartId) {
        if (this.isDestroyed || !this.charts[chartId]) return;

        try {
            this.charts[chartId].update('none');
        } catch (error) {
            console.error('Error refreshing chart:', error);
        }
    }

    downloadChart(chartId) {
        if (this.isDestroyed || !this.charts[chartId]) return;

        try {
            const link = document.createElement('a');
            link.download = `${chartId}_chart.png`;
            link.href = this.charts[chartId].toBase64Image();
            link.click();
        } catch (error) {
            console.error('Error downloading chart:', error);
        }
    }

    // Cleanup method
    destroy() {
        console.log('Destroying dashboard manager...');

        this.isDestroyed = true;

        // Stop real-time updates
        this.stopRealTimeUpdates();

        // Clear all timeouts
        this.chartUpdateTimeouts.forEach(timeout => clearTimeout(timeout));
        this.chartUpdateTimeouts.clear();

        // Cancel all animations
        this.cancelAllAnimations();

        // Remove event listeners
        this.removeEventListeners();

        // Destroy charts
        this.destroyAllCharts();

        // Clear references
        this.charts = {};

        console.log('Dashboard manager destroyed');
    }
}

// Initialize dashboard when DOM is loaded
let dashboardManager = null;

document.addEventListener('DOMContentLoaded', function() {
    // Destroy existing instance
    if (dashboardManager) {
        dashboardManager.destroy();
    }

    // Create new instance
    dashboardManager = new DashboardManager();
    window.dashboardManager = dashboardManager;
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (dashboardManager) {
        dashboardManager.destroy();
    }
});

// Add CSS for fullscreen charts
const style = document.createElement('style');
style.textContent = `
    .fullscreen {
        position: fixed !important;
        top: 0;
        left: 0;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999;
        background: white;
        padding: 20px;
    }

    .chart-fullscreen {
        overflow: hidden;
    }

    .animate-number {
        transition: all 0.3s ease;
    }

    /* Performance optimizations */
    .card {
        will-change: transform;
    }

    canvas {
        will-change: transform;
    }
`;

// Only add style once
if (!document.getElementById('dashboard-styles')) {
    style.id = 'dashboard-styles';
    document.head.appendChild(style);
}
