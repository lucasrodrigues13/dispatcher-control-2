<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    use HasFactory;

    protected $table = "carriers";

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
        'dispatcher_company_id',
    ];

    public function dispatchers()
    {
        return $this->belongsTo(Dispatcher::class, 'dispatcher_company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
