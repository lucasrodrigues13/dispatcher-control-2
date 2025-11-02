<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class permissions_roles extends Model
{
    protected $table = 'permissions_roles';

    protected $fillable = [
        'permission_id',
        'role_id',
    ];

    public $timestamps = true;
}
