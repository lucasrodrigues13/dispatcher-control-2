<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Driver extends Model
{
    use HasFactory;

    /**
     * Aplicar TenantScope para filtrar automaticamente por tenant
     */
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    protected $fillable = [
        'carrier_id',
        'name',
        'phone',
        'ssn_tax_id',
        'email',
    ];

    /**
     * Cada Carrier pertence a um User
     */
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * Driver relaciona-se com User através do email
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
