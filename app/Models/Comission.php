<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Comission extends Model
{
    use HasFactory;

    protected $table = "commissions";

    /**
     * Aplicar TenantScope para filtrar automaticamente por tenant
     */
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    protected $fillable = [
        'dispatcher_id',
        'deal_id',
        'employee_id',
        'value',
    ];

    public function dispatcher()
    {
        return $this->belongsTo(Dispatcher::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
