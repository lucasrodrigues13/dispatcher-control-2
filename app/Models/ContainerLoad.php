<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContainerLoad extends Model
{
    use HasFactory;

    protected $table = 'containers_loads';

    protected $fillable = [
        'container_id',
        'load_id',
        'position',
        'moved_at',
    ];

    // Relacionamento com o Container
    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    // Relacionamento com o Load
    public function loadRelation() // ou use outro nome, como 'relatedLoad'
    {
        return $this->belongsTo(Load::class);
    }

    public function loadItem() // nome sem conflito
    {
        return $this->belongsTo(Load::class, 'load_id');
    }

}
