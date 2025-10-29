<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'user_id',
        'void_check_path',
        'w9_path',
        'coi_path',
        'proof_fmcsa_path',
        'drivers_license_path',
        'truck_picture_1_path',
        'truck_picture_2_path',
        'truck_picture_3_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
