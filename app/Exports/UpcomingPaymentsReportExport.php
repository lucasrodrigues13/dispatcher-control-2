<?php


namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;


class UpcomingPaymentsReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
            'Dias até Vencimento',
            'Termos de Pagamento',
            'Status',
            'Categoria de Prazo'
        ];
    }

    public function map($row): array
    {
        return [
            $row->invoice_id,
            $row->customer_name,
            $row->load_id,
            $row->amount,
            $row->invoice_date ? \Carbon\Carbon::parse($row->invoice_date)->format('d/m/Y') : 'N/A',
            $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d/m/Y') : 'N/A',
            $row->days_until_due,
            $row->payment_terms,
            $this->getStatusLabel($row->status),
            $this->getPaymentCategory($row->days_until_due)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '06B6D4']
            ]
        ]);

        $lastRow = $this->data->count() + 1;
        $sheet->getStyle("D2:D{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        return $sheet;
    }

    public function title(): string
    {
        return 'Pagamentos Futuros';
    }

    private function getStatusLabel($status): string
    {
        $labels = ['pending' => 'Pendente', 'confirmed' => 'Confirmado'];
        return $labels[$status] ?? ucfirst($status);
    }

    private function getPaymentCategory($days): string
    {
        if ($days <= 7) return 'Próximos 7 dias';
        if ($days <= 15) return '8-15 dias';
        if ($days <= 30) return '16-30 dias';
        return '31+ dias';
    }
}
