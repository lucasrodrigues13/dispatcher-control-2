<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = "employees";

    protected $fillable = [
        'dispatcher_id',
        'user_id',
        'name',
        'email',
        'phone',
        'position',
        'ssn_tax_id',
        'created_at',
        'updated_at'
    ];

    /**
     * Relacionamento com User
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    
    /**
     * Relacionamento com Dispatcher
     */
    public function dispatcher()
    {
        return $this->belongsTo(\App\Models\Dispatcher::class);
    }

    /**
     * Accessor para retornar o nome do usuário associado
     */
    public function getUserNameAttribute()
    {
        // Se não há user_id, retorna null
        if (!$this->user_id) {
            return null;
        }
        
        // Carrega o relacionamento se não estiver carregado
        if (!$this->relationLoaded('user')) {
            $this->load('user');
        }
        
        return $this->user ? $this->user->name : null;
    }

    /**
     * Accessor para retornar o email do usuário associado
     */
    public function getUserEmailAttribute()
    {
        // Se não há user_id, retorna null
        if (!$this->user_id) {
            return null;
        }
        
        // Carrega o relacionamento se não estiver carregado
        if (!$this->relationLoaded('user')) {
            $this->load('user');
        }
        
        return $this->user ? $this->user->email : null;
    }
}

