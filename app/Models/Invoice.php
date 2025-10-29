<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'load_id',
        'dispatcher_id',
        'carrier_id',
        'amount',
        'amount_paid',
        'invoice_date',
        'due_date',
        'paid_date',
        'payment_status',
        'notes'
    ];

    // Método renomeado para evitar conflito
    public function loadRelation()
    {
        return $this->belongsTo(Load::class);
    }

    public function dispatcher()
    {
        return $this->belongsTo(Dispatcher::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }
}
