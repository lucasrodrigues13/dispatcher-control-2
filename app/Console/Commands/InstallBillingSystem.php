<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Plan;

class InstallBillingSystem extends Command
{
    protected $signature = 'billing:install';
    protected $description = 'Install the billing system with default plans';

    public function handle()
    {
        $this->info('Installing Billing System...');

        // Executar migrations
        $this->call('migrate');

        // Criar planos padrão
        $this->createDefaultPlans();

        $this->info('Billing system installed successfully!');
    }

    private function createDefaultPlans()
    {
        $this->info('Creating default plans...');

        // Plano Trial
        Plan::updateOrCreate(
            ['slug' => 'trial'],
            [
                'name' => 'Trial',
                'price' => 0.00,
                'max_loads_per_month' => null,
                'max_loads_per_week' => 50,
                'max_carriers' => 1,
                'max_employees' => 0,
                'max_drivers' => 0,
                'is_trial' => true,
                'trial_days' => 30,
                'active' => true,
            ]
        );

        // Plano Dispatcher Pro
        Plan::updateOrCreate(
            ['slug' => 'dispatcher-pro'],
            [
                'name' => 'Dispatcher Pro',
                'price' => 10.00,
                'max_loads_per_month' => null,
                'max_loads_per_week' => null,
                'max_carriers' => 1,
                'max_employees' => 0,
                'max_drivers' => 0,
                'is_trial' => false,
                'trial_days' => 0,
                'active' => true,
            ]
        );

        $this->info('Default plans created successfully!');
    }
}
