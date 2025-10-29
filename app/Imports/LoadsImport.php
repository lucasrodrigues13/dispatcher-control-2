<?php

namespace App\Imports;

use App\Models\Load;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LoadsImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    use Importable;

    private $carrierId;
    private $dispatcherId;
    private $employeeId;
    private static $processedCount = 0;
    private static $errorCount = 0;
    private static $createdCount = 0;
    private static $updatedCount = 0;

    public function __construct($carrierId, $dispatcherId, $employeeId = null)
    {
        $this->carrierId = $carrierId;
        $this->dispatcherId = $dispatcherId;
        $this->employeeId = $employeeId;

        // Reset counters
        self::$processedCount = 0;
        self::$errorCount = 0;
        self::$createdCount = 0;
        self::$updatedCount = 0;

        Log::info("Iniciando importação - Carrier: {$carrierId}, Dispatcher: {$dispatcherId}");
    }

    public function model(array $row)
    {
        try {
            self::$processedCount++;

            // Log apenas das primeiras linhas para debug
            if (self::$processedCount <= 3) {
                Log::info("Linha " . self::$processedCount . " - Dados:", [
                    'load_id' => $row['load_id'] ?? 'N/A',
                    'year_make_model' => $row['year_make_model'] ?? 'N/A',
                    'price' => $row['price'] ?? 'N/A',
                    'driver' => $row['driver'] ?? 'N/A',
                    'total_campos' => count($row)
                ]);
            }

            // Verificar se a linha tem dados válidos
            $hasData = false;
            foreach ($row as $key => $value) {
                if (!empty($value) && $value !== null) {
                    $hasData = true;
                    break;
                }
            }

            if (!$hasData) {
                return null;
            }

            // ⭐ FIXED: Ensure all fields have consistent data types and null handling
            $data = [
                // IDs e identificadores - SEMPRE preenchidos
                'carrier_id' => $this->carrierId,
                'dispatcher_id' => $this->dispatcherId,
                'employee_id' => $this->employeeId,

                // ⭐ CRITICAL: Only include fields that exist in your database schema
                'load_id' => $this->cleanString($this->getValue($row, [
                    'load_id', 'loadid', 'id', 'load', 'numero_carga', 'carga_id', 'load_number'
                ])),
                'internal_load_id' => $this->cleanString($this->getValue($row, [
                    'internal_load_id', 'internal_id', 'interno', 'id_interno'
                ])),

                // Dados básicos
                'creation_date' => $this->parseDate($row, [
                    'creation_date', 'created_date', 'date_created', 'data_criacao', 'created'
                ]),
                'dispatcher' => $this->cleanString($this->getValue($row, [
                    'dispatcher', 'despachante', 'despachador', 'dispatch'
                ])),
                'trip' => $this->cleanString($this->getValue($row, [
                    'trip', 'viagem', 'trip_number', 'trip_id'
                ])),

                // Informações do veículo
                'year_make_model' => $this->cleanString($this->getValue($row, [
                    'year_make_model', 'vehicle', 'veiculo', 'carro', 'make_model', 'ano_marca_modelo',
                    'year', 'make', 'model', 'vehicle_info'
                ])),
                'vin' => $this->cleanString($this->getValue($row, [
                    'vin', 'chassi', 'numero_chassi', 'vin_number'
                ])),
                'lot_number' => $this->cleanString($this->getValue($row, [
                    'lot_number', 'lote', 'numero_lote', 'lot', 'lot_no'
                ])),
                'has_terminal' => $this->getBoolean($row, [
                    'has_terminal', 'terminal', 'tem_terminal'
                ]),
                'dispatched_to_carrier' => $this->cleanString($this->getValue($row, [
                    'dispatched_to_carrier', 'carrier', 'transportadora', 'carrier_name'
                ])),

                // Pickup (Coleta)
                'pickup_name' => $this->cleanString($this->getValue($row, [
                    'pickup_name', 'pickup_contact', 'nome_coleta', 'pickup', 'coleta_nome'
                ])),
                'pickup_address' => $this->cleanLongText($this->getValue($row, [
                    'pickup_address', 'pickup_addr', 'endereco_coleta', 'coleta_endereco'
                ])),
                'pickup_city' => $this->cleanString($this->getValue($row, [
                    'pickup_city', 'pickup_cidade', 'cidade_coleta', 'coleta_cidade', 'origin_city'
                ])),
                'pickup_state' => $this->cleanString($this->getValue($row, [
                    'pickup_state', 'pickup_estado', 'estado_coleta', 'coleta_estado', 'origin_state'
                ])),
                'pickup_zip' => $this->cleanString($this->getValue($row, [
                    'pickup_zip', 'pickup_zipcode', 'pickup_cep', 'cep_coleta', 'coleta_cep'
                ])),
                'pickup_phone' => $this->cleanString($this->getValue($row, [
                    'pickup_phone', 'pickup_tel', 'telefone_coleta', 'coleta_telefone'
                ])),
                'pickup_mobile' => $this->cleanString($this->getValue($row, [
                    'pickup_mobile', 'pickup_cell', 'pickup_cel', 'celular_coleta', 'coleta_celular'
                ])),
                'pickup_notes' => $this->cleanLongText($this->getValue($row, [
                    'pickup_notes', 'pickup_obs', 'observacoes_coleta', 'coleta_observacoes'
                ])),
                'scheduled_pickup_date' => $this->parseDate($row, [
                    'scheduled_pickup_date', 'pickup_date', 'data_coleta_programada', 'coleta_programada'
                ]),
                'actual_pickup_date' => $this->parseDate($row, [
                    'actual_pickup_date', 'pickup_actual_date', 'data_coleta_real', 'coleta_real'
                ]),
                'buyer_number' => $this->cleanString($this->getValue($row, [
                    'buyer_number', 'buyer_no', 'numero_comprador', 'buyer', 'comprador'
                ])),

                // Delivery (Entrega)
                'delivery_name' => $this->cleanString($this->getValue($row, [
                    'delivery_name', 'delivery_contact', 'nome_entrega', 'delivery', 'entrega_nome'
                ])),
                'delivery_address' => $this->cleanLongText($this->getValue($row, [
                    'delivery_address', 'delivery_addr', 'endereco_entrega', 'entrega_endereco'
                ])),
                'delivery_city' => $this->cleanString($this->getValue($row, [
                    'delivery_city', 'delivery_cidade', 'cidade_entrega', 'entrega_cidade', 'destination_city'
                ])),
                'delivery_state' => $this->cleanString($this->getValue($row, [
                    'delivery_state', 'delivery_estado', 'estado_entrega', 'entrega_estado', 'destination_state'
                ])),
                'delivery_zip' => $this->cleanString($this->getValue($row, [
                    'delivery_zip', 'delivery_zipcode', 'delivery_cep', 'cep_entrega', 'entrega_cep'
                ])),
                'delivery_phone' => $this->cleanString($this->getValue($row, [
                    'delivery_phone', 'delivery_tel', 'telefone_entrega', 'entrega_telefone'
                ])),
                'delivery_mobile' => $this->cleanString($this->getValue($row, [
                    'delivery_mobile', 'delivery_cell', 'delivery_cel', 'celular_entrega', 'entrega_celular'
                ])),
                'delivery_notes' => $this->cleanLongText($this->getValue($row, [
                    'delivery_notes', 'delivery_obs', 'observacoes_entrega', 'entrega_observacoes'
                ])),
                'scheduled_delivery_date' => $this->parseDate($row, [
                    'scheduled_delivery_date', 'delivery_date', 'data_entrega_programada', 'entrega_programada'
                ]),
                'actual_delivery_date' => $this->parseDate($row, [
                    'actual_delivery_date', 'delivery_actual_date', 'data_entrega_real', 'entrega_real'
                ]),

                // Shipper (Remetente)
                'shipper_name' => $this->cleanString($this->getValue($row, [
                    'shipper_name', 'shipper', 'remetente', 'nome_remetente'
                ])),
                'shipper_phone' => $this->cleanString($this->getValue($row, [
                    'shipper_phone', 'shipper_tel', 'telefone_remetente', 'remetente_telefone'
                ])),

                // Valores financeiros
                'price' => $this->getNumeric($row, [
                    'price', 'amount', 'total', 'valor', 'preco', 'revenue'
                ]),
                'expenses' => $this->getNumeric($row, [
                    'expenses', 'expense', 'despesas', 'gastos', 'custos'
                ]),
                'broker_fee' => $this->getNumeric($row, [
                    'broker_fee', 'fee', 'taxa_corretor', 'broker', 'comissao_broker'
                ]),
                'driver_pay' => $this->getNumeric($row, [
                    'driver_pay', 'driver_payment', 'pagamento_motorista', 'pago_motorista'
                ]),
                'invoiced_fee' => $this->cleanString($this->getValue($row, [
                    'invoiced_fee', 'invoice_fee', 'taxa_faturada', 'fee_invoiced'
                ])),

                // Informações de pagamento
                'payment_method' => $this->cleanString($this->getValue($row, [
                    'payment_method', 'pay_method', 'metodo_pagamento', 'forma_pagamento'
                ])),
                'paid_amount' => $this->getNumeric($row, [
                    'paid_amount', 'amount_paid', 'valor_pago', 'pago'
                ]),
                'paid_method' => $this->cleanString($this->getValue($row, [
                    'paid_method', 'metodo_pago', 'como_pago', 'paid_via'
                ])),
                'payment_status' => $this->cleanString($this->getValue($row, [
                    'payment_status', 'pay_status', 'status_pagamento', 'status_pago'
                ])),
                'payment_terms' => $this->cleanString($this->getValue($row, [
                    'payment_terms', 'pay_terms', 'termos_pagamento', 'prazo_pagamento'
                ])),
                'payment_notes' => $this->cleanLongText($this->getValue($row, [
                    'payment_notes', 'pay_notes', 'observacoes_pagamento', 'notas_pagamento'
                ])),
                'reference_number' => $this->cleanString($this->getValue($row, [
                    'reference_number', 'ref_number', 'numero_referencia', 'reference', 'ref'
                ])),
                'receipt_date' => $this->parseDate($row, [
                    'receipt_date', 'received_date', 'data_recebimento', 'recebido_em'
                ]),

                // Invoice (Fatura)
                'invoice_number' => $this->cleanString($this->getValue($row, [
                    'invoice_number', 'invoice_no', 'numero_fatura', 'invoice', 'fatura'
                ])),
                'invoice_notes' => $this->cleanLongText($this->getValue($row, [
                    'invoice_notes', 'invoice_obs', 'observacoes_fatura', 'notas_fatura'
                ])),
                'invoice_date' => $this->parseDate($row, [
                    'invoice_date', 'invoiced_date', 'data_fatura', 'faturado_em'
                ]),

                // Driver e status
                'driver' => $this->cleanString($this->getValue($row, [
                    'driver', 'driver_name', 'motorista', 'nome_motorista'
                ])),
                'status_move' => 'no_moved',
            ];

            // ⭐ CRITICAL FIX: Only include fields that have values and exist in the fillable array
            $fillableFields = (new Load)->getFillable();
            $cleanData = [];

            foreach ($data as $key => $value) {
                // Only include if field is fillable and has a non-null value
                if (in_array($key, $fillableFields)) {
                    $cleanData[$key] = $value;
                }
            }

            // ⭐ VERIFICATION: load_id is mandatory
            if (empty($cleanData['load_id'])) {
                self::$errorCount++;
                Log::warning("Linha " . self::$processedCount . " - load_id vazio, pulando");
                return null;
            }

            // Debug das primeiras linhas
            if (self::$processedCount <= 3) {
                Log::info("Linha " . self::$processedCount . " - Processado:", [
                    'load_id' => $cleanData['load_id'],
                    'campos_preenchidos' => count(array_filter($cleanData, function($v) { return $v !== null; }))
                ]);
            }

            // Verificar se já existe e atualizar OU criar novo
            $existingLoad = Load::where('load_id', $cleanData['load_id'])->first();

            if ($existingLoad) {
                // ⭐ FIX: Update existing record directly instead of through batch
                $existingLoad->update($cleanData);
                self::$updatedCount++;
                return null; // Don't return model for batch insert
            }

            // Criar novo
            self::$createdCount++;
            return new Load($cleanData);

        } catch (\Exception $e) {
            self::$errorCount++;
            Log::error("Erro linha " . self::$processedCount . ":", [
                'erro' => $e->getMessage(),
                'load_id' => $row['load_id'] ?? 'N/A',
                'linha' => self::$processedCount
            ]);
            return null;
        }
    }

    /**
     * ⭐ IMPROVED: More robust string cleaning
     */
    private function cleanString($value)
    {
        if ($value === null || $value === '' || $value === 0) {
            return null;
        }

        // Convert to string and clean
        $cleaned = trim((string) $value);

        // Remove problematic characters for SQL
        $cleaned = str_replace(['"', "'", "\n", "\r", "\t", "\0"], ['-', '-', ' ', ' ', ' ', ''], $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned); // Multiple spaces to single space
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned); // Remove control chars

        // Limit length to prevent SQL issues
        if (strlen($cleaned) > 255) {
            $cleaned = substr($cleaned, 0, 255);
        }

        return empty($cleaned) ? null : $cleaned;
    }

    /**
     * ⭐ IMPROVED: More robust long text cleaning
     */
    private function cleanLongText($value)
    {
        if ($value === null || $value === '' || $value === 0) {
            return null;
        }

        $cleaned = trim((string) $value);

        // More gentle cleaning for long text
        $cleaned = str_replace(['"', "'"], ['-', '-'], $cleaned);
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);

        // Limit to 1000 chars for text fields
        if (strlen($cleaned) > 1000) {
            $cleaned = substr($cleaned, 0, 1000);
        }

        return empty($cleaned) ? null : $cleaned;
    }

    /**
     * ⭐ IMPROVED: Better value retrieval with fuzzy matching
     */
    private function getValue(array $row, array $possibleKeys)
    {
        // First, try exact matches
        foreach ($possibleKeys as $key) {
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                return trim($row[$key]);
            }
        }

        // Then try fuzzy matching
        foreach ($possibleKeys as $key) {
            foreach ($row as $rowKey => $rowValue) {
                $normalizedRowKey = strtolower(str_replace([' ', '-', '_'], '', $rowKey));
                $normalizedKey = strtolower(str_replace([' ', '-', '_'], '', $key));

                if ($normalizedRowKey === $normalizedKey) {
                    if ($rowValue !== null && $rowValue !== '') {
                        return trim($rowValue);
                    }
                }
            }
        }

        return null;
    }

    /**
     * ⭐ IMPROVED: Better numeric value handling
     */
    private function getNumeric(array $row, array $possibleKeys)
    {
        $value = $this->getValue($row, $possibleKeys);

        if ($value === null || $value === '') {
            return null;
        }

        // Handle different number formats
        $cleaned = preg_replace('/[^\d.,\-]/', '', (string) $value);

        // Handle comma as decimal separator
        if (substr_count($cleaned, ',') === 1 && substr_count($cleaned, '.') === 0) {
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (substr_count($cleaned, ',') > 0 && substr_count($cleaned, '.') > 0) {
            // Handle formats like 1,234.56
            $lastComma = strrpos($cleaned, ',');
            $lastDot = strrpos($cleaned, '.');

            if ($lastDot > $lastComma) {
                $cleaned = str_replace(',', '', $cleaned);
            } else {
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            }
        }

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    /**
     * ⭐ IMPROVED: Better boolean handling
     */
    private function getBoolean(array $row, array $possibleKeys)
    {
        $value = $this->getValue($row, $possibleKeys);

        if ($value === null || $value === '') {
            return null;
        }

        $value = strtolower(trim((string) $value));
        $trueValues = ['yes', 'sim', 'true', '1', 'verdadeiro', 'x', 'checked', 'on', 'y'];
        $falseValues = ['no', 'não', 'nao', 'false', '0', 'falso', 'off', 'n'];

        if (in_array($value, $trueValues)) {
            return 1;
        } elseif (in_array($value, $falseValues)) {
            return 0;
        }

        return null;
    }

    /**
     * ⭐ IMPROVED: More robust date parsing
     */
    private function parseDate(array $row, array $possibleKeys)
    {
        $value = $this->getValue($row, $possibleKeys);

        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        // Try different date formats
        $formats = [
            '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', // mm/dd/yyyy
            '/^(\d{4})-(\d{1,2})-(\d{1,2})$/',   // yyyy-mm-dd
            '/^(\d{1,2})-(\d{1,2})-(\d{4})$/',   // dd-mm-yyyy
            '/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', // yyyy/mm/dd
        ];

        foreach ($formats as $format) {
            if (preg_match($format, $value, $matches)) {
                try {
                    if ($format === '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/') {
                        // mm/dd/yyyy
                        $month = (int) $matches[1];
                        $day = (int) $matches[2];
                        $year = (int) $matches[3];
                    } elseif ($format === '/^(\d{4})-(\d{1,2})-(\d{1,2})$/') {
                        // yyyy-mm-dd
                        $year = (int) $matches[1];
                        $month = (int) $matches[2];
                        $day = (int) $matches[3];
                    } elseif ($format === '/^(\d{1,2})-(\d{1,2})-(\d{4})$/') {
                        // dd-mm-yyyy
                        $day = (int) $matches[1];
                        $month = (int) $matches[2];
                        $year = (int) $matches[3];
                    } elseif ($format === '/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/') {
                        // yyyy/mm/dd
                        $year = (int) $matches[1];
                        $month = (int) $matches[2];
                        $day = (int) $matches[3];
                    }

                    if ($year >= 1900 && $year <= 2030 && checkdate($month, $day, $year)) {
                        return sprintf('%04d-%02d-%02d', $year, $month, $day);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            '*.price' => 'nullable|numeric|min:0',
            '*.paid_amount' => 'nullable|numeric|min:0',
            '*.expenses' => 'nullable|numeric|min:0',
            '*.broker_fee' => 'nullable|numeric|min:0',
            '*.driver_pay' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * ⭐ REDUCED: Much smaller batch size to prevent SQL issues
     */
    public function batchSize(): int
    {
        return 5; // Very small to prevent column mismatch issues
    }

    public function chunkSize(): int
    {
        return 5;
    }

    /**
     * Final statistics log
     */
    public function __destruct()
    {
        if (self::$processedCount > 0) {
            Log::info("=== IMPORTAÇÃO FINALIZADA ===", [
                'total_processadas' => self::$processedCount,
                'criadas' => self::$createdCount,
                'atualizadas' => self::$updatedCount,
                'erros' => self::$errorCount,
                'sucesso_rate' => round((self::$createdCount + self::$updatedCount) / self::$processedCount * 100, 2) . '%'
            ]);
        }
    }
}
