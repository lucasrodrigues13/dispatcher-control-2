<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\BillingService;
use App\Models\Plan;

class CreateMissingSubscriptions extends Command
{
    protected $signature = 'subscriptions:create-missing';
    protected $description = 'Cria subscriptions freemium para usuários que não têm';

    public function handle()
    {
        $this->info('Verificando planos...');
        
        // Verificar se plano freemium existe
        $freemiumPlan = Plan::where('slug', 'freemium')->whereNull('user_id')->first();
        
        if (!$freemiumPlan) {
            $this->error('Plano freemium não encontrado! Execute o seeder primeiro:');
            $this->info('php artisan db:seed --class=PlansSeeder');
            return 1;
        }
        
        $this->info("Plano freemium encontrado (ID: {$freemiumPlan->id})");
        
        // Buscar usuários sem subscription
        $usersWithoutSubscription = User::whereDoesntHave('subscription')->get();
        
        if ($usersWithoutSubscription->isEmpty()) {
            $this->info('Todos os usuários já têm subscription!');
            return 0;
        }
        
        $this->info("Encontrados {$usersWithoutSubscription->count()} usuários sem subscription");
        
        $billingService = app(BillingService::class);
        $created = 0;
        $errors = 0;
        
        foreach ($usersWithoutSubscription as $user) {
            try {
                // Identificar usuário principal
                $mainUser = $billingService->getMainUser($user);
                
                // Criar subscription apenas se ainda não tiver
                if (!$mainUser->subscription) {
                    $billingService->createFreemiumSubscription($mainUser);
                    $created++;
                    $this->info("✓ Subscription criada para usuário {$mainUser->id} ({$mainUser->email})");
                } else {
                    $this->comment("  Usuário {$user->id} já tem subscription através do usuário principal {$mainUser->id}");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Erro ao criar subscription para usuário {$user->id}: {$e->getMessage()}");
            }
        }
        
        $this->newLine();
        $this->info("Resumo:");
        $this->info("  - Subscriptions criadas: {$created}");
        $this->info("  - Erros: {$errors}");
        
        return 0;
    }
}

