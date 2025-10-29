<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispatcher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'company_name',
        'ssn_itin',
        'ein_tax_id',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'notes',
        'phone',
        'departament'
    ];

    /**
     * Relação com o usuário (proprietário ou funcionário).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com os carriers (carriers) dessa empresa dispatcher.
     */
    public function carriers()
    {
        return $this->hasMany(Customer::class, 'dispatcher_company_id');
    }

    /**
     * Relacionamento com os funcionários (caso precise criar isso futuramente).
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
