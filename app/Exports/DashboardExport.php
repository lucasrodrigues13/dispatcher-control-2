<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DashboardExport implements WithMultipleSheets
{
    protected $data;
    protected $type;

    public function __construct($data, $type = 'all')
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function sheets(): array
    {
        $sheets = [];

        switch ($this->type) {
            case 'all':
                $sheets[] = new ManagementStatsSheet($this->data);
                $sheets[] = new LoadAveragesSheet($this->data);
                $sheets[] = new RevenueSheet($this->data);
                $sheets[] = new CommissionSheet($this->data);
                $sheets[] = new CarrierRevenueSheet($this->data);
                $sheets[] = new ForecastSheet($this->data);
                $sheets[] = new UpcomingPaymentsSheet($this->data);
                $sheets[] = new OverdueInvoicesSheet($this->data);
                break;
            case 'revenue':
                $sheets[] = new RevenueSheet($this->data);
                break;
            case 'commission':
                $sheets[] = new CommissionSheet($this->data);
                break;
            case 'carrier_revenue':
                $sheets[] = new CarrierRevenueSheet($this->data);
                break;
            case 'forecast':
                $sheets[] = new ForecastSheet($this->data);
                break;
            case 'upcoming_payments':
                $sheets[] = new UpcomingPaymentsSheet($this->data);
                break;
            case 'overdue_invoices':
                $sheets[] = new OverdueInvoicesSheet($this->data);
                break;
            default:
                $sheets[] = new ManagementStatsSheet($this->data);
        }

        return $sheets;
    }
}

class ManagementStatsSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            [
                $this->data['management_stats']['customer_count'],
                $this->data['management_stats']['driver_count'],
                $this->data['management_stats']['employee_count'],
                $this->data['management_stats']['carrier_count']
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Total Customers',
            'Total Drivers',
            'Total Employees',
            'Total Carriers'
        ];
    }

    public function title(): string
    {
        return 'Management Statistics';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E2E2']
                ]
            ]
        ];
    }
}

class LoadAveragesSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            [
                $this->data['load_averages']['avg_per_day'],
                $this->data['load_averages']['avg_per_week'],
                $this->data['load_averages']['avg_per_company'],
                $this->data['load_averages']['avg_per_driver'],
                $this->data['load_averages']['total_loads']
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Average per Day',
            'Average per Week',
            'Average per Company',
            'Average per Driver',
            'Total Loads'
        ];
    }

    public function title(): string
    {
        return 'Load Averages';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E2E2']
                ]
            ]
        ];
    }
}

class RevenueSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            [
                '$' . number_format($this->data['revenue_stats']['gross_revenue'], 2),
                '$' . number_format($this->data['revenue_stats']['revenue_last_month'], 2),
                '$' . number_format($this->data['revenue_stats']['revenue_this_month'], 2),
                '$' . number_format($this->data['revenue_stats']['custom_revenue'], 2)
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Gross Revenue',
            'Revenue Last Month',
            'Revenue This Month',
            'Custom Period Revenue'
        ];
    }

    public function title(): string
    {
        return 'Dispatcher Fee Revenue';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E2E2']
                ]
            ]
        ];
    }
}

class CommissionSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            [
                '$' . number_format($this->data['commission_stats']['gross_commission'], 2),
                '$' . number_format($this->data['commission_stats']['commission_last_month'], 2),
                '$' . number_format($this->data['commission_stats']['commission_this_month'], 2),
                '$' . number_format($this->data['commission_stats']['custom_commission'], 2)
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Total Commissions',
            'Commission Last Month',
            'Commission This Month',
            'Custom Period Commission'
        ];
    }

    public function title(): string
    {
        return 'Employee Commissions';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E2E2']
                ]
            ]
        ];
    }
}

class CarrierRevenueSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            [
                '$' . number_format($this->data['carrier_revenue_stats']['gross_revenue_price'], 2),
                '$' . number_format($this->data['carrier_revenue_stats']['gross_revenue_paid'], 2),
                '$' . number_format($this->data['carrier_revenue_stats']['custom_revenue_price'], 2),
                '$' . number_format($this->data['carrier_revenue_stats']['custom_revenue_paid'], 2),
                '$' . number_format($this->data['carrier_revenue_stats']['revenue_last_month_price'], 2),
                '$' . number_format($this->data['carrier_revenue_stats']['revenue_this_month_price'], 2)
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Total Revenue (Price)',
            'Total Revenue (Paid)',
            'Period Revenue (Price)',
            'Period Revenue (Paid)',
            'Last Month (Price)',
            'This Month (Price)'
        ];
    }

    public function title(): string
    {
        return 'Carrier Customer Revenue';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E2E2']
                ]
            ]
        ];
    }
}

class ForecastSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            [
                '$' . number_format($this->data['forecast_stats']['to_be_invoiced'], 2),
                '$' . number_format($this->data['forecast_stats']['invoiced_not_paid'], 2),
                '$' . number_format($this->data['forecast_stats']['total_forecast'], 2)
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'To Be Invoiced',
            'Invoiced Not Paid',
            'Total Forecast'
        ];
    }

    public function title(): string
    {
        return 'Revenue Forecast';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E2E2']
                ]
            ]
        ];
    }
}

class UpcomingPaymentsSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $result = [];
        foreach ($this->data['upcoming_payments']['payments'] as $payment) {
            $result[] = [
                $payment['invoice_id'],
                $payment['customer'],
                '$' . number_format($payment['amount'], 2),
                $payment['due_date']->format('Y-m-d'),
                $payment['days_until_due'] . ' days'
            ];
        }
        return $result;
    }

    public function headings(): array
    {
        return [
            'Invoice ID',
            'Customer',
            'Amount',
            'Due Date',
            'Days Until Due'
        ];
    }

    public function title(): string
    {
        return 'Upcoming Payments';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E2E2']
                ]
            ]
        ];
    }
}

class OverdueInvoicesSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $result = [];
        foreach ($this->data['overdue_invoices']['invoices'] as $invoice) {
            $result[] = [
                $invoice['invoice_id'],
                $invoice['customer'],
                '$' . number_format($invoice['amount'], 2),
                $invoice['due_date']->format('Y-m-d'),
                $invoice['days_overdue'] . ' days'
            ];
        }
        return $result;
    }

    public function headings(): array
    {
        return [
            'Invoice ID',
            'Customer',
            'Amount',
            'Due Date',
            'Days Overdue'
        ];
    }

    public function title(): string
    {
        return 'Overdue Invoices';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFF0000']
                ],
                'font' => ['color' => ['argb' => 'FFFFFFFF']]
            ]
        ];
    }
}
