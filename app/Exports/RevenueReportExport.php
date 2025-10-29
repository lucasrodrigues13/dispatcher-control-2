<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class RevenueReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    /**
     * Retorna a coleção de dados para exportação
     */
    public function collection()
    {
        return $this->data;
    }

    /**
     * Define os cabeçalhos das colunas
     */
    public function headings(): array
    {
        return [
            'ID da Carga',
            'Cliente',
            'Número da Fatura',
            'Data da Fatura',
            'Taxa do Dispatcher',
            'Status do Pagamento',
            'Data de Criação',
            'Mês/Ano',
            'Trimestre'
        ];
    }

    /**
     * Mapeia os dados para as colunas
     */
    public function map($row): array
    {
        return [
            $row->load_id,
            $row->customer_name,
            $row->invoice_number ?? 'N/A',
            $row->invoice_date ? \Carbon\Carbon::parse($row->invoice_date)->format('d/m/Y') : 'N/A',
            $row->dispatcher_fee,
            $this->getPaymentStatusLabel($row->payment_status),
            $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') : 'N/A',
            $row->invoice_date ? \Carbon\Carbon::parse($row->invoice_date)->format('m/Y') : 'N/A',
            $row->invoice_date ? $this->getQuarter(\Carbon\Carbon::parse($row->invoice_date)) : 'N/A'
        ];
    }

    /**
     * Aplica estilos à planilha
     */
    public function styles(Worksheet $sheet)
    {
        // Estilo do cabeçalho
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3B82F6']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]);

        // Formatar coluna de valores monetários
        $lastRow = $this->data->count() + 1;
        $sheet->getStyle("E2:E{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        // Bordas para toda a tabela
        $sheet->getStyle("A1:I{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Altura das linhas
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension('1')->setRowHeight(25);

        // Adicionar total no final
        $totalRow = $lastRow + 2;
        $sheet->setCellValue("D{$totalRow}", 'TOTAL:');
        $sheet->setCellValue("E{$totalRow}", "=SUM(E2:E{$lastRow})");

        $sheet->getStyle("D{$totalRow}:E{$totalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6']
            ]
        ]);

        $sheet->getStyle("E{$totalRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        return $sheet;
    }

    /**
     * Define o título da aba
     */
    public function title(): string
    {
        return 'Relatório de Receitas';
    }

    /**
     * Converte status de pagamento para label legível
     */
    private function getPaymentStatusLabel($status): string
    {
        $labels = [
            'paid' => 'Pago',
            'pending' => 'Pendente',
            'overdue' => 'Vencido',
            'cancelled' => 'Cancelado'
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Calcula o trimestre baseado na data
     */
    private function getQuarter(\Carbon\Carbon $date): string
    {
        $quarter = ceil($date->month / 3);
        return "Q{$quarter}/{$date->year}";
    }
}
