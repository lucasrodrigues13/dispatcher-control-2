<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportService
{
    /**
     * Obter relatório de receita (Dispatcher Fee)
     */
    public function getRevenueReport(array $filters = []): Collection
    {
        try {
            // Dados fictícios para demonstração - substitua pela query real
            $mockData = collect([
                (object) [
                    'load_id' => 'L001',
                    'customer_name' => 'ABC Logistics',
                    'invoice_date' => '2024-08-15',
                    'dispatcher_fee' => 2500.00,
                    'payment_status' => 'paid',
                    'invoice_number' => 'INV-001',
                    'created_at' => '2024-08-01'
                ],
                (object) [
                    'load_id' => 'L002',
                    'customer_name' => 'XYZ Transport',
                    'invoice_date' => '2024-08-18',
                    'dispatcher_fee' => 1800.00,
                    'payment_status' => 'paid',
                    'invoice_number' => 'INV-002',
                    'created_at' => '2024-08-05'
                ],
                (object) [
                    'load_id' => 'L003',
                    'customer_name' => 'Fast Delivery',
                    'invoice_date' => '2024-08-20',
                    'dispatcher_fee' => 3200.00,
                    'payment_status' => 'paid',
                    'invoice_number' => 'INV-003',
                    'created_at' => '2024-08-10'
                ],
                (object) [
                    'load_id' => 'L004',
                    'customer_name' => 'ABC Logistics',
                    'invoice_date' => '2024-08-22',
                    'dispatcher_fee' => 2100.00,
                    'payment_status' => 'paid',
                    'invoice_number' => 'INV-004',
                    'created_at' => '2024-08-15'
                ]
            ]);

            return $this->applyFilters($mockData, $filters);

            // Query real seria algo como:
            /*
            return DB::table('loads as l')
                ->join('customers as c', 'l.customer_id', '=', 'c.id')
                ->join('invoices as i', 'l.id', '=', 'i.load_id')
                ->select([
                    'l.id as load_id',
                    'c.company_name as customer_name',
                    'i.invoice_date',
                    'i.dispatcher_fee',
                    'i.payment_status',
                    'i.invoice_number',
                    'l.created_at'
                ])
                ->where('i.payment_status', 'paid')
                ->when(isset($filters['start_date']), function($query) use ($filters) {
                    return $query->where('i.invoice_date', '>=', $filters['start_date']);
                })
                ->when(isset($filters['end_date']), function($query) use ($filters) {
                    return $query->where('i.invoice_date', '<=', $filters['end_date']);
                })
                ->when(isset($filters['customer_id']), function($query) use ($filters) {
                    return $query->where('l.customer_id', $filters['customer_id']);
                })
                ->orderBy('i.invoice_date', 'desc')
                ->get();
            */
        } catch (\Exception $e) {
            Log::error('Error in getRevenueReport: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Obter relatório de comissões
     */
    public function getCommissionReport(array $filters = []): Collection
    {
        try {
            $mockData = collect([
                (object) [
                    'employee_id' => 1,
                    'employee_name' => 'João Silva',
                    'customer_name' => 'ABC Logistics',
                    'load_id' => 'L001',
                    'commission_amount' => 125.00,
                    'commission_percentage' => 5.0,
                    'base_amount' => 2500.00,
                    'payment_date' => '2024-08-15',
                    'status' => 'paid'
                ],
                (object) [
                    'employee_id' => 2,
                    'employee_name' => 'Maria Santos',
                    'customer_name' => 'XYZ Transport',
                    'load_id' => 'L002',
                    'commission_amount' => 90.00,
                    'commission_percentage' => 5.0,
                    'base_amount' => 1800.00,
                    'payment_date' => '2024-08-18',
                    'status' => 'paid'
                ],
                (object) [
                    'employee_id' => 3,
                    'employee_name' => 'Pedro Costa',
                    'customer_name' => 'Fast Delivery',
                    'load_id' => 'L003',
                    'commission_amount' => 160.00,
                    'commission_percentage' => 5.0,
                    'base_amount' => 3200.00,
                    'payment_date' => '2024-08-20',
                    'status' => 'paid'
                ]
            ]);

            return $this->applyFilters($mockData, $filters);
        } catch (\Exception $e) {
            Log::error('Error in getCommissionReport: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Obter relatório de receita por transportadora/cliente
     */
    public function getCarrierRevenueReport(array $filters = []): Collection
    {
        try {
            $dataType = $filters['data_type'] ?? 'price';

            $mockData = collect([
                (object) [
                    'carrier_id' => 1,
                    'carrier_name' => 'Swift Transport',
                    'customer_name' => 'ABC Logistics',
                    'load_id' => 'L001',
                    'price' => 2500.00,
                    'paid' => 2500.00,
                    'revenue' => $dataType === 'paid' ? 2500.00 : 2500.00,
                    'load_date' => '2024-08-15',
                    'delivery_date' => '2024-08-17',
                    'status' => 'delivered'
                ],
                (object) [
                    'carrier_id' => 2,
                    'carrier_name' => 'Quick Logistics',
                    'customer_name' => 'XYZ Transport',
                    'load_id' => 'L002',
                    'price' => 1800.00,
                    'paid' => 1750.00,
                    'revenue' => $dataType === 'paid' ? 1750.00 : 1800.00,
                    'load_date' => '2024-08-18',
                    'delivery_date' => '2024-08-20',
                    'status' => 'delivered'
                ],
                (object) [
                    'carrier_id' => 3,
                    'carrier_name' => 'Express Delivery',
                    'customer_name' => 'Fast Delivery',
                    'load_id' => 'L003',
                    'price' => 3200.00,
                    'paid' => 3200.00,
                    'revenue' => $dataType === 'paid' ? 3200.00 : 3200.00,
                    'load_date' => '2024-08-20',
                    'delivery_date' => '2024-08-22',
                    'status' => 'delivered'
                ]
            ]);

            return $this->applyFilters($mockData, $filters);
        } catch (\Exception $e) {
            Log::error('Error in getCarrierRevenueReport: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Obter relatório de previsão (Forecast)
     */
    public function getForecastReport(array $filters = []): Collection
    {
        try {
            $mockData = collect([
                (object) [
                    'load_id' => 'L005',
                    'customer_name' => 'ABC Logistics',
                    'carrier_name' => 'Swift Transport',
                    'scheduled_pickup_date' => '2024-08-25',
                    'scheduled_delivery_date' => '2024-08-27',
                    'actual_pickup_date' => null,
                    'actual_delivery_date' => null,
                    'forecasted_revenue' => 2800.00,
                    'status' => 'to_be_invoiced',
                    'invoice_status' => 'pending',
                    'billing_rule' => 'actual_delivered_date'
                ],
                (object) [
                    'load_id' => 'L006',
                    'customer_name' => 'XYZ Transport',
                    'carrier_name' => 'Quick Logistics',
                    'scheduled_pickup_date' => '2024-08-26',
                    'scheduled_delivery_date' => '2024-08-28',
                    'actual_pickup_date' => '2024-08-26',
                    'actual_delivery_date' => null,
                    'forecasted_revenue' => 1950.00,
                    'status' => 'in_transit',
                    'invoice_status' => 'pending',
                    'billing_rule' => 'actual_delivered_date'
                ],
                (object) [
                    'load_id' => 'L007',
                    'customer_name' => 'Fast Delivery',
                    'carrier_name' => 'Express Delivery',
                    'scheduled_pickup_date' => '2024-08-28',
                    'scheduled_delivery_date' => '2024-08-30',
                    'actual_pickup_date' => null,
                    'actual_delivery_date' => null,
                    'forecasted_revenue' => 3500.00,
                    'status' => 'to_be_invoiced',
                    'invoice_status' => 'pending',
                    'billing_rule' => 'actual_picked_up_date'
                ]
            ]);

            return $this->applyFilters($mockData, $filters);
        } catch (\Exception $e) {
            Log::error('Error in getForecastReport: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Obter relatório de pagamentos futuros
     */
    public function getUpcomingPaymentsReport(array $filters = []): Collection
    {
        try {
            $mockData = collect([
                (object) [
                    'invoice_id' => 'INV-005',
                    'customer_name' => 'ABC Logistics',
                    'load_id' => 'L005',
                    'amount' => 2800.00,
                    'due_date' => '2024-09-05',
                    'invoice_date' => '2024-08-25',
                    'status' => 'pending',
                    'days_until_due' => 14,
                    'payment_terms' => '30 days'
                ],
                (object) [
                    'invoice_id' => 'INV-006',
                    'customer_name' => 'XYZ Transport',
                    'load_id' => 'L006',
                    'amount' => 1950.00,
                    'due_date' => '2024-09-08',
                    'invoice_date' => '2024-08-28',
                    'status' => 'pending',
                    'days_until_due' => 17,
                    'payment_terms' => '30 days'
                ],
                (object) [
                    'invoice_id' => 'INV-007',
                    'customer_name' => 'Fast Delivery',
                    'load_id' => 'L007',
                    'amount' => 3500.00,
                    'due_date' => '2024-09-12',
                    'invoice_date' => '2024-08-30',
                    'status' => 'pending',
                    'days_until_due' => 21,
                    'payment_terms' => '30 days'
                ]
            ]);

            return $this->applyFilters($mockData, $filters);
        } catch (\Exception $e) {
            Log::error('Error in getUpcomingPaymentsReport: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Obter relatório de faturas vencidas
     */
    public function getPastDueReport(array $filters = []): Collection
    {
        try {
            $mockData = collect([
                (object) [
                    'invoice_id' => 'INV-OLD-001',
                    'customer_name' => 'Late Payer Inc',
                    'load_id' => 'L-OLD-001',
                    'amount' => 1200.00,
                    'due_date' => '2024-07-15',
                    'invoice_date' => '2024-06-15',
                    'status' => 'overdue',
                    'days_overdue' => 38,
                    'payment_terms' => '30 days',
                    'overdue_category' => '31-60 days'
                ],
                (object) [
                    'invoice_id' => 'INV-OLD-002',
                    'customer_name' => 'Delayed Corp',
                    'load_id' => 'L-OLD-002',
                    'amount' => 850.00,
                    'due_date' => '2024-08-01',
                    'invoice_date' => '2024-07-01',
                    'status' => 'overdue',
                    'days_overdue' => 21,
                    'payment_terms' => '30 days',
                    'overdue_category' => '0-30 days'
                ],
                (object) [
                    'invoice_id' => 'INV-OLD-003',
                    'customer_name' => 'Very Late LLC',
                    'load_id' => 'L-OLD-003',
                    'amount' => 2100.00,
                    'due_date' => '2024-05-20',
                    'invoice_date' => '2024-04-20',
                    'status' => 'overdue',
                    'days_overdue' => 94,
                    'payment_terms' => '30 days',
                    'overdue_category' => '+90 days'
                ]
            ]);

            return $this->applyFilters($mockData, $filters);
        } catch (\Exception $e) {
            Log::error('Error in getPastDueReport: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Obter estatísticas para o dashboard
     */
    public function getLoadsByStatus(): array
    {
        return [
            'delivered' => 450,
            'in_transit' => 85,
            'pending' => 32,
            'cancelled' => 8
        ];
    }

    public function getRevenueTrend(): array
    {
        return [
            'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            'data' => [120000, 135000, 150000, 165000, 180000, 185000]
        ];
    }

    public function getTopCarriers(): array
    {
        return [
            ['name' => 'Swift Transport', 'revenue' => 45000],
            ['name' => 'Quick Logistics', 'revenue' => 38000],
            ['name' => 'Express Delivery', 'revenue' => 32000],
            ['name' => 'Fast Freight', 'revenue' => 28000],
            ['name' => 'Rapid Transit', 'revenue' => 25000]
        ];
    }

    public function getCommissionSummary(): array
    {
        return [
            'total_this_month' => 15000,
            'total_last_month' => 12500,
            'average_per_employee' => 3000,
            'top_performer' => [
                'name' => 'João Silva',
                'amount' => 5200
            ]
        ];
    }

    /**
     * Aplicar filtros aos dados
     */
    private function applyFilters(Collection $data, array $filters): Collection
    {
        return $data->filter(function ($item) use ($filters) {
            // Filtro de data
            if (isset($filters['start_date']) || isset($filters['end_date'])) {
                $dateField = $this->getDateFieldForItem($item);
                if ($dateField) {
                    $itemDate = Carbon::parse($item->$dateField);

                    if (isset($filters['start_date']) && $itemDate->lt(Carbon::parse($filters['start_date']))) {
                        return false;
                    }

                    if (isset($filters['end_date']) && $itemDate->gt(Carbon::parse($filters['end_date']))) {
                        return false;
                    }
                }
            }

            // Filtro de cliente
            if (isset($filters['customer_id']) && $filters['customer_id'] !== 'all') {
                if (isset($item->customer_id) && $item->customer_id != $filters['customer_id']) {
                    return false;
                }
            }

            // Filtro de transportadora
            if (isset($filters['carrier_id']) && $filters['carrier_id'] !== 'all') {
                if (isset($item->carrier_id) && $item->carrier_id != $filters['carrier_id']) {
                    return false;
                }
            }

            // Filtro de funcionário
            if (isset($filters['employee_id']) && $filters['employee_id'] !== 'all') {
                if (isset($item->employee_id) && $item->employee_id != $filters['employee_id']) {
                    return false;
                }
            }

            // Filtro de motorista
            if (isset($filters['driver_id']) && $filters['driver_id'] !== 'all') {
                if (isset($item->driver_id) && $item->driver_id != $filters['driver_id']) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Determinar qual campo de data usar para filtros
     */
    private function getDateFieldForItem($item): ?string
    {
        if (isset($item->invoice_date)) {
            return 'invoice_date';
        }
        if (isset($item->payment_date)) {
            return 'payment_date';
        }
        if (isset($item->due_date)) {
            return 'due_date';
        }
        if (isset($item->load_date)) {
            return 'load_date';
        }
        if (isset($item->created_at)) {
            return 'created_at';
        }

        return null;
    }

    /**
     * Calcular métricas de cargas
     */
    public function getLoadMetrics(): array
    {
        // Dados fictícios - substitua por queries reais
        return [
            'avg_loads_per_day' => 12.5,
            'avg_loads_per_week' => 87.5,
            'avg_loads_per_company' => 38.2,
            'avg_loads_per_driver' => 6.3,
            'total_loads_this_month' => 375,
            'loads_completed' => 342,
            'loads_in_progress' => 25,
            'loads_cancelled' => 8
        ];
    }

    /**
     * Obter dados para gráficos específicos
     */
    public function getChartData(string $reportType, array $filters = []): array
    {
        switch ($reportType) {
            case 'revenue':
                return $this->getRevenueChartData($filters);
            case 'commission':
                return $this->getCommissionChartData($filters);
            case 'carrier_revenue':
                return $this->getCarrierRevenueChartData($filters);
            case 'forecast':
                return $this->getForecastChartData($filters);
            case 'payments':
                return $this->getPaymentsChartData($filters);
            default:
                return ['error' => 'Invalid report type'];
        }
    }

    private function getRevenueChartData(array $filters): array
    {
        $data = $this->getRevenueReport($filters);

        // Agrupar por mês
        $monthlyData = $data->groupBy(function($item) {
            return Carbon::parse($item->invoice_date)->format('Y-m');
        })->map(function($group) {
            return $group->sum('dispatcher_fee');
        });

        return [
            'labels' => $monthlyData->keys()->map(function($month) {
                return Carbon::parse($month . '-01')->format('M Y');
            })->values()->toArray(),
            'data' => $monthlyData->values()->toArray()
        ];
    }

    private function getCommissionChartData(array $filters): array
    {
        $data = $this->getCommissionReport($filters);

        // Agrupar por funcionário
        $employeeData = $data->groupBy('employee_name')->map(function($group) {
            return $group->sum('commission_amount');
        });

        return [
            'labels' => $employeeData->keys()->toArray(),
            'data' => $employeeData->values()->toArray()
        ];
    }

    private function getCarrierRevenueChartData(array $filters): array
    {
        $data = $this->getCarrierRevenueReport($filters);

        // Agrupar por transportadora
        $carrierData = $data->groupBy('carrier_name')->map(function($group) {
            return $group->sum('revenue');
        })->take(8);

        return [
            'labels' => $carrierData->keys()->toArray(),
            'data' => $carrierData->values()->toArray()
        ];
    }

    private function getForecastChartData(array $filters): array
    {
        $data = $this->getForecastReport($filters);

        // Agrupar por mês de entrega prevista
        $forecastData = $data->groupBy(function($item) {
            $date = $item->scheduled_delivery_date ?: $item->scheduled_pickup_date;
            return $date ? Carbon::parse($date)->format('Y-m') : 'Sem Data';
        })->map(function($group) {
            return $group->sum('forecasted_revenue');
        });

        return [
            'labels' => $forecastData->keys()->map(function($month) {
                return $month === 'Sem Data' ? $month : Carbon::parse($month . '-01')->format('M Y');
            })->toArray(),
            'data' => $forecastData->values()->toArray()
        ];
    }

    private function getPaymentsChartData(array $filters): array
    {
        $upcomingPayments = $this->getUpcomingPaymentsReport($filters);
        $pastDuePayments = $this->getPastDueReport($filters);

        return [
            'upcoming' => [
                'labels' => ['Próximos 7 dias', '8-15 dias', '16-30 dias', '31+ dias'],
                'data' => [45000, 32000, 28000, 15000]
            ],
            'overdue' => [
                'labels' => ['0-30 dias', '31-60 dias', '61-90 dias', '+90 dias'],
                'data' => [12000, 8500, 4200, 2100]
            ]
        ];
    }
}
