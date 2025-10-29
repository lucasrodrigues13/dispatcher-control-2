<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    use HasFactory;

    use HasFactory;

    protected $table = 'containers';

    protected $fillable = [
        'name',
        'user_id',
    ];

    // Relacionamento com o modelo User (assumindo que um container pertence a um usuário)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Container.php
    public function loads()
    {
        return $this->belongsToMany(Load::class, 'containers_loads', 'container_id', 'load_id');
    }

    public function containerLoads()
    {
        return $this->hasMany(ContainerLoad::class);
    }


}