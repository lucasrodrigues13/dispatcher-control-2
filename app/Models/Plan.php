<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'max_loads_per_month',
        'max_loads_per_week',
        'max_carriers',
        'max_dispatchers',  // ✅ Adicionado via migration
        'max_employees',
        'max_drivers',
        'max_brokers',      // ✅ Adicionado via migration
        'user_id',          // ✅ Adicionado via migration (planos customizados)
        'is_custom',        // ✅ Adicionado via migration
        'ai_voice_service', // ✅ Adicionado via migration
        'is_trial',
        'trial_days',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_trial' => 'boolean',
        'is_custom' => 'boolean',
        'ai_voice_service' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Relacionamento com User (para planos customizados)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para planos globais (não customizados)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope para planos customizados
     */
    public function scopeCustom($query)
    {
        return $query->whereNotNull('user_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
