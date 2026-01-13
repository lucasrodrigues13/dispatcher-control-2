<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'stripe_subscription_item_id',
        'stripe_price_id',
        'item_type',
        'quantity',
        'unit_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_amount' => 'integer',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Verifica se é item do plano principal
     */
    public function isMainPlan(): bool
    {
        return $this->item_type === 'main_plan';
    }

    /**
     * Verifica se é item do serviço de IA
     */
    public function isAiVoiceService(): bool
    {
        return $this->item_type === 'ai_voice_service';
    }

    /**
     * Scope para itens do plano principal
     */
    public function scopeMainPlan($query)
    {
        return $query->where('item_type', 'main_plan');
    }

    /**
     * Scope para itens do serviço de IA
     */
    public function scopeAiVoiceService($query)
    {
        return $query->where('item_type', 'ai_voice_service');
    }
}
