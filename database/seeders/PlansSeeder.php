<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlansSeeder extends Seeder
{
    public function run()
    {
        Plan::create([
            'name' => 'Trial',
            'slug' => 'trial',
            'price' => 0.00,
            'max_loads_per_month' => null,
            'max_loads_per_week' => 50,
            'max_carriers' => 1,
            'max_employees' => 0,
            'max_drivers' => 0,
            'is_trial' => true,
            'trial_days' => 30,
            'active' => true,
        ]);

        Plan::create([
            'name' => 'Dispatcher Pro',
            'slug' => 'dispatcher-pro',
            'price' => 10.00,
            'max_loads_per_month' => null,
            'max_loads_per_week' => null,
            'max_carriers' => 1,
            'max_employees' => 0,
            'max_drivers' => 0,
            'is_trial' => false,
            'trial_days' => 0,
            'active' => true,
        ]);
    }
}
