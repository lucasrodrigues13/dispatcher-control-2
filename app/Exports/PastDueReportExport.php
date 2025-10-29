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

class PastDueReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
            'ID da Fatura',
            'Cliente',
            'ID da Carga',
            'Valor',
            'Data da Fatura',
            'Data de Vencimento',
            'Dias em Atraso',
            'Termos de Pagamento',
            'Status',
            'Categoria de Atraso',
            'Valor de Juros'
        ];
    }

    public function map($row): array
    {
        $interestAmount = $this->calculateInterest($row->amount, $row->days_overdue);

        return [
            $row->invoice_id,
            $row->customer_name,
            $row->load_id,
            $row->amount,
            $row->invoice_date ? \Carbon\Carbon::parse($row->invoice_date)->format('d/m/Y') : 'N/A',
            $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d/m/Y') : 'N/A',
            $row->days_overdue,
            $row->payment_terms,
            $this->getStatusLabel($row->status),
            $row->overdue_category,
            $interestAmount
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EF4444']
            ]
        ]);

        $lastRow = $this->data->count() + 1;
        $sheet->getStyle("D2:D{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $sheet->getStyle("K2:K{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        // Destacar linhas com muito atraso
        for ($row = 2; $row <= $lastRow; $row++) {
            $daysOverdue = $sheet->getCell("G{$row}")->getValue();
            if ($daysOverdue > 90) {
                $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']]
                ]);
            }
        }

        return $sheet;
    }

    public function title(): string
    {
        return 'Faturas Vencidas';
    }

    private function getStatusLabel($status): string
    {
        $labels = ['overdue' => 'Vencido', 'partially_paid' => 'Parcialmente Pago'];
        return $labels[$status] ?? ucfirst($status);
    }

    private function calculateInterest($amount, $daysOverdue): float
    {
        // Taxa de juros fictícia: 0.5% ao mês (0.0167% ao dia)
        $dailyRate = 0.0167 / 100;
        return $amount * $dailyRate * $daysOverdue;
    }
}
