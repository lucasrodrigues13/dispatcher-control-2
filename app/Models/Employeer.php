<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employeer extends Model
{
    use HasFactory;

    protected $table = "employees";

    protected $fillable = [
        'dispatcher_id',
        'phone',
        'position',
        'ssn_tax_id',
        'user_id',
    ];

    /**
     * Cada Dispatcher pertence a um User
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    
    /**
     * Cada Dispatcher pertence a um User
     */
    public function dispatcher()
    {
        return $this->belongsTo(\App\Models\Dispatcher::class);
    }

}
