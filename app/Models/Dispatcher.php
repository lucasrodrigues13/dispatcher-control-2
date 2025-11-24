<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispatcher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'owner_id',
        'is_owner',
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

    protected $casts = [
        'is_owner' => 'boolean',
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
        return $this->hasMany(Carrier::class, 'dispatcher_id');
    }

    /**
     * Relacionamento com os funcionários (employees).
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'dispatcher_id');
    }

    /**
     * Relacionamento com o owner (dispatcher principal)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Verifica se é o owner do tenant
     */
    public function isOwner(): bool
    {
        return $this->is_owner === true;
    }
}
