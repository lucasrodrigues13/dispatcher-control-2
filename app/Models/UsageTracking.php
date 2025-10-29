<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageTracking extends Model
{
    use HasFactory;

    protected $table = "usage_tracking";

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'week',
        'loads_count',
        'carriers_count',
        'employees_count',
        'drivers_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
