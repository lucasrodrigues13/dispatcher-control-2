<?php


namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CommissionReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
            'ID do Funcionário',
            'Nome do Funcionário',
            'Cliente',
            'ID da Carga',
            'Valor Base',
            'Percentual (%)',
            'Valor da Comissão',
            'Data do Pagamento',
            'Status',
            'Mês/Ano'
        ];
    }

    public function map($row): array
    {
        return [
            $row->employee_id,
            $row->employee_name,
            $row->customer_name,
            $row->load_id,
            $row->base_amount,
            $row->commission_percentage,
            $row->commission_amount,
            $row->payment_date ? \Carbon\Carbon::parse($row->payment_date)->format('d/m/Y') : 'N/A',
            $this->getStatusLabel($row->status),
            $row->payment_date ? \Carbon\Carbon::parse($row->payment_date)->format('m/Y') : 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Cabeçalho
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '10B981']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Formatação monetária
        $lastRow = $this->data->count() + 1;
        $sheet->getStyle("E2:E{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $sheet->getStyle("G2:G{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $sheet->getStyle("F2:F{$lastRow}")->getNumberFormat()->setFormatCode('0.00"%"');

        // Total
        $totalRow = $lastRow + 2;
        $sheet->setCellValue("F{$totalRow}", 'TOTAL COMISSÕES:');
        $sheet->setCellValue("G{$totalRow}", "=SUM(G2:G{$lastRow})");

        $sheet->getStyle("F{$totalRow}:G{$totalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']]
        ]);

        return $sheet;
    }

    public function title(): string
    {
        return 'Relatório de Comissões';
    }

    private function getStatusLabel($status): string
    {
        $labels = ['paid' => 'Pago', 'pending' => 'Pendente', 'cancelled' => 'Cancelado'];
        return $labels[$status] ?? ucfirst($status);
    }
}
