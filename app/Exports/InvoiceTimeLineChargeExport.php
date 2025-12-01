<?php

namespace App\Exports;

use App\Models\TimeLineCharge;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class InvoiceTimeLineChargeExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithColumnWidths, WithEvents, WithCustomStartCell
{
    protected $charge;
    protected $loads;
    protected $selectedColumns;
    protected $columnLabels;

    public function __construct(TimeLineCharge $charge, Collection $loads, array $selectedColumns = [])
    {
        $this->charge = $charge;
        $this->loads = $loads;
        $this->selectedColumns = $selectedColumns;
        
        // Mapear nomes de colunas para labels bonitos
        $this->columnLabels = [
            'load_id' => 'Load ID',
            'internal_load_id' => 'Internal Load ID',
            'year_make_model' => 'Vehicle',
            'vin' => 'VIN',
            'lot_number' => 'Lot Number',
            'creation_date' => 'Creation Date',
            'scheduled_pickup_date' => 'Scheduled Pickup',
            'actual_pickup_date' => 'Actual Pickup',
            'scheduled_delivery_date' => 'Scheduled Delivery',
            'actual_delivery_date' => 'Actual Delivery',
            'pickup_name' => 'Pickup Name',
            'delivery_name' => 'Delivery Name',
            'price' => 'Price ($)',
            'paid_amount' => 'Paid Amount ($)',
            'dispatcher' => 'Dispatcher',
            'driver' => 'Driver',
            'broker_fee' => 'Broker Fee ($)',
            'driver_pay' => 'Driver Pay ($)',
            'payment_status' => 'Payment Status',
            'invoice_number' => 'Invoice Number',
            'invoice_date' => 'Invoice Date',
            'receipt_date' => 'Receipt Date',
        ];
    }

    public function collection()
    {
        return $this->loads;
    }

    public function headings(): array
    {
        // Se colunas específicas foram selecionadas, usar apenas elas
        if (!empty($this->selectedColumns)) {
            return array_map(function($col) {
                return $this->columnLabels[$col] ?? ucwords(str_replace('_', ' ', $col));
            }, $this->selectedColumns);
        }
        
        // Caso contrário, usar colunas padrão
        return [
            'Load ID',
            'Vehicle',
            'Price ($)',
            'Dispatcher',
            'Driver',
            'Broker Fee ($)',
            'Driver Pay ($)',
            'Payment Status',
        ];
    }

    public function map($load): array
    {
        $columns = !empty($this->selectedColumns) ? $this->selectedColumns : [
            'load_id', 'year_make_model', 'price', 'dispatcher', 
            'driver', 'broker_fee', 'driver_pay', 'payment_status'
        ];
        
        $row = [];
        foreach ($columns as $column) {
            $row[] = $this->getColumnValue($load, $column);
        }
        
        return $row;
    }

    protected function getColumnValue($load, $column)
    {
        // Converter para array se for objeto para facilitar acesso
        $data = is_object($load) ? (array) $load : $load;
        
        // Função auxiliar para acessar valor (suporta objeto e array)
        $getValue = function($key) use ($load, $data) {
            if (is_object($load)) {
                return $load->$key ?? $load->{$key} ?? null;
            }
            return $data[$key] ?? null;
        };
        
        switch ($column) {
            case 'load_id':
                return $getValue('load_id') ?? '-';
            
            case 'internal_load_id':
                return $getValue('internal_load_id') ?? '-';
            
            case 'year_make_model':
                return $getValue('year_make_model') ?? '-';
            
            case 'vin':
                return $getValue('vin') ?? '-';
            
            case 'lot_number':
                return $getValue('lot_number') ?? '-';
            
            case 'creation_date':
            case 'scheduled_pickup_date':
            case 'actual_pickup_date':
            case 'scheduled_delivery_date':
            case 'actual_delivery_date':
            case 'invoice_date':
            case 'receipt_date':
                $date = $getValue($column);
                if (!$date) return '-';
                // Se já for string formatada, retornar como está
                if (is_string($date) && preg_match('/\d{2}\/\d{2}\/\d{4}/', $date)) {
                    return $date;
                }
                try {
                    return \Carbon\Carbon::parse($date)->format('m/d/Y');
                } catch (\Exception $e) {
                    return '-';
                }
            
            case 'pickup_name':
                return $getValue('pickup_name') ?? '-';
            
            case 'delivery_name':
                return $getValue('delivery_name') ?? '-';
            
            case 'price':
            case 'paid_amount':
            case 'broker_fee':
            case 'driver_pay':
                $value = $getValue($column);
                if ($value === null || $value === '') return 0;
                return is_numeric($value) ? (float)$value : 0;
            
            case 'dispatcher':
                // Tentar diferentes formas de acessar o dispatcher
                $dispatcher = $getValue('dispatcher');
                if ($dispatcher) return $dispatcher;
                
                $dispatcherName = $getValue('dispatcher_name');
                if ($dispatcherName) return $dispatcherName;
                
                // Tentar através de relacionamento se for objeto
                if (is_object($load) && isset($load->dispatcher)) {
                    if (is_object($load->dispatcher)) {
                        return $load->dispatcher->user->name ?? $load->dispatcher->name ?? '-';
                    }
                }
                
                $dispatcherId = $getValue('dispatcher_id');
                return $dispatcherId ? "Dispatcher ID: {$dispatcherId}" : '-';
            
            case 'driver':
                return $getValue('driver') ?? $getValue('driver_name') ?? '-';
            
            case 'payment_status':
                $status = $getValue('payment_status');
                return $this->formatPaymentStatus($status ?? 'pending');
            
            case 'invoice_number':
                return $getValue('invoice_number') ?? '-';
            
            default:
                return $getValue($column) ?? '-';
        }
    }

    protected function formatPaymentStatus($status)
    {
        $statusLabels = [
            'paid' => 'Paid',
            'pending' => 'Pending',
            'overdue' => 'Overdue',
            'partial' => 'Partial',
        ];
        
        return $statusLabels[strtolower($status)] ?? ucfirst($status);
    }

    public function columnWidths(): array
    {
        $widths = [];
        $columns = !empty($this->selectedColumns) ? $this->selectedColumns : [
            'load_id', 'year_make_model', 'price', 'dispatcher', 
            'driver', 'broker_fee', 'driver_pay', 'payment_status'
        ];
        
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        
        foreach ($columns as $index => $column) {
            $letter = $columnLetters[$index] ?? chr(65 + $index);
            
            // Definir larguras específicas baseadas no tipo de coluna
            if (in_array($column, ['load_id', 'internal_load_id'])) {
                $widths[$letter] = 15;
            } elseif (in_array($column, ['year_make_model', 'pickup_name', 'delivery_name'])) {
                $widths[$letter] = 25;
            } elseif (strpos($column, 'date') !== false) {
                $widths[$letter] = 15;
            } elseif (in_array($column, ['price', 'paid_amount', 'broker_fee', 'driver_pay'])) {
                $widths[$letter] = 15;
            } else {
                $widths[$letter] = 18;
            }
        }
        
        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $this->getLastColumn();
        $headerRow = 4; // Cabeçalho começa na linha 4 (com WithCustomStartCell)
        $dataStartRow = 5; // Dados começam na linha 5 (após cabeçalho)
        $lastDataRow = $dataStartRow + $this->loads->count() - 1;
        $totalRow = $lastDataRow + 1;
        
        // ⭐ ESTILO DO CABEÇALHO DA TABELA (igual ao PDF - azul escuro #013d81)
        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '013D81'], // Azul escuro igual ao PDF
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // ⭐ NOTA: Informações da invoice são adicionadas no método registerEvents()
        // para garantir que sejam inseridas antes dos dados

        // ⭐ ESTILO DAS CÉLULAS DE DADOS
        $sheet->getStyle("A{$dataStartRow}:{$lastColumn}{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'DDDDDD'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // ⭐ ALTERNAR CORES DAS LINHAS (zebra striping)
        for ($row = $dataStartRow; $row <= $lastDataRow; $row++) {
            if (($row - $dataStartRow) % 2 == 1) {
                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FBFCFD'],
                    ],
                ]);
            }
        }

        // ⭐ FORMATAR COLUNAS DE VALORES MONETÁRIOS
        $columns = !empty($this->selectedColumns) ? $this->selectedColumns : [
            'load_id', 'year_make_model', 'price', 'dispatcher', 
            'driver', 'broker_fee', 'driver_pay', 'payment_status'
        ];
        
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        
        foreach ($columns as $index => $column) {
            $letter = $columnLetters[$index] ?? chr(65 + $index);
            
            if (in_array($column, ['price', 'paid_amount', 'broker_fee', 'driver_pay'])) {
                $sheet->getStyle("{$letter}{$dataStartRow}:{$letter}{$lastDataRow}")->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
            }
        }

        // ⭐ LINHA DE TOTAL
        $priceColumnIndex = array_search('price', $columns);
        $priceColumn = $priceColumnIndex !== false ? ($columnLetters[$priceColumnIndex] ?? 'C') : 'C';
        
        // Calcular qual coluna vem antes do price para fazer merge
        $mergeEndColumn = $priceColumnIndex > 0 ? $columnLetters[$priceColumnIndex - 1] : 'A';
        
        $sheet->setCellValue('A' . $totalRow, 'TOTAL');
        if ($priceColumnIndex > 0) {
            $sheet->mergeCells("A{$totalRow}:{$mergeEndColumn}{$totalRow}");
        }
        
        $totalAmount = $this->loads->sum('price') ?? 0;
        $sheet->setCellValue($priceColumn . $totalRow, $totalAmount);
        
        $sheet->getStyle("A{$totalRow}:{$lastColumn}{$totalRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '28A745'], // Verde igual ao PDF
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        
        $sheet->getStyle($priceColumn . $totalRow)->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        // ⭐ CONGELAR CABEÇALHO DA TABELA
        $sheet->freezePane('A' . $dataStartRow);

        return $sheet;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $this->getLastColumn();
                
                // ⭐ ADICIONAR INFORMAÇÕES DA INVOICE NAS LINHAS 1-3
                // Linha 1: Título
                $sheet->setCellValue('A1', 'Invoice Information');
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '013D81'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Linha 2: Informações detalhadas
                $sheet->setCellValue('A2', 'Invoice ID: ' . ($this->charge->invoice_id ?? 'N/A'));
                $sheet->setCellValue('D2', 'Carrier: ' . ($this->charge->carrier->user->name ?? 'N/A'));
                $sheet->setCellValue('G2', 'Due Date: ' . ($this->charge->due_date ? \Carbon\Carbon::parse($this->charge->due_date)->format('m/d/Y') : 'N/A'));
                $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                    'font' => ['size' => 10, 'bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                // Linha 3: Vazia (espaçamento)
                $sheet->getRowDimension(3)->setRowHeight(5);
                
                // Ajustar altura da linha de informações
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(20);
                
                // Ajustar altura da linha do cabeçalho da tabela (linha 4)
                $sheet->getRowDimension(4)->setRowHeight(25);
                
                // Ajustar altura das linhas de dados
                $dataStartRow = 5;
                for ($row = $dataStartRow; $row <= $dataStartRow + $this->loads->count() - 1; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }
                
                // Ajustar altura da linha de total
                $totalRow = $dataStartRow + $this->loads->count();
                $sheet->getRowDimension($totalRow)->setRowHeight(25);
            },
        ];
    }

    protected function getLastColumn()
    {
        $columns = !empty($this->selectedColumns) ? $this->selectedColumns : [
            'load_id', 'year_make_model', 'price', 'dispatcher', 
            'driver', 'broker_fee', 'driver_pay', 'payment_status'
        ];
        
        $count = count($columns);
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        
        return $columnLetters[$count - 1] ?? 'Z';
    }

    public function startCell(): string
    {
        return 'A4'; // Começar dados na linha 4 (linhas 1-3 são para informações da invoice)
    }

    public function title(): string
    {
        return 'Invoice ' . ($this->charge->invoice_id ?? 'Details');
    }
}

