<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class ChargeSetup extends Model
{
    use HasFactory;

    protected $table = 'charges_setups';

    /**
     * Aplicar TenantScope para filtrar automaticamente por tenant
     */
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    protected $fillable = [
        'charges_setup_array',
        'carrier_id',
        'dispatcher_id',
        'price',
    ];

    protected $casts = [
        'charges_setup_array' => 'array',
    ];

    // Relacionamento com Carrier
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    // ⭐ ADICIONAR: Relacionamento com Dispatcher
    public function dispatcher()
    {
        return $this->belongsTo(Dispatcher::class);
    }
}
