<?php

namespace App\Services;

use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
// use App\Models\Carrier;
use App\Services\StripeService;
use App\Repositories\UsageTrackingRepository;
// use Illuminate\Support\Facades\Log;
// use Carbon\Carbon;

class BillingService
{
    protected $stripeService;
    protected $usageTrackingRepo;

    public function __construct(StripeService $stripeService, UsageTrackingRepository $usageTrackingRepo)
    {
        $this->stripeService = $stripeService;
        $this->usageTrackingRepo = $usageTrackingRepo;
    }

    // public function canCreateCarrier(User $user): array
    // {
    //     $subscription = $user->subscription;

    //     if (!$subscription) {
    //         return [
    //             'allowed' => false,
    //             'requires_payment' => false,
    //             'reason' => 'no_subscription',
    //             'message' => 'Você precisa de uma assinatura ativa para criar carriers.',
    //         ];
    //     }

    //     $plan = $subscription->plan;

    //     // Plano unlimited sempre pode criar
    //     if ($plan && $plan->slug === 'carrier-unlimited') {
    //         return [
    //             'allowed' => true,
    //             'requires_payment' => false,
    //         ];
    //     }

    //     // Conta carriers do dispatcher atual
    //     $dispatcher = $user->dispatchers()->first();
    //     if (!$dispatcher) {
    //         return [
    //             'allowed' => false,
    //             'requires_payment' => false,
    //             'reason' => 'no_dispatcher',
    //             'message' => 'Você precisa ser um dispatcher para criar carriers.',
    //         ];
    //     }

    //     $currentCarriersCount = Carrier::where('dispatcher_id', $dispatcher->id)->count();

    //     // Para planos free/trial, limite de 1 carrier gratuito
    //     $freeCarriersLimit = 1;
    //     $additionalCarrierPrice = 10.00;

    //     if ($currentCarriersCount < $freeCarriersLimit) {
    //         // Ainda tem carrier gratuito disponível
    //         return [
    //             'allowed' => true,
    //             'requires_payment' => false,
    //             'is_free' => true,
    //             'remaining_free' => $freeCarriersLimit - $currentCarriersCount,
    //         ];
    //     }

    //     // Precisa pagar por carrier adicional
    //     return [
    //         'allowed' => false, // Não permitir até confirmar pagamento
    //         'requires_payment' => true,
    //         'current_carriers' => $currentCarriersCount,
    //         'free_limit' => $freeCarriersLimit,
    //         'additional_price' => $additionalCarrierPrice,
    //         'message' => "Você atingiu o limite de carriers gratuitos ({$freeCarriersLimit}). Carriers adicionais custam \${$additionalCarrierPrice} cada.",
    //     ];
    // }

    // /**
    //  * Processa pagamento por carrier adicional
    //  */
    // public function processAdditionalCarrierPayment(User $user, string $paymentIntentId): array
    // {
    //     try {
    //         $paymentIntent = $this->stripeService->retrievePaymentIntent($paymentIntentId);

    //         if ($paymentIntent->status !== 'succeeded') {
    //             return [
    //                 'success' => false,
    //                 'message' => 'Pagamento não confirmado. Status: ' . $paymentIntent->status
    //             ];
    //         }

    //         // Registra o pagamento usando sua estrutura existente
    //         $subscription = $user->subscription;

    //         \App\Models\Payment::create([
    //             'subscription_id' => $subscription->id,
    //             'amount' => 10.00,
    //             'status' => 'paid',
    //             'payment_method' => 'stripe',
    //             'transaction_id' => $paymentIntent->id,
    //             'gateway_response' => json_encode($paymentIntent),
    //             'paid_at' => now(),
    //             'type' => 'additional_carrier',
    //             'metadata' => json_encode([
    //                 'payment_intent_id' => $paymentIntentId,
    //                 'description' => 'Additional Carrier Fee',
    //                 'user_id' => $user->id,
    //             ]),
    //         ]);

    //         return [
    //             'success' => true,
    //             'message' => 'Pagamento processado com sucesso! Você pode criar o carrier agora.',
    //         ];

    //     } catch (\Exception $e) {
    //         \Log::error('Erro ao processar pagamento de carrier adicional', [
    //             'error' => $e->getMessage(),
    //             'user_id' => $user->id,
    //         ]);

    //         return [
    //             'success' => false,
    //             'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
    //         ];
    //     }
    // }

    // /**
    //  * Cria Payment Intent para carrier adicional
    //  */
    // public function createAdditionalCarrierPaymentIntent(User $user): array
    // {
    //     try {
    //         $amount = 1000; // $10.00 em centavos

    //         $metadata = [
    //             'user_id' => (string) $user->id,
    //             'user_email' => $user->email,
    //             'transaction_type' => 'additional_carrier',
    //             'description' => 'Additional Carrier Fee',
    //         ];

    //         $intent = $this->stripeService->createPaymentIntent($amount, $metadata);

    //         return [
    //             'success' => true,
    //             'client_secret' => $intent->client_secret,
    //             'amount' => $amount,
    //         ];

    //     } catch (\Exception $e) {
    //         \Log::error('Erro ao criar Payment Intent para carrier adicional', [
    //             'error' => $e->getMessage(),
    //             'user_id' => $user->id,
    //         ]);

    //         return [
    //             'success' => false,
    //             'error' => 'Erro ao criar Payment Intent: ' . $e->getMessage()
    //         ];
    //     }
    // }

    // // Métodos existentes mantidos...

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
        $usage = $this->usageTrackingRepo->getCurrentUsage($user);

        // Se for plano ilimitado
        if ($plan->slug === 'carrier-unlimited') {
            return [
                'carriers' => [
                    'used' => $usage ? $usage->carriers_count : 0,
                    'limit' => null,
                ],
                'employees' => [
                    'used' => $usage ? $usage->employees_count : 0,
                    'limit' => null,
                ],
                'drivers' => [
                    'used' => $usage ? $usage->drivers_count : 0,
                    'limit' => null,
                ],
                'loads_this_month' => [
                    'used' => $usage ? $usage->loads_count : 0,
                    'limit' => null,
                ],
            ];
        }

        // Verifica se está em trial
        $isTrial = $subscription->trial_ends_at && $subscription->trial_ends_at->isFuture();

        return [
            'carriers' => [
                'used' => $usage ? $usage->carriers_count : 0,
                'limit' => $plan->max_carriers,
            ],
            'employees' => [
                'used' => $usage ? $usage->employees_count : 0,
                'limit' => $plan->max_employees,
            ],
            'drivers' => [
                'used' => $usage ? $usage->drivers_count : 0,
                'limit' => $plan->max_drivers,
            ],
            'loads_this_month' => [
                'used' => $usage ? $usage->loads_count : 0,
                'limit' => $isTrial ? null : $plan->max_loads_per_month, // ilimitado no trial
            ],
        ];
    }

    public function calculateMonthlyBilling(User $user)
        {
            $month = now()->month;
            $subscription = $user->subscription;
            $plan = $subscription->plan;
            $monthlyLoads = $plan->max_loads_per_month;
            $usage = $this->usageTrackingRepo->getCurrentUsage($user);

            // Mês 1 = Trial gratuito
            // if ($user->subscription->started_at->month === $month &&
            //     $user->subscription->started_at->year === now()->year) {
            //     return ['plan' => 'trial', 'cost' => 0];
            // }

            // Mês 2+ = Verificar limite
            if ($usage->loads_count == $monthlyLoads) {
                return [
                    'allowed' => true,
                    'suggest_upgrade' => true,
                    'message' => 'You have reached the employee limit for your plan. Please upgrade to add more.',
                ];
            }
        }

    public function checkUsageLimits(User $user, string $resourceType)
    {
        // return $this->usageTrackingRepo->checkLimits($user, $resourceType);
        return ["allowed" => true];
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
