<?php

namespace App\Services;

use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;

class BillingService
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Cria ou atualiza uma assinatura
     */
    public function createOrUpdateSubscription(User $user, Plan $plan, array $paymentData = [])
    {
        $existingSubscription = $user->subscription;
        $amount = $paymentData['amount'] ?? $plan->price;

        if ($existingSubscription) {
            // Atualizar assinatura existente
            $existingSubscription->update([
                'plan_id' => $plan->id,
                'status' => $paymentData['status'] ?? 'active',
                'payment_method' => $paymentData['payment_method'] ?? 'stripe',
                'stripe_payment_id' => $paymentData['stripe_payment_id'] ?? null,
                'amount'            => $amount,
                'expires_at' => $this->calculateExpirationDate($plan),
                'updated_at' => now(),
            ]);

            return $existingSubscription;
        } else {
            // Criar nova assinatura
            return Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => $paymentData['status'] ?? 'active',
                'payment_method' => $paymentData['payment_method'] ?? 'stripe',
                'stripe_payment_id' => $paymentData['stripe_payment_id'] ?? null,
                'started_at' => now(),
                'amount'            => $amount,
                'expires_at' => $this->calculateExpirationDate($plan),
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
            ]);
        }
    }

    /**
     * Calcula a data de expiração baseada no ciclo de cobrança
     */
    protected function calculateExpirationDate(Plan $plan)
    {
        // Se for o plano carrier unlimited, nunca expira
        if ($plan->slug === 'carrier-unlimited') {
            return null;
        }

        switch ($plan->billing_cycle) {
            case 'weekly':
                return now()->addWeek();
            case 'monthly':
                return now()->addMonth();
            case 'quarterly':
                return now()->addMonths(3);
            case 'yearly':
                return now()->addYear();
            default:
                return now()->addMonth();
        }
    }

    /**
     * Cria uma assinatura ilimitada para carriers
     */
    public function createCarrierUnlimitedSubscription(User $user)
    {
        $plan = Plan::where('slug', 'carrier-unlimited')->first();

        if (!$plan) {
            throw new \Exception('Plano Carrier Unlimited não encontrado. Certifique-se de que foi criado na base de dados.');
        }

        return $this->createOrUpdateSubscription($user, $plan, [
            'status' => 'active',
            'payment_method' => 'free', // ou 'system'
        ]);
    }

    /**
     * Atualiza o plano para um plano pago
     */
    public function upgradeToPaidPlan(User $user, Plan $plan, string $paymentMethod)
    {
        // Verificar se o usuário já tem uma assinatura
        $subscription = $user->subscription;

        if ($subscription) {
            // Upgrade da assinatura existente
            $subscription->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'payment_method' => $paymentMethod,
                'expires_at' => $this->calculateExpirationDate($plan),
            ]);
        } else {
            // Criar nova assinatura
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'payment_method' => $paymentMethod,
                'started_at' => now(),
                'expires_at' => $this->calculateExpirationDate($plan),
            ]);
        }

        return $subscription;
    }

    /**
     * Verifica se a assinatura está ativa
     */
    public function isSubscriptionActive(User $user)
    {
        $subscription = $user->subscription;

        if (!$subscription) {
            return false;
        }

        // Se for plano carrier unlimited, sempre ativo
        if ($subscription->plan && $subscription->plan->slug === 'carrier-unlimited') {
            return true;
        }

        return $subscription->status === 'active' &&
               ($subscription->expires_at === null || $subscription->expires_at->isFuture());
    }

    /**
     * Verifica se a assinatura está em período de teste
     */
    public function isOnTrial(User $user)
    {
        $subscription = $user->subscription;

        if (!$subscription || !$subscription->trial_ends_at) {
            return false;
        }

        return $subscription->trial_ends_at->isFuture();
    }

    public function createTrialSubscription(User $user)
    {
        // Ajuste este filtro conforme seu modelo: aqui buscamos o primeiro plano com trial_days > 0
        $plan = Plan::where('trial_days', '>', 0)->first();

        if (! $plan) {
            throw new \Exception('Nenhum plano de trial configurado.');
        }

        // Chama seu método genérico de criação/atualização
        return $this->createOrUpdateSubscription($user, $plan, [
            'status' => 'trial',
            // não há pagamento, mas você pode passar outros metadados se precisar
        ]);
    }

    /**
     * Cancela uma assinatura
     */
    public function cancelSubscription(User $user, $immediately = false)
    {
        $subscription = $user->subscription;

        if (!$subscription) {
            throw new \Exception('Usuário não possui assinatura ativa');
        }

        // Não permitir cancelar assinatura de carrier unlimited
        if ($subscription->plan && $subscription->plan->slug === 'carrier-unlimited') {
            throw new \Exception('Não é possível cancelar assinatura de carrier');
        }

        if ($immediately) {
            $subscription->update([
                'status' => 'cancelled',
                'expires_at' => now(),
            ]);
        } else {
            // Cancelar no final do período
            $subscription->update([
                'status' => 'cancelled',
                // expires_at permanece o mesmo para permitir uso até o final
            ]);
        }

        return $subscription;
    }

    /**
     * Reativa uma assinatura
     */
    public function reactivateSubscription(User $user, string $paymentMethod)
    {
        $subscription = $user->subscription;

        if (!$subscription) {
            throw new \Exception('Usuário não possui assinatura');
        }

        $subscription->update([
            'status' => 'active',
            'payment_method' => $paymentMethod,
            'expires_at' => $this->calculateExpirationDate($subscription->plan),
            'blocked_at' => null,
        ]);

        return $subscription;
    }

    /**
     * Bloqueia uma assinatura por falta de pagamento
     */
    public function blockSubscription(User $user, string $reason = 'payment_failed')
    {
        $subscription = $user->subscription;

        if ($subscription) {
            // Não bloquear carriers com plano unlimited
            if ($subscription->plan && $subscription->plan->slug === 'carrier-unlimited') {
                return $subscription; // Retorna sem bloquear
            }

            $subscription->update([
                'status' => 'blocked',
                'blocked_at' => now(),
                'block_reason' => $reason,
            ]);
        }

        return $subscription;
    }

    /**
     * Obtém o uso atual vs limites do plano
     */
    public function getUsageStats(User $user)
    {
        $subscription = $user->subscription;

        if (!$subscription || !$subscription->plan) {
            return null;
        }

        $plan = $subscription->plan;

        // Se for plano carrier unlimited, retorna limites como null (ilimitado)
        if ($plan->slug === 'carrier-unlimited') {
            return [
                'carriers' => [
                    'used' => $user->carriers()->count(),
                    'limit' => null, // ilimitado
                ],
                'employees' => [
                    'used' => $user->employees()->count(),
                    'limit' => null, // ilimitado
                ],
                'drivers' => [
                    'used' => $user->drivers()->count(),
                    'limit' => null, // ilimitado
                ],
                'loads_this_month' => [
                    'used' => $user->loads()
                               ->whereMonth('loads.created_at', now()->month)
                               ->count(),
                    'limit' => null, // ilimitado
                ],
                'loads_this_week' => [
                    'used' => $user->loads()
                               ->whereBetween('loads.created_at', [
                                    now()->startOfWeek(),
                                    now()->endOfWeek()
                               ])->count(),
                    'limit' => null, // ilimitado
                ],
            ];
        }

        // Lógica normal para outros planos
        return [
            'carriers' => [
                'used' => $user->carriers()->count(),
                'limit' => $plan->max_carriers,
            ],
            'employees' => [
                'used' => $user->employees()->count(),
                'limit' => $plan->max_employees,
            ],
            'drivers' => [
                'used' => $user->drivers()->count(),
                'limit' => $plan->max_drivers,
            ],
            'loads_this_month' => [
                'used' => $user->loads()
                               ->whereMonth('loads.created_at', now()->month)
                               ->count(),
                'limit' => $plan->max_loads_per_month,
            ],
            'loads_this_week' => [
                'used' => $user->loads()
                               ->whereBetween('loads.created_at', [
                                    now()->startOfWeek(),
                                    now()->endOfWeek()
                               ])->count(),
                'limit' => $plan->max_loads_per_week,
            ],
        ];
    }

    public function checkUsageLimits(User $user): array
    {
        $stats = $this->getUsageStats($user);

        // sem assinatura ou sem plano não bloqueia
        if (!$stats) {
            return ['allowed' => true];
        }

        // Se for carrier unlimited, sempre permitir
        $subscription = $user->subscription;
        if ($subscription && $subscription->plan && $subscription->plan->slug === 'carrier-unlimited') {
            return ['allowed' => true];
        }

        // 1) bloqueia se ultrapassar qualquer limite
        foreach ($stats as $key => $data) {
            if ($data['limit'] !== null && $data['used'] > $data['limit']) {
                return [
                    'allowed' => false,
                    'reason'  => "Você ultrapassou o limite de {$key} ({$data['used']} de {$data['limit']}).",
                ];
            }
        }

        // 2) adiciona aviso caso esteja chegando perto (80% do limite)
        foreach ($stats as $key => $data) {
            if ($data['limit'] !== null && $data['limit'] > 0 && $data['used'] >= $data['limit'] * 0.8) {
                return [
                    'allowed' => true,
                    'warning' => true,
                    'message' => "Você está usando {$data['used']} de {$data['limit']} {$key} permitidos. Atenção ao limite do seu plano.",
                ];
            }
        }

        // tudo ok
        return ['allowed' => true];
    }

    /**
     * Verifica se o usuário é um carrier
     */
    public function isCarrier(User $user): bool
    {
        return $user->roles()->where('name', 'Carrier')->exists();
    }

    /**
     * Rastreia o uso de recursos
     */
    public function trackUsage(User $user, string $resourceType, int $quantity = 1)
    {
        // Se for carrier, não precisa rastrear limites
        if ($this->isCarrier($user)) {
            return true;
        }

        // Lógica normal de rastreamento para outros usuários
        // Implementar conforme necessário
        return true;
    }
}
