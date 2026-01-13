<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'user_id',
        'invoice_id',
        'payment_intent_id',
        'amount',
        'status',
        'failure_reason',
        'attempted_at',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'attempted_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para tentativas bem-sucedidas
     */
    public function scopeSucceeded($query)
    {
        return $query->where('status', 'succeeded');
    }

    /**
     * Scope para tentativas falhadas
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope para tentativas pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
