<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Carrier extends Model
{
    use HasFactory;

    protected $table = "carriers";

    /**
     * Aplicar TenantScope para filtrar automaticamente por tenant
     */
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    protected $fillable = [
        'company_name',
        'phone',
        'contact_name',
        'about',
        'website',
        'trailer_capacity',
        'is_auto_hauler',
        'is_towing',
        'is_driveaway',
        'contact_phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'mc',
        'dot',
        'ein',
        'user_id',
        'dispatcher_id',
    ];

    public function dispatcher()
    {
        return $this->belongsTo(Dispatcher::class, 'dispatcher_id');
    }
    
    // Alias para compatibilidade (se usado em algum lugar)
    public function dispatchers()
    {
        return $this->dispatcher();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
