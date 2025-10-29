<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broker extends Model
{
    use HasFactory;

    protected $table = 'brokers';

    protected $fillable = [
        'user_id',
        'license_number',
        'company_name',
        'phone',
        'address',
        'notes',
        'accounting_email',
        'accounting_phone_number',
        'fee_percent',
        'payment_terms',
        'payment_method',
    ];

    // Relacionamento: um broker pertence a um usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
