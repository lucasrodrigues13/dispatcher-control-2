<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalService extends Model
{
    use HasFactory;

    protected $table = 'additional_services';

    protected $fillable = [
        'describe',
        'quantity',
        'value',
        'total',
        'status',
        'carrier_id',
        'dispatcher_id',
        'is_installment',
        'installment_type',
        'installment_count',
    ];

    // Relacionamento com Carrier
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    // Relacionamento com Dispatcher
    public function dispatcher()
    {
        return $this->belongsTo(Dispatcher::class);
    }
}
