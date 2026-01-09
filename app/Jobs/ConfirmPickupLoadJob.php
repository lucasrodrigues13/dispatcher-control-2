<?php

namespace App\Jobs;

use App\Models\Load;
use App\Models\LoadPickupConfirmationAttempt;
use App\Services\LoadService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class ConfirmPickupLoadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $loadId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $attempt = null;
        try {
            // Buscar load sem TenantScope para garantir que encontra mesmo que seja de outro tenant
            $load = Load::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                ->findOrFail($this->loadId);

            // Buscar tentativa pendente para este load
            $attempt = LoadPickupConfirmationAttempt::getPendingAttempt($this->loadId);
            
            if ($attempt) {
                // Atualizar status para processing
                $attempt->update(['status' => 'processing']);
            }

            // Verificar se o load ainda está em status assigned
            if ($load->kanban_status !== 'assigned') {
                Log::info("Load {$this->loadId} is not in assigned status, skipping confirmation", [
                    'current_status' => $load->kanban_status
                ]);
                
                // Marcar tentativa como failed se existir
                if ($attempt) {
                    $attempt->update([
                        'status' => 'failed',
                        'error_message' => "Load is not in assigned status. Current status: {$load->kanban_status}"
                    ]);
                }
                
                // Job completed successfully (no error, just skip)
                return;
            }

            // Preparar payload para N8N
            $payload = $this->preparePayloadForN8N($load);

            // Enviar para N8N
            $this->sendToN8N($payload);

            Log::info("Successfully sent load {$this->loadId} to N8N for pickup confirmation", [
                'load_id' => $load->id,
                'load_identifier' => $load->load_id ?? $load->internal_load_id,
                'attempt_id' => $attempt?->id,
            ]);

            // Atualizar tentativa: manter como processing até receber confirmação do webhook
            // O webhook atualizará para 'completed' quando a confirmação for recebida
            // Não atualizamos aqui para completed porque ainda não recebemos a confirmação

        } catch (\Exception $e) {
            // Log error and update attempt status
            Log::warning("ConfirmPickupLoadJob: Error processing load {$this->loadId}", [
                'error' => $e->getMessage(),
                'attempt_id' => $attempt?->id,
            ]);
            
            // Atualizar tentativa como failed
            if ($attempt) {
                $attempt->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
            
            // Job completes without error (silent failure)
            // This prevents jobs from going to failed_jobs table
            return;
        }
    }

    /**
     * Prepare payload for N8N webhook
     */
    private function preparePayloadForN8N(Load $load): array
    {
        return [
            'load' => [
                'id' => $load->id,
                'load_id' => $load->load_id,
                'internal_load_id' => $load->internal_load_id,
                'creation_date' => $load->creation_date?->format('Y-m-d'),
                'dispatcher' => $load->dispatcher,
                'trip' => $load->trip,
                'year_make_model' => $load->year_make_model,
                'vin' => $load->vin,
                'lot_number' => $load->lot_number,
                'has_terminal' => $load->has_terminal,
                'dispatched_to_carrier' => $load->dispatched_to_carrier,
                'pickup_name' => $load->pickup_name,
                'pickup_address' => $load->pickup_address,
                'pickup_city' => $load->pickup_city,
                'pickup_state' => $load->pickup_state,
                'pickup_zip' => $load->pickup_zip,
                'scheduled_pickup_date' => $load->scheduled_pickup_date?->format('Y-m-d'),
                'pickup_phone' => $load->pickup_phone,
                'pickup_mobile' => $load->pickup_mobile,
                'actual_pickup_date' => $load->actual_pickup_date?->format('Y-m-d'),
                'buyer_number' => $load->buyer_number,
                'pickup_notes' => $load->pickup_notes,
                'delivery_name' => $load->delivery_name,
                'delivery_address' => $load->delivery_address,
                'delivery_city' => $load->delivery_city,
                'delivery_state' => $load->delivery_state,
                'delivery_zip' => $load->delivery_zip,
                'scheduled_delivery_date' => $load->scheduled_delivery_date?->format('Y-m-d'),
                'actual_delivery_date' => $load->actual_delivery_date?->format('Y-m-d'),
                'delivery_phone' => $load->delivery_phone,
                'delivery_mobile' => $load->delivery_mobile,
                'delivery_notes' => $load->delivery_notes,
                'shipper_name' => $load->shipper_name,
                'shipper_phone' => $load->shipper_phone,
                'price' => $load->price,
                'expenses' => $load->expenses,
                'broker_fee' => $load->broker_fee,
                'driver_pay' => $load->driver_pay,
                'payment_method' => $load->payment_method,
                'paid_amount' => $load->paid_amount,
                'paid_method' => $load->paid_method,
                'reference_number' => $load->reference_number,
                'receipt_date' => $load->receipt_date?->format('Y-m-d'),
                'payment_terms' => $load->payment_terms,
                'payment_notes' => $load->payment_notes,
                'payment_status' => $load->payment_status,
                'invoice_number' => $load->invoice_number,
                'invoice_notes' => $load->invoice_notes,
                'invoice_date' => $load->invoice_date?->format('Y-m-d'),
                'driver' => $load->driver,
                'kanban_status' => $load->kanban_status,
                'carrier' => $load->carrier ? [
                    'id' => $load->carrier->id,
                    'company_name' => $load->carrier->company_name,
                    'email' => $load->carrier->user->email ?? null,
                ] : null,
                'dispatcher_info' => $load->dispatcher ? [
                    'name' => $load->dispatcher,
                ] : null,
            ],
            'timestamp' => now()->toIso8601String(),
            'source' => 'dispatcher-control',
            'webhook_callback_url' => URL::route('webhook.n8n.pickup-confirmation'),
        ];
    }

    /**
     * Send load data to N8N webhook
     */
    private function sendToN8N(array $payload): void
    {
        $webhookUrl = config('services.n8n.webhook_url');
        
        if (!$webhookUrl) {
            throw new \Exception('N8N webhook URL is not configured');
        }

        $client = new Client();
        
        $response = $client->post($webhookUrl, [
            'json' => $payload,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
        ]);

        $statusCode = $response->getStatusCode();
        
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \Exception("N8N webhook returned status code: {$statusCode}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ConfirmPickupLoadJob failed permanently for load {$this->loadId}", [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Atualizar tentativa como failed permanentemente
        $attempt = LoadPickupConfirmationAttempt::getPendingAttempt($this->loadId);
        if ($attempt) {
            $attempt->update([
                'status' => 'failed',
                'error_message' => 'Job failed permanently after ' . $this->attempts() . ' attempts: ' . $exception->getMessage()
            ]);
        }
        
        // Aqui você pode adicionar lógica para notificar o usuário
        // ou marcar o load como necessitando intervenção manual
        $load = Load::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->find($this->loadId);
        if ($load) {
            // Opcional: marcar o load com algum status especial para intervenção
            // $load->update(['pickup_status' => 'REQUIRES_MANUAL_INTERVENTION']);
        }
    }
}
