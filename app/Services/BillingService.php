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

    /**
     * ⭐ NOVO: Calcula valor proporcional para upgrade de plano
     * Retorna o valor a ser cobrado proporcional aos dias restantes
     */
    public function calculateProratedUpgradeAmount(Subscription $currentSubscription, Plan $newPlan): array
    {
        // Se não tem data de expiração, não é upgrade proporcional
        if (!$currentSubscription->expires_at) {
            return [
                'is_upgrade' => false,
                'amount' => $newPlan->price,
                'full_amount' => $newPlan->price,
                'prorated_amount' => 0,
                'days_remaining' => 0,
                'days_in_period' => 30,
            ];
        }

        $currentPlan = $currentSubscription->plan;

        // ⭐ NOVO: Se o plano atual é freemium, não calcular proporcional - deve pagar preço cheio
        // Freemium deve ser interrompido imediatamente e iniciar plano pago com novo período de 30 dias
        if ($currentPlan && ($currentPlan->slug === 'freemium' || ($currentPlan->price ?? 0) == 0)) {
            return [
                'is_upgrade' => false,
                'amount' => $newPlan->price,
                'full_amount' => $newPlan->price,
                'prorated_amount' => 0,
                'days_remaining' => 0,
                'days_in_period' => 30,
                'is_from_freemium' => true, // Flag para identificar que vem do freemium
            ];
        }

        $currentAmount = $currentSubscription->amount ?? $currentPlan->price ?? 0;
        $newAmount = $newPlan->price;

        // Se o novo plano é mais barato ou igual, não precisa calcular proporcional
        if ($newAmount <= $currentAmount) {
            return [
                'is_upgrade' => false,
                'amount' => $newAmount,
                'full_amount' => $newAmount,
                'prorated_amount' => 0,
                'days_remaining' => 0,
                'days_in_period' => 30,
            ];
        }

        // Calcular dias restantes até expires_at
        $now = now();
        $expiresAt = $currentSubscription->expires_at;

        // Se já expirou, não é upgrade proporcional
        if ($expiresAt->isPast()) {
            return [
                'is_upgrade' => false,
                'amount' => $newAmount,
                'full_amount' => $newAmount,
                'prorated_amount' => 0,
                'days_remaining' => 0,
                'days_in_period' => 30,
            ];
        }

        // ⭐ CORRIGIDO: Calcular dias restantes até o vencimento incluindo o dia de HOJE
        // Usar startOfDay para garantir que contamos dias completos
        $nowStart = $now->copy()->startOfDay();
        $expiresAtStart = $expiresAt->copy()->addDay()->startOfDay();
        $daysRemaining = $nowStart->diffInDays($expiresAtStart, false);

        // ⭐ CORRIGIDO: Se o plano foi criado/atualizado hoje e vence em aproximadamente 30 dias,
        // garantir que contamos 30 dias completos (incluindo hoje)
        // Verificar se o plano foi criado/atualizado hoje ou muito recentemente
        $lastUpdated = $currentSubscription->updated_at ?? $currentSubscription->created_at;
        $lastUpdatedStart = $lastUpdated->copy()->startOfDay();
        $daysSinceUpdate = $nowStart->diffInDays($lastUpdatedStart, false);

        // Se foi atualizado hoje (mesmo dia) e a diferença até o vencimento é próxima de 30 dias,
        // considerar período completo de 30 dias
        if ($daysSinceUpdate == 0 && $daysRemaining >= 29 && $daysRemaining <= 31) {
            $daysRemaining = 30; // Garantir 30 dias completos quando plano foi fechado hoje
        }

        // Se restam menos de 1 dia completo, não calcular proporcional - cobrar valor cheio
        if ($daysRemaining < 1) {
            return [
                'is_upgrade' => false,
                'amount' => $newAmount,
                'full_amount' => $newAmount,
                'prorated_amount' => 0,
                'days_remaining' => 0,
                'days_in_period' => 30,
            ];
        }

        // ⭐ CORRIGIDO: O período padrão é 30 dias (ciclo mensal)
        $daysInPeriod = 30; // Período padrão mensal

        // Diferença de preço (quanto está sendo adicionado - apenas os novos recursos)
        $priceDifference = $newAmount - $currentAmount;

        // ⭐ CORRIGIDO: SEMPRE calcular valor proporcional baseado nos dias restantes até o vencimento
        // A proporcionalidade deve ser aplicada apenas à DIFERENÇA (novos recursos adicionados),
        // não ao valor total do plano
        // Exemplo: Se faltam 30 dias e adiciona $20, cobra ($20 * 30) / 30 = $20 (valor cheio da diferença)
        // Exemplo: Se faltam 15 dias e adiciona $20, cobra ($20 * 15) / 30 = $10 (proporcional da diferença)
        $proratedAmount = ($priceDifference * $daysRemaining) / $daysInPeriod;

        // ⭐ CORRIGIDO: Se o valor proporcional calculado é maior que a diferença (não deveria acontecer),
        // limitar ao valor da diferença
        if ($proratedAmount > $priceDifference) {
            $proratedAmount = $priceDifference;
        }

        // ⭐ CORRIGIDO: Se o valor proporcional é muito próximo da diferença (diferença < $0.01),
        // considerar como valor cheio da diferença para evitar problemas de arredondamento
        if (abs($proratedAmount - $priceDifference) < 0.01) {
            $proratedAmount = $priceDifference;
        }

        return [
            'is_upgrade' => true, // Sempre marcar como upgrade quando há diferença de preço
            'amount' => round($proratedAmount, 2), // Valor proporcional da diferença a ser cobrado agora
            'full_amount' => $newAmount, // Valor completo do novo plano (para referência)
            'prorated_amount' => round($proratedAmount, 2), // Valor proporcional calculado
            'current_amount' => $currentAmount, // Valor atual do plano
            'price_difference' => $priceDifference, // Diferença entre novo e atual
            'days_remaining' => max(0, $daysRemaining), // Dias restantes até vencimento
            'days_in_period' => $daysInPeriod, // Período total (30 dias)
            'expires_at' => $expiresAt, // Data de vencimento
        ];
    }

    public function createOrUpdateSubscription(User $user, Plan $plan, array $paymentData = [])
    {
        $existingSubscription = $user->subscription;
        $amount = $paymentData['amount'] ?? $plan->price;

        // ⭐ NOVO: Se é upgrade e tem expires_at preservado, manter a data original
        $preserveExpiresAt = $paymentData['preserve_expires_at'] ?? false;
        $originalExpiresAt = $preserveExpiresAt && $existingSubscription ? $existingSubscription->expires_at : null;

        if ($existingSubscription) {
            // Atualizar assinatura existente
            $updateData = [
                'plan_id' => $plan->id,
                'status' => $paymentData['status'] ?? 'active',
                'payment_method' => $paymentData['payment_method'] ?? 'stripe',
                'stripe_payment_id' => $paymentData['stripe_payment_id'] ?? null,
                'amount' => $plan->price, // Sempre atualizar para o valor completo do novo plano
                'updated_at' => now(),
            ];

            // ⭐ NOVO: Se é upgrade proporcional, manter expires_at original
            if ($preserveExpiresAt && $originalExpiresAt) {
                $updateData['expires_at'] = $originalExpiresAt;
            } else {
                $updateData['expires_at'] = $this->calculateExpirationDate($plan);
            }

            $existingSubscription->update($updateData);

            // ⭐ CORRIGIDO: Recarregar subscription com relacionamento plan para garantir dados atualizados
            $existingSubscription->refresh();
            $existingSubscription->load('plan');

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

    /**
     * ⭐ NOVO: Cria subscription freemium automática para novos usuários
     */
    public function createFreemiumSubscription(User $user)
    {
        $plan = Plan::where('slug', 'freemium')->whereNull('user_id')->first();

        if (!$plan) {
            throw new \Exception('Plano freemium não encontrado. Execute o seeder primeiro.');
        }

        // Verifica se já tem subscription
        if ($user->subscription) {
            return $user->subscription;
        }

        // Cria subscription freemium
        return $this->createOrUpdateSubscription($user, $plan, [
            'status' => 'active',
            'amount' => 0.00,
        ]);
    }

    /**
     * ⭐ NOVO: Verifica se usuário está no primeiro mês promocional (30 dias)
     */
    public function isFirstMonth(User $user): bool
    {
        $subscription = $user->subscription;

        if (!$subscription || !$subscription->started_at) {
            return false;
        }

        // Primeiro mês = 30 dias a partir de started_at
        $firstMonthEnd = $subscription->started_at->copy()->addDays(30);

        return now()->isBefore($firstMonthEnd);
    }

    /**
     * ⭐ NOVO: Verifica limite de cargas e bloqueia se exceder
     */
    public function checkLoadLimit(User $user): array
    {
        // ⭐ CORRIGIDO: Identificar o usuário principal (Dispatcher) que possui a subscription
        $mainUser = $this->getMainUser($user);

        // ⭐ CORRIGIDO: Recarregar subscription do banco para garantir dados atualizados
        $mainUser->unsetRelation('subscription');
        $subscription = $mainUser->subscription()->with('plan')->first();

        if (!$subscription || !$subscription->plan) {
            return [
                'allowed' => false,
                'message' => 'Você precisa de uma assinatura ativa.',
                'suggest_upgrade' => true,
            ];
        }

        $plan = $subscription->plan;

        // ⭐ Primeiro mês: cargas ilimitadas (sempre permite)
        if ($this->isFirstMonth($mainUser)) {
            return [
                'allowed' => true,
                'is_first_month' => true,
                'message' => 'Primeiro mês promocional: cargas ilimitadas!',
            ];
        }

        // Após primeiro mês: verificar limite
        $usage = $this->usageTrackingRepo->getCurrentUsage($mainUser);
        $loadsUsed = $usage ? $usage->loads_count : 0;
        $maxLoads = $plan->max_loads_per_month;

        // Se max_loads_per_month é null ou 0, significa ilimitado
        if ($maxLoads === null || $maxLoads === 0) {
            return [
                'allowed' => true,
                'loads_used' => $loadsUsed,
                'max_loads' => null,
            ];
        }

        // ⭐ IMPORTANTE: Só bloqueia se JÁ excedeu o limite
        // Se ainda tem espaço (loadsUsed < maxLoads), permite importação
        if ($loadsUsed >= $maxLoads) {
            return [
                'allowed' => false,
                'message' => "You have reached the limit of {$maxLoads} loads/month. Upgrade to Premium for unlimited loads.",
                'suggest_upgrade' => true,
                'loads_used' => $loadsUsed,
                'max_loads' => $maxLoads,
            ];
        }

        // Ainda tem espaço disponível
        return [
            'allowed' => true,
            'loads_used' => $loadsUsed,
            'max_loads' => $maxLoads,
            'remaining' => $maxLoads - $loadsUsed,
        ];
    }

    /**
     * ⭐ NOVO: Identifica o usuário principal (Dispatcher) que possui a subscription
     * Se o usuário passado for um Carrier/Broker, busca o Dispatcher principal
     */
    public function getMainUser(User $user): User
    {
        // ⭐ CORRIGIDO: Sempre buscar o Dispatcher principal, mesmo sem subscription

        // Se o usuário já é um Dispatcher, verificar se tem registro na tabela dispatchers
        if ($user->isDispatcher()) {
            $dispatcher = \App\Models\Dispatcher::where('user_id', $user->id)->first();
            if ($dispatcher) {
                // É um Dispatcher válido, retorna ele mesmo
                return $user;
            }
        }

        // Se é Carrier, buscar o Dispatcher através do relacionamento
        if ($user->isCarrier()) {
            $carrier = \App\Models\Carrier::where('user_id', $user->id)->first();
            if ($carrier && $carrier->dispatcher_id) {
                $dispatcher = \App\Models\Dispatcher::find($carrier->dispatcher_id);
                if ($dispatcher && $dispatcher->user) {
                    return $dispatcher->user;
                }
            }
        }

        // Se é Broker, buscar o Dispatcher principal
        // ⭐ CORRIGIDO: Broker.user_id agora aponta para o Dispatcher principal (após correção no BrokerController)
        // Quando um Broker User está logado, o Broker (entidade) tem user_id que aponta para o dispatcher principal
        $broker = \App\Models\Broker::where('user_id', $user->id)->first();
        if ($broker && $broker->user_id) {
            // Verificar se Broker.user_id aponta para um Dispatcher
            $dispatcherUser = \App\Models\User::find($broker->user_id);
            if ($dispatcherUser && $dispatcherUser->isDispatcher()) {
                return $dispatcherUser;
            }

            // Se Broker.user_id aponta para o próprio User Broker (dados antigos),
            // buscar através de quem tem esse Broker vinculado
            // Buscar Dispatcher que tem brokers com user_id apontando para ele
            $dispatcherWithBroker = \App\Models\Dispatcher::whereHas('user', function ($query) use ($broker) {
                $query->where('id', $broker->user_id);
            })->first();

            if ($dispatcherWithBroker && $dispatcherWithBroker->user) {
                return $dispatcherWithBroker->user;
            }
        }

        // ⭐ NOVO: Buscar Dispatcher que tem brokers vinculados através do user_id do Broker
        // Se Broker.user_id aponta para o próprio User Broker (dados antigos), buscar Dispatcher que tem esse Broker
        if ($broker) {
            // Buscar Dispatcher que tem brokers com user_id apontando para o dispatcher
            // Mas como Broker.user_id aponta para o próprio User Broker, precisamos buscar de outra forma
            // Buscar todos os Dispatchers e ver qual tem esse Broker vinculado através do email
            $allDispatchers = \App\Models\Dispatcher::with('user')->get();
            foreach ($allDispatchers as $dispatcher) {
                $dispatcherUser = $dispatcher->user;
                if ($dispatcherUser) {
                    $dispatcherUserId = $dispatcherUser->id ?? null;
                    if ($dispatcherUserId) {
                        // Buscar brokers desse dispatcher
                        $brokersOfDispatcher = \App\Models\Broker::where('user_id', $dispatcherUserId)->get();
                        foreach ($brokersOfDispatcher as $brokerOfDispatcher) {
                            $brokerUser = $brokerOfDispatcher->user;
                            $brokerUserId = $brokerUser->id ?? null;
                            // Verificar se o Broker tem o mesmo ID do usuário logado
                            if ($brokerUserId && $brokerUserId === $user->id) {
                                return $dispatcherUser;
                            }
                        }
                    }
                }
            }
        }

        // Se é Employee, buscar através do dispatcher
        $employee = \App\Models\Employee::where('email', $user->email)->first();
        if ($employee && $employee->dispatcher_id) {
            $dispatcher = \App\Models\Dispatcher::find($employee->dispatcher_id);
            if ($dispatcher && $dispatcher->user) {
                return $dispatcher->user;
            }
        }

        // Se é Driver, buscar através do carrier -> dispatcher
        $driver = \App\Models\Driver::where('email', $user->email)->first();
        if ($driver && $driver->carrier_id) {
            $carrier = \App\Models\Carrier::find($driver->carrier_id);
            if ($carrier && $carrier->dispatcher_id) {
                $dispatcher = \App\Models\Dispatcher::find($carrier->dispatcher_id);
                if ($dispatcher && $dispatcher->user) {
                    return $dispatcher->user;
                }
            }
        }

        // Se não encontrou dispatcher, retorna o próprio usuário
        // (assumindo que é um dispatcher sem registro na tabela dispatchers ainda)
        return $user;
    }

    /**
     * ⭐ NOVO: Verifica limite de usuários e bloqueia se exceder
     */
    public function checkUserLimit(User $user, string $userType = null): array
    {
        // ⭐ CORRIGIDO: Identificar o usuário principal (Dispatcher) que possui a subscription
        $mainUser = $this->getMainUser($user);

        // ⭐ CORRIGIDO: Recarregar subscription do banco para garantir dados atualizados
        // Forçar recarregamento do relacionamento para evitar cache
        $mainUser->unsetRelation('subscription');
        $subscription = $mainUser->subscription()->with('plan')->first();

        if (!$subscription || !$subscription->plan) {
            \Illuminate\Support\Facades\Log::info('checkUserLimit - Sem subscription ou plano', [
                'user_id' => $mainUser->id,
                'has_subscription' => $subscription ? true : false,
                'has_plan' => $subscription && $subscription->plan ? true : false,
            ]);
            
            return [
                'allowed' => false,
                'message' => 'Você precisa de uma assinatura ativa.',
                'suggest_upgrade' => true,
            ];
        }

        $plan = $subscription->plan;
        
        // ⭐ NOVO: Log para debug
        \Illuminate\Support\Facades\Log::info('checkUserLimit - Verificando limites', [
            'user_id' => $mainUser->id,
            'user_type' => $userType,
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'plan_max_employees' => $plan->max_employees,
            'plan_max_carriers' => $plan->max_carriers,
            'plan_max_drivers' => $plan->max_drivers,
            'plan_max_brokers' => $plan->max_brokers,
            'plan_is_custom' => $plan->is_custom ?? false,
            'subscription_updated_at' => $subscription->updated_at,
        ]);

        // Contar usuários ativos do dispatcher principal
        $dispatcher = \App\Models\Dispatcher::where('user_id', $mainUser->id)->first();
        $dispatcherId = $dispatcher ? $dispatcher->id : null;
        $ownerId = $mainUser->getOwnerId();

        // Carriers vinculados ao dispatcher do tenant
        $carriersCount = \App\Models\Carrier::whereHas('dispatcher', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })->count();

        // Dispatchers do tenant
        $dispatchersCount = \App\Models\Dispatcher::where('owner_id', $ownerId)->count();

        // Employees vinculados ao dispatcher do tenant
        $employeesCount = \App\Models\Employee::whereHas('dispatcher', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })->count();

        // Drivers: contar através dos carriers do tenant
        $carrierIds = \App\Models\Carrier::whereHas('dispatcher', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })->pluck('id');
        $driversCount = \App\Models\Driver::whereIn('carrier_id', $carrierIds)->count();

        // Brokers vinculados ao tenant
        $brokersCount = \App\Models\Broker::whereHas('user', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })->count();

        // Limites do plano
        $maxCarriers = $plan->max_carriers ?? 0;
        $maxDispatchers = $plan->max_dispatchers ?? 0;
        $maxEmployees = $plan->max_employees ?? 0;
        $maxDrivers = $plan->max_drivers ?? 0;
        $maxBrokers = $plan->max_brokers ?? 0;

        // ⭐ VALIDAÇÃO POR TIPO ESPECÍFICO (prioridade)
        // Se userType foi informado, validar APENAS esse tipo específico
        if ($userType) {
            $currentCount = match ($userType) {
                'carrier' => $carriersCount,
                'dispatcher' => $dispatchersCount,
                'employee' => $employeesCount,
                'driver' => $driversCount,
                'broker' => $brokersCount,
                default => 0,
            };

            $maxCount = match ($userType) {
                'carrier' => $maxCarriers,
                'dispatcher' => $maxDispatchers,
                'employee' => $maxEmployees,
                'driver' => $maxDrivers,
                'broker' => $maxBrokers,
                default => 0,
            };

            // Se maxCount é null, significa ilimitado para esse tipo
            if ($maxCount === null) {
                return [
                    'allowed' => true,
                    'is_unlimited' => true,
                    'current_count' => $currentCount,
                    'max_count' => null,
                ];
            }

            // ⭐ CORRIGIDO: Se maxCount é 0, significa que o plano NÃO permite criar esse tipo
            // Mas precisamos verificar se o plano foi atualizado recentemente após pagamento
            if ($maxCount === 0) {
                // ⭐ NOVO: Log para debug
                \Illuminate\Support\Facades\Log::info('checkUserLimit - maxCount é 0', [
                    'user_id' => $mainUser->id,
                    'user_type' => $userType,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'max_employees' => $plan->max_employees,
                    'max_carriers' => $plan->max_carriers,
                    'max_drivers' => $plan->max_drivers,
                    'max_brokers' => $plan->max_brokers,
                    'subscription_updated_at' => $subscription->updated_at,
                ]);
                
                $typeName = ucfirst($userType);
                return [
                    'allowed' => false,
                    'message' => "Seu plano atual não permite criar mais usuários. Upgrade para adicionar {$typeName}s.",
                    'suggest_upgrade' => true,
                    'current_count' => $currentCount,
                    'max_count' => 0,
                    'resource_type' => $userType,
                ];
            }

            // Verificar se já atingiu o limite específico
            if ($currentCount >= $maxCount) {
                $typeName = ucfirst($userType);
                return [
                    'allowed' => false,
                    'message' => "{$typeName}s limit reached ({$currentCount}/{$maxCount}). Upgrade to add more.",
                    'suggest_upgrade' => true,
                    'current_count' => $currentCount,
                    'max_count' => $maxCount,
                    'resource_type' => $userType,
                ];
            }

            // Ainda tem espaço disponível para esse tipo específico
            return [
                'allowed' => true,
                'current_count' => $currentCount,
                'max_count' => $maxCount,
                'remaining' => $maxCount - $currentCount,
                'resource_type' => $userType,
            ];
        }

        // Se não foi informado userType, retornar informações gerais (para compatibilidade)
        $totalUsers = $carriersCount + $dispatchersCount + $employeesCount + $driversCount + $brokersCount;
        $maxTotal = $maxCarriers + $maxDispatchers + $maxEmployees + $maxDrivers + $maxBrokers;

        return [
            'allowed' => true,
            'users_count' => $totalUsers,
            'max_users' => $maxTotal,
            'remaining' => $maxTotal - $totalUsers,
            'details' => [
                'carriers' => ['used' => $carriersCount, 'limit' => $maxCarriers],
                'dispatchers' => ['used' => $dispatchersCount, 'limit' => $maxDispatchers],
                'employees' => ['used' => $employeesCount, 'limit' => $maxEmployees],
                'drivers' => ['used' => $driversCount, 'limit' => $maxDrivers],
                'brokers' => ['used' => $brokersCount, 'limit' => $maxBrokers],
            ],
        ];
    }

    /**
     * ⭐ MANTIDO: Método antigo para compatibilidade (pode ser removido depois)
     */
    public function createTrialSubscription(User $user)
    {
        // Redireciona para createFreemiumSubscription
        return $this->createFreemiumSubscription($user);
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
        // ⭐ CORRIGIDO: Identificar o usuário principal (Dispatcher) que possui a subscription
        $mainUser = $this->getMainUser($user);

        // ⭐ CORRIGIDO: Recarregar subscription do banco para garantir dados atualizados
        $mainUser->unsetRelation('subscription');
        $subscription = $mainUser->subscription()->with('plan')->first();

        if (!$subscription || !$subscription->plan) {
            return null;
        }
        $plan = $subscription->plan;
        $usage = $this->usageTrackingRepo->getCurrentUsage($mainUser);
        
        // ⭐ CORRIGIDO: Usar a mesma lógica de contagem do checkUserLimit
        // Contar diretamente do banco ao invés de confiar na tabela usage_tracking
        $ownerId = $mainUser->getOwnerId();

        // Contar dispatchers usados (dispatchers do tenant)
        $dispatchersUsed = \App\Models\Dispatcher::where('owner_id', $ownerId)->count();

        // Contar employees usados (employees vinculados ao dispatcher do tenant)
        $employeesUsed = \App\Models\Employee::whereHas('dispatcher', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })->count();

        // Contar carriers usados (carriers vinculados ao dispatcher do tenant)
        $carriersUsed = \App\Models\Carrier::whereHas('dispatcher', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })->count();

        // Contar drivers usados (drivers através dos carriers do tenant)
        $carrierIds = \App\Models\Carrier::whereHas('dispatcher', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })->pluck('id');
        $driversUsed = \App\Models\Driver::whereIn('carrier_id', $carrierIds)->count();

        // Contar brokers usados (brokers do tenant)
        $brokersUsed = \App\Models\Broker::whereHas('user', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })->count();

        // Contar loads do mês atual (usar usage_tracking apenas para loads)
        $loadsThisMonth = $usage ? $usage->loads_count : 0;

        // Se for plano ilimitado
        if ($plan->slug === 'carrier-unlimited') {
            return [
                'dispatchers' => [
                    'used' => $dispatchersUsed,
                    'limit' => null,
                ],
                'employees' => [
                    'used' => $employeesUsed,
                    'limit' => null,
                ],
                'carriers' => [
                    'used' => $carriersUsed,
                    'limit' => null,
                ],
                'drivers' => [
                    'used' => $driversUsed,
                    'limit' => null,
                ],
                'brokers' => [
                    'used' => $brokersUsed,
                    'limit' => null,
                ],
                'loads_this_month' => [
                    'used' => $loadsThisMonth,
                    'limit' => null,
                ],
            ];
        }

        // Verifica se está em trial
        $isTrial = $subscription->trial_ends_at && $subscription->trial_ends_at->isFuture();

        return [
            'dispatchers' => [
                'used' => $dispatchersUsed,
                'limit' => $plan->max_dispatchers,
            ],
            'employees' => [
                'used' => $employeesUsed,
                'limit' => $plan->max_employees,
            ],
            'carriers' => [
                'used' => $carriersUsed,
                'limit' => $plan->max_carriers,
            ],
            'drivers' => [
                'used' => $driversUsed,
                'limit' => $plan->max_drivers,
            ],
            'brokers' => [
                'used' => $brokersUsed,
                'limit' => $plan->max_brokers ?? 0,
            ],
            'loads_this_month' => [
                'used' => $loadsThisMonth,
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
