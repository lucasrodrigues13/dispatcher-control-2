<?php
namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ForecastReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'ID da Carga',
            'Cliente',
            'Transportadora',
            'Data Prevista Coleta',
            'Data Prevista Entrega',
            'Data Real Coleta',
            'Data Real Entrega',
            'Receita Prevista',
            'Status',
            'Status da Fatura',
            'Regra de Faturamento',
            'Dias até Entrega'
        ];
    }

    public function map($row): array
    {
        $scheduledDelivery = $row->scheduled_delivery_date ? \Carbon\Carbon::parse($row->scheduled_delivery_date) : null;
        $daysUntilDelivery = $scheduledDelivery ? $scheduledDelivery->diffInDays(\Carbon\Carbon::now(), false) : null;

        return [
            $row->load_id,
            $row->customer_name,
            $row->carrier_name,
            $row->scheduled_pickup_date ? \Carbon\Carbon::parse($row->scheduled_pickup_date)->format('d/m/Y') : 'N/A',
            $row->scheduled_delivery_date ? \Carbon\Carbon::parse($row->scheduled_delivery_date)->format('d/m/Y') : 'N/A',
            $row->actual_pickup_date ? \Carbon\Carbon::parse($row->actual_pickup_date)->format('d/m/Y') : 'Pendente',
            $row->actual_delivery_date ? \Carbon\Carbon::parse($row->actual_delivery_date)->format('d/m/Y') : 'Pendente',
            $row->forecasted_revenue,
            $this->getStatusLabel($row->status),
            $this->getInvoiceStatusLabel($row->invoice_status),
            $this->getBillingRuleLabel($row->billing_rule),
            $daysUntilDelivery !== null ? ($daysUntilDelivery > 0 ? "Em {$daysUntilDelivery} dias" : "Há " . abs($daysUntilDelivery) . " dias") : 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '8B5CF6']
            ]
        ]);

        $lastRow = $this->data->count() + 1;
        $sheet->getStyle("H2:H{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        // Total de receita prevista
        $totalRow = $lastRow + 2;
        $sheet->setCellValue("G{$totalRow}", 'RECEITA PREVISTA TOTAL:');
        $sheet->setCellValue("H{$totalRow}", "=SUM(H2:H{$lastRow})");

        $sheet->getStyle("G{$totalRow}:H{$totalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']]
        ]);

        return $sheet;
    }

    public function title(): string
    {
        return 'Previsão de Receitas';
    }

    private function getStatusLabel($status): string
    {
        $labels = [
            'to_be_invoiced' => 'A ser Faturado',
            'in_transit' => 'Em Trânsito',
            'delivered' => 'Entregue',
            'pending' => 'Pendente'
        ];
        return $labels[$status] ?? ucfirst($status);
    }

    private function getInvoiceStatusLabel($status): string
    {
        $labels = ['pending' => 'Pendente', 'generated' => 'Gerada', 'paid' => 'Paga'];
        return $labels[$status] ?? ucfirst($status);
    }

    private function getBillingRuleLabel($rule): string
    {
        $labels = [
            'actual_delivered_date' => 'Data Real de Entrega',
            'actual_picked_up_date' => 'Data Real de Coleta',
            'scheduled_delivery_date' => 'Data Programada de Entrega'
        ];
        return $labels[$rule] ?? ucfirst(str_replace('_', ' ', $rule));
    }
}
