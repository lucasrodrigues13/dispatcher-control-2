<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class VinDecoderController extends Controller
{
    /**
     * API da NHTSA para decodificação de VIN
     */
    private $nhtsaApiUrl = 'https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/';

    /**
     * Decodificar VIN usando a API da NHTSA
     */
    public function decodeVin(Request $request)
    {
        try {
            // Validação do VIN
            $request->validate([
                'vin' => [
                    'required',
                    'string',
                    'size:17',
                    'regex:/^[A-HJ-NPR-Z0-9]+$/i' // Exclui I, O, Q
                ]
            ], [
                'vin.required' => 'VIN é obrigatório',
                'vin.size' => 'VIN deve ter exatamente 17 caracteres',
                'vin.regex' => 'VIN contém caracteres inválidos (I, O, Q não são permitidos)'
            ]);

            $vin = strtoupper($request->input('vin'));

            // Verificar cache primeiro
            $cacheKey = "vin_decode_{$vin}";
            $cachedData = Cache::get($cacheKey);

            if ($cachedData) {
                return response()->json([
                    'success' => true,
                    'message' => 'VIN decodificado com sucesso (cache)',
                    'data' => $cachedData,
                    'from_cache' => true
                ]);
            }

            Log::info('Decodificação de VIN iniciada', ['vin' => $vin]);

            // Fazer chamada para a API da NHTSA
            $response = Http::timeout(15)
                ->retry(2, 1000) // 2 tentativas com 1 segundo de intervalo
                ->get($this->nhtsaApiUrl . $vin, [
                    'format' => 'json'
                ]);

            if (!$response->successful()) {
                Log::error('Erro na API da NHTSA', [
                    'vin' => $vin,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao conectar com o serviço de decodificação'
                ], 500);
            }

            $data = $response->json();

            if (empty($data['Results'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum resultado encontrado para este VIN'
                ], 404);
            }

            // Processar os dados retornados
            $vehicleInfo = $this->processVinData($data['Results']);

            // Armazenar no cache por 24 horas
            Cache::put($cacheKey, $vehicleInfo, now()->addHours(24));

            Log::info('VIN decodificado com sucesso', [
                'vin' => $vin,
                'year_make_model' => $vehicleInfo['year_make_model'] ?? 'N/A'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'VIN decodificado com sucesso',
                'data' => $vehicleInfo,
                'from_cache' => false
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'VIN inválido',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erro na decodificação de VIN', [
                'vin' => $request->input('vin'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Processar dados da API NHTSA
     */
    private function processVinData(array $results)
    {
        $vehicleData = [];

        // Extrair informações relevantes
        foreach ($results as $item) {
            if (isset($item['Variable']) && isset($item['Value'])) {
                $variable = $item['Variable'];
                $value = $item['Value'];

                // Filtrar valores válidos
                if (!empty($value) &&
                    $value !== 'Not Applicable' &&
                    $value !== null &&
                    $value !== '0' &&
                    $value !== 'N/A') {

                    $vehicleData[$variable] = $value;
                }
            }
        }

        // Verificar erros
        $errorCode = $vehicleData['Error Code'] ?? '';
        $hasError = !empty($errorCode) && $errorCode !== '0';

        if ($hasError) {
            $vehicleData['has_error'] = true;
            $vehicleData['error_message'] = $this->getErrorMessage($errorCode);
        } else {
            $vehicleData['has_error'] = false;
        }

        // Construir year_make_model
        $year = $vehicleData['Model Year'] ?? '';
        $make = $vehicleData['Make'] ?? '';
        $model = $vehicleData['Model'] ?? '';
        $vehicleData['year_make_model'] = trim("$year $make $model");

        // Organizar dados importantes
        $importantFields = [
            'Make',
            'Model',
            'Model Year',
            'Vehicle Type',
            'Body Class',
            'Engine Configuration',
            'Engine Number of Cylinders',
            'Displacement (L)',
            'Fuel Type - Primary',
            'Transmission Style',
            'Drive Type',
            'Manufacturer Name',
            'Plant Country',
            'Plant City',
            'Plant State',
            'Series',
            'Trim',
            'Doors',
            'GVWR'
        ];

        $processedData = [
            'year_make_model' => $vehicleData['year_make_model'],
            'has_error' => $vehicleData['has_error'],
            'details' => []
        ];

        if ($hasError) {
            $processedData['error_message'] = $vehicleData['error_message'];
        }

        // Adicionar apenas campos importantes que existem
        foreach ($importantFields as $field) {
            if (isset($vehicleData[$field])) {
                $processedData['details'][$field] = $vehicleData[$field];
            }
        }

        return $processedData;
    }

    /**
     * Mapear códigos de erro para mensagens amigáveis
     */
    private function getErrorMessage($errorCode)
    {
        $errorMessages = [
            '1' => 'VIN inválido - formato incorreto',
            '2' => 'VIN inválido - dígito de verificação incorreto',
            '3' => 'VIN inválido - ano não suportado',
            '4' => 'VIN inválido - fabricante não encontrado',
            '5' => 'VIN parcial - informações limitadas disponíveis',
            '6' => 'VIN incompleto',
            '7' => 'VIN não encontrado na base de dados'
        ];

        return $errorMessages[$errorCode] ?? "Erro na decodificação (código: $errorCode)";
    }

    /**
     * Validação rápida de VIN
     */
    public function validateVin(Request $request)
    {
        $vin = strtoupper($request->input('vin', ''));

        $validations = [
            'length_valid' => strlen($vin) === 17,
            'format_valid' => preg_match('/^[A-HJ-NPR-Z0-9]+$/', $vin),
            'no_forbidden_chars' => !preg_match('/[IOQ]/', $vin),
        ];

        $isValid = array_reduce($validations, function($carry, $item) {
            return $carry && $item;
        }, true);

        // Validação adicional do dígito verificador (9ª posição)
        $checkDigitValid = true;
        if ($isValid && strlen($vin) === 17) {
            $checkDigitValid = $this->validateCheckDigit($vin);
        }

        return response()->json([
            'valid' => $isValid && $checkDigitValid,
            'checks' => array_merge($validations, ['check_digit_valid' => $checkDigitValid]),
            'message' => ($isValid && $checkDigitValid) ? 'VIN válido' : 'VIN inválido',
            'vin' => $vin
        ]);
    }

    /**
     * Validar dígito verificador do VIN (simplificado)
     */
    private function validateCheckDigit($vin)
    {
        // Esta é uma validação básica do dígito verificador
        // A implementação completa seria mais complexa
        $weights = [8, 7, 6, 5, 4, 3, 2, 10, 0, 9, 8, 7, 6, 5, 4, 3, 2];
        $transliteration = [
            'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8,
            'J' => 1, 'K' => 2, 'L' => 3, 'M' => 4, 'N' => 5, 'P' => 7, 'R' => 9,
            'S' => 2, 'T' => 3, 'U' => 4, 'V' => 5, 'W' => 6, 'X' => 7, 'Y' => 8, 'Z' => 9
        ];

        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $char = $vin[$i];
            if (is_numeric($char)) {
                $value = intval($char);
            } else {
                $value = $transliteration[$char] ?? 0;
            }
            $sum += $value * $weights[$i];
        }

        $checkDigit = $sum % 11;
        $expectedCheckDigit = $checkDigit === 10 ? 'X' : strval($checkDigit);

        return $vin[8] === $expectedCheckDigit;
    }

    /**
     * Limpar cache de VIN (opcional - para administração)
     */
    public function clearVinCache(Request $request)
    {
        $vin = strtoupper($request->input('vin'));

        if ($vin) {
            Cache::forget("vin_decode_{$vin}");
            return response()->json([
                'success' => true,
                'message' => "Cache do VIN {$vin} removido"
            ]);
        }

        // Limpar todo o cache de VINs (cuidado!)
        Cache::flush();
        return response()->json([
            'success' => true,
            'message' => 'Todo o cache de VINs foi removido'
        ]);
    }

    /**
     * Estatísticas de uso (opcional)
     */
    public function getStats()
    {
        // Implementar estatísticas de uso se necessário
        return response()->json([
            'total_requests' => Cache::get('vin_total_requests', 0),
            'cache_hits' => Cache::get('vin_cache_hits', 0),
            'api_calls' => Cache::get('vin_api_calls', 0)
        ]);
    }
}
