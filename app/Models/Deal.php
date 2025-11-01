<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispatcher_id',
        'carrier_id',
        'value',
    ];

    public function dispatcher()
    {
        return $this->belongsTo(Dispatcher::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function commissions()
    {
        return $this->hasMany(Comission::class);
    }
}
