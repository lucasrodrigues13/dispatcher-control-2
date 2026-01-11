<?php

namespace App\Jobs;

use App\Models\LoadPickupConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchVapiCallDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The confirmation ID
     */
    public $confirmationId;

    /**
     * The VAPI call ID
     */
    public $vapiCallId;

    /**
     * Create a new job instance.
     */
    public function __construct($confirmationId, $vapiCallId)
    {
        $this->confirmationId = $confirmationId;
        $this->vapiCallId = $vapiCallId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $confirmation = LoadPickupConfirmation::find($this->confirmationId);
            
            if (!$confirmation) {
                Log::warning('FetchVapiCallDetailsJob: Confirmation not found', [
                    'confirmation_id' => $this->confirmationId,
                    'vapi_call_id' => $this->vapiCallId,
                ]);
                return;
            }

            $apiKey = config('services.vapi.api_key');
            
            if (!$apiKey) {
                Log::error('FetchVapiCallDetailsJob: VAPI API key not configured');
                return;
            }

            // Call VAPI API to get call details
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->get("https://api.vapi.ai/call/{$this->vapiCallId}");

            if (!$response->successful()) {
                Log::error('FetchVapiCallDetailsJob: VAPI API request failed', [
                    'confirmation_id' => $this->confirmationId,
                    'vapi_call_id' => $this->vapiCallId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return;
            }

            $data = $response->json();

            // Update confirmation with additional data
            $updateData = [];

            // Update summary if present
            if (isset($data['summary'])) {
                $updateData['summary'] = $data['summary'];
            }

            // Update recordingUrl (call_record_url)
            if (isset($data['recordingUrl'])) {
                $updateData['call_record_url'] = $data['recordingUrl'];
            }

            // Update transcription (full transcript text)
            if (isset($data['transcript'])) {
                $updateData['transcription'] = $data['transcript'];
            }

            // Update confirmation
            if (!empty($updateData)) {
                $confirmation->update($updateData);
                
                Log::info('FetchVapiCallDetailsJob: Confirmation updated successfully', [
                    'confirmation_id' => $this->confirmationId,
                    'vapi_call_id' => $this->vapiCallId,
                    'updated_fields' => array_keys($updateData),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('FetchVapiCallDetailsJob: Error processing job', [
                'confirmation_id' => $this->confirmationId,
                'vapi_call_id' => $this->vapiCallId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
