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

class CarrierRevenueReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
            'ID da Transportadora',
            'Nome da Transportadora',
            'Cliente',
            'ID da Carga',
            'Preço (Price)',
            'Pago (Paid)',
            'Receita Utilizada',
            'Data da Carga',
            'Data de Entrega',
            'Status',
            'Diferença (Price - Paid)'
        ];
    }

    public function map($row): array
    {
        return [
            $row->carrier_id,
            $row->carrier_name,
            $row->customer_name,
            $row->load_id,
            $row->price,
            $row->paid,
            $row->revenue,
            $row->load_date ? \Carbon\Carbon::parse($row->load_date)->format('d/m/Y') : 'N/A',
            $row->delivery_date ? \Carbon\Carbon::parse($row->delivery_date)->format('d/m/Y') : 'N/A',
            $this->getStatusLabel($row->status),
            $row->price - $row->paid
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F59E0B']
            ]
        ]);

        $lastRow = $this->data->count() + 1;
        $sheet->getStyle("E2:G{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $sheet->getStyle("K2:K{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        return $sheet;
    }

    public function title(): string
    {
        return 'Receita por Transportadora';
    }

    private function getStatusLabel($status): string
    {
        $labels = ['delivered' => 'Entregue', 'in_transit' => 'Em Trânsito', 'pending' => 'Pendente'];
        return $labels[$status] ?? ucfirst($status);
    }
}
