<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_event_id',
        'event_type',
        'event_object_id',
        'processed',
        'processing_started_at',
        'processing_completed_at',
        'processing_error',
        'raw_event',
    ];

    protected $casts = [
        'processed' => 'boolean',
        'processing_started_at' => 'datetime',
        'processing_completed_at' => 'datetime',
        'raw_event' => 'array',
    ];

    /**
     * Verifica se o evento já foi processado
     */
    public static function isProcessed(string $stripeEventId): bool
    {
        return self::where('stripe_event_id', $stripeEventId)
            ->where('processed', true)
            ->exists();
    }

    /**
     * Marca evento como processando
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'processed' => false,
            'processing_started_at' => now(),
            'processing_error' => null,
        ]);
    }

    /**
     * Marca evento como processado com sucesso
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'processed' => true,
            'processing_completed_at' => now(),
            'processing_error' => null,
        ]);
    }

    /**
     * Marca evento como falhado
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'processed' => false,
            'processing_completed_at' => now(),
            'processing_error' => $error,
        ]);
    }
}
