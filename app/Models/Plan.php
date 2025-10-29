<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'max_loads_per_month',
        'max_loads_per_week',
        'max_carriers',
        'max_employees',
        'max_drivers',
        'is_trial',
        'trial_days',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_trial' => 'boolean',
        'active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
