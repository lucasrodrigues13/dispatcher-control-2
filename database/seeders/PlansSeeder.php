<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlansSeeder extends Seeder
{
    public function run()
    {
        // ⭐ Plano Freemium (automático para novos usuários)
        Plan::updateOrCreate(
            ['slug' => 'freemium'],
            [
                'name' => 'Freemium',
                'price' => 0.00,
                'max_loads_per_month' => 75,  // Limite após primeiro mês
                'max_loads_per_week' => null,
                'max_carriers' => 1,
                'max_dispatchers' => 1,
                'max_employees' => 0,
                'max_drivers' => 0,
                'max_brokers' => 0,
                'is_trial' => false,
                'is_custom' => false,
                'trial_days' => 0,
                'active' => true,
                'user_id' => null, // Plano global, não customizado (importante para BillingService)
            ]
        );

        $this->command->info('Plano Freemium criado com sucesso!');
    }
}
