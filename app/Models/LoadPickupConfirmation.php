<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadPickupConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_id',
        'contact_name',
        'car_ready_for_pickup',
        'not_ready_when',
        'hours_of_operation',
        'car_condition',
        'is_address_correct',
        'pickup_address',
        'pickup_city',
        'pickup_state',
        'pickup_zip',
        'special_instructions',
        'call_record_url',
        'call_transcription_url',
        'vapi_call_id',
        'vapi_call_status',
        'call_duration',
        'call_cost',
        'raw_payload',
    ];

    protected $casts = [
        'car_ready_for_pickup' => 'boolean',
        'is_address_correct' => 'boolean',
        'not_ready_when' => 'datetime',
        'call_duration' => 'integer',
        'call_cost' => \App\Casts\MoneyCast::class,
        'raw_payload' => 'array',
    ];

    /**
     * Relacionamento com Load
     */
    public function loadRelation()
    {
        return $this->belongsTo(Load::class, 'load_id');
    }
}
