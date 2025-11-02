<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolesUsers extends Model
{
    use HasFactory;

    protected $table = 'roles_users';

    protected $fillable = [
        'user_id',
        'role_id',
    ];

    public $timestamps = true;
}
