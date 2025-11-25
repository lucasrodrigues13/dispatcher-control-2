<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Services\BillingService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected BillingService $billingService;
    protected StripeService  $stripeService;

    public function __construct(BillingService $billingService, StripeService $stripeService)
    {
        $this->billingService = $billingService;
        $this->stripeService  = $stripeService;
    }

    /** Página principal das assinaturas */
    public function index()
    {
        $user = auth()->user();
        
        // ⭐ CORRIGIDO: Identificar usuário principal
        $billingService = app(BillingService::class);
        $mainUser = $billingService->getMainUser($user);
        
        $subscription = $mainUser->subscription;

        return view('subscription.index', compact('subscription'));
    }

    /** Listagem de planos - ⭐ DESABILITADO: Redireciona para build-plan */
    public function plans()
    {
        // ⭐ CORRIGIDO: Redirecionar para montar plano customizado
        return redirect()->route('subscription.build-plan')
            ->with('info', 'Agora você pode montar seu plano personalizado!');
    }

    /** Rota intermediária de checkout (form GET) */
    public function checkout(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);

        $plan = Plan::findOrFail($request->plan_id);
        $user = auth()->user();
        
        // ⭐ CORRIGIDO: Identificar usuário principal
        $mainUser = $this->billingService->getMainUser($user);
        $currentSubscription = $mainUser->subscription;

        // ⭐ CORRIGIDO: Se há dados na sessão, criar/atualizar plano temporário para cálculo
        $pendingPlanData = session('pending_plan');
        
        if ($pendingPlanData) {
            // Verificar se já existe plano temporário ou criar novo para cálculo
            $tempPlan = Plan::where('id', $plan->id)->first();
            
            if ($tempPlan && strpos($tempPlan->slug ?? '', 'temp-') === 0) {
                // Atualizar plano temporário existente com dados da sessão
                $tempPlan->update([
                    'price' => $pendingPlanData['total_price'],
                    'name' => "Plano Customizado - {$pendingPlanData['total_users']} usuários",
                ]);
                $planForCalculation = $tempPlan;
            } else {
                // Criar novo plano temporário para cálculo
                $planForCalculation = Plan::create([
                    'user_id' => $mainUser->id,
                    'name' => "Plano Customizado - {$pendingPlanData['total_users']} usuários",
                    'slug' => 'temp-calculation-' . $mainUser->id . '-' . time(),
                    'price' => $pendingPlanData['total_price'],
                    'max_carriers' => $pendingPlanData['carriers'],
                    'max_dispatchers' => $pendingPlanData['dispatchers'],
                    'max_employees' => $pendingPlanData['employees'],
                    'max_drivers' => $pendingPlanData['drivers'],
                    'max_brokers' => $pendingPlanData['brokers'],
                    'max_loads_per_month' => null,
                    'is_custom' => true,
                    'active' => false,
                ]);
            }
        } else {
            $planForCalculation = $plan;
        }

        // ⭐ NOVO: Detectar se é downgrade (redução de plano)
        $isDowngrade = false;
        $prorationInfo = null;
        
        if ($currentSubscription && $currentSubscription->expires_at) {
            $currentPlan = $currentSubscription->plan;
            
            // ⭐ CORRIGIDO: Verificar se vem do freemium primeiro
            $isFromFreemium = $currentPlan && ($currentPlan->slug === 'freemium' || ($currentPlan->price ?? 0) == 0);
            
            if ($isFromFreemium) {
                // Freemium: não calcular proporcional, pagar preço cheio
                $prorationInfo = [
                    'is_upgrade' => false,
                    'is_from_freemium' => true,
                    'amount' => $planForCalculation->price,
                    'full_amount' => $planForCalculation->price,
                ];
            } else {
                $currentAmount = $currentSubscription->amount ?? $currentPlan->price ?? 0;
                $newAmount = $planForCalculation->price;
                
                // Se novo plano é mais barato, é downgrade
                if ($newAmount < $currentAmount) {
                    $isDowngrade = true;
                } else {
                    // Se é upgrade de plano pago, calcular valor proporcional
                    $prorationInfo = $this->billingService->calculateProratedUpgradeAmount($currentSubscription, $planForCalculation);
                }
            }
        }

        // ⭐ NOVO: Obter URL de retorno da sessão (se existir)
        $returnUrl = session('return_url_after_payment');
        
        // Debug: Log para verificar se a URL está na sessão
        Log::info('Checkout - URL de retorno', [
            'return_url_from_session' => $returnUrl,
            'previous_url' => url()->previous(),
            'current_url' => url()->current(),
            'all_session_keys' => array_keys(session()->all()),
        ]);
        
        // Se não houver URL de retorno na sessão, usar URL anterior ou dashboard
        if (!$returnUrl) {
            $previousUrl = url()->previous();
            // Se a URL anterior for a própria página de checkout ou build-plan, usar dashboard
            if ($previousUrl && 
                !str_contains($previousUrl, '/subscription/checkout') && 
                !str_contains($previousUrl, '/subscription/build-plan') &&
                !str_contains($previousUrl, '/subscription/plans') &&
                !str_contains($previousUrl, '/login') &&
                !str_contains($previousUrl, '/register')) {
                $returnUrl = $previousUrl;
            } else {
                $returnUrl = route('dashboard.index');
            }
        }
        
        // Garantir que temos uma URL válida
        if (!$returnUrl || $returnUrl === url()->current()) {
            $returnUrl = route('dashboard.index');
        }
        
        return view('subscription.checkout', compact('plan', 'currentSubscription', 'prorationInfo', 'pendingPlanData', 'isDowngrade', 'planForCalculation', 'returnUrl'));
    }

    /** API: cria o PaymentIntent no Stripe */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);

        try {
            $user = auth()->user();
            
            // ⭐ CORRIGIDO: Identificar usuário principal
            $mainUser = $this->billingService->getMainUser($user);
            $currentSubscription = $mainUser->subscription;
            
            $plan = Plan::findOrFail($request->plan_id);

            // ⭐ CORRIGIDO: Se há dados na sessão, usar esses dados ao invés do plano vinculado
            $pendingPlanData = session('pending_plan');
            
            if ($pendingPlanData) {
                // Usar dados da sessão (plano configurado pelo usuário)
                $planPrice = $pendingPlanData['total_price'];
                $planName = "Plano Customizado - {$pendingPlanData['total_users']} usuários";
            } else {
                // Usar plano passado (fallback)
                $planPrice = $plan->price;
                $planName = $plan->name;
            }

            // ⭐ CORRIGIDO: Atualizar plano existente temporariamente para cálculo proporcional
            // Isso permite que o método calculateProratedUpgradeAmount receba um objeto Plan válido
            if ($pendingPlanData) {
                // Atualizar o plano existente temporariamente com dados da sessão
                $plan->price = $planPrice;
                $plan->name = $planName;
                $planForCalculation = $plan;
            } else {
                $planForCalculation = $plan;
            }

            // ⭐ NOVO: Detectar se é downgrade ou upgrade
            $prorationInfo = null;
            $isUpgrade = false;
            $isDowngrade = false;
            $amountToCharge = $planPrice;

            if ($currentSubscription && $currentSubscription->expires_at && $currentSubscription->expires_at->isFuture()) {
                $currentPlan = $currentSubscription->plan;
                
                // ⭐ CORRIGIDO: Verificar se vem do freemium primeiro
                $isFromFreemium = $currentPlan && ($currentPlan->slug === 'freemium' || ($currentPlan->price ?? 0) == 0);
                
                if ($isFromFreemium) {
                    // Freemium: pagar preço cheio, não calcular proporcional
                    $amountToCharge = $planPrice;
                    $prorationInfo = [
                        'is_upgrade' => false,
                        'is_from_freemium' => true,
                        'amount' => $planPrice,
                        'full_amount' => $planPrice,
                    ];
                } else {
                    $currentAmount = $currentSubscription->amount ?? $currentPlan->price ?? 0;
                    
                    // Se novo plano é mais barato, é downgrade (não precisa pagar agora)
                    if ($planPrice < $currentAmount) {
                        $isDowngrade = true;
                        // Não criar Payment Intent para downgrade
                        return response()->json([
                            'success' => false,
                            'is_downgrade' => true,
                            'message' => 'Downgrade não requer pagamento imediato. Use o endpoint de downgrade.',
                        ], 400);
                    } else {
                        // Se é upgrade de plano pago, calcular valor proporcional
                        $prorationInfo = $this->billingService->calculateProratedUpgradeAmount($currentSubscription, $planForCalculation);
                        
                        if ($prorationInfo['is_upgrade'] && $prorationInfo['amount'] > 0) {
                            $isUpgrade = true;
                            $amountToCharge = $prorationInfo['amount'];
                        }
                    }
                }
            }

            $amount = intval(round($amountToCharge * 100)); // Converter para centavos

            // Metadata conforme documentação do Stripe
            $metadata = [
                'user_id' => (string) $mainUser->id,
                'user_email' => $mainUser->email,
                'plan_id' => (string) $plan->id,
                'plan_name' => $planName,
                'billing_cycle' => $plan->billing_cycle ?? 'month',
                'transaction_type' => $isUpgrade ? 'upgrade_prorated' : 'subscription_payment',
                'is_upgrade' => $isUpgrade ? 'true' : 'false',
            ];

            if ($isUpgrade && $prorationInfo) {
                $metadata['prorated_amount'] = (string) $prorationInfo['amount'];
                $metadata['full_amount'] = (string) $prorationInfo['full_amount'];
                $metadata['days_remaining'] = (string) $prorationInfo['days_remaining'];
            }

            $intent = $this->stripeService->createPaymentIntent($amount, $metadata);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'amount' => $amount,
                'amount_charged' => $amountToCharge,
                'plan_name' => $planName,
                'full_amount' => $planPrice,
                'is_upgrade' => $isUpgrade,
                'proration_info' => $prorationInfo,
                'success' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating Payment Intent', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'plan_id' => $request->plan_id
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao criar Payment Intent: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ⭐ NOVO: Processa downgrade de plano (sem pagamento imediato)
     * Atualiza subscription para próximo ciclo
     */
    public function processDowngrade(Request $request): JsonResponse
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);

        try {
            $user = auth()->user();
            
            // ⭐ CORRIGIDO: Identificar usuário principal
            $mainUser = $this->billingService->getMainUser($user);
            $currentSubscription = $mainUser->subscription;
            
            if (!$currentSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma assinatura ativa encontrada.'
                ], 400);
            }

            $plan = Plan::findOrFail($request->plan_id);
            
            // ⭐ CORRIGIDO: Se há dados na sessão, usar esses dados
            $pendingPlanData = session('pending_plan');
            
            if ($pendingPlanData) {
                $planPrice = $pendingPlanData['total_price'];
                $planName = "Plano Customizado - {$pendingPlanData['total_users']} usuários";
            } else {
                $planPrice = $plan->price;
                $planName = $plan->name;
            }

            $currentPlan = $currentSubscription->plan;
            $currentAmount = $currentSubscription->amount ?? $currentPlan->price ?? 0;
            
            // Verificar se realmente é downgrade
            if ($planPrice >= $currentAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta não é uma redução de plano. Use o checkout normal para upgrades.'
                ], 400);
            }

            // ⭐ CORRIGIDO: Criar/atualizar plano APENAS após confirmação
            if ($pendingPlanData && $plan->is_custom) {
                $isTemporaryPlan = strpos($plan->slug ?? '', 'temp-') === 0;
                
                if ($isTemporaryPlan) {
                    // Criar plano definitivo
                    $finalPlan = Plan::create([
                        'user_id' => $mainUser->id,
                        'name' => "Plano Customizado - {$pendingPlanData['total_users']} usuários",
                        'slug' => 'custom-user-' . $mainUser->id . '-' . time(),
                        'price' => $pendingPlanData['total_price'],
                        'max_carriers' => $pendingPlanData['carriers'],
                        'max_dispatchers' => $pendingPlanData['dispatchers'],
                        'max_employees' => $pendingPlanData['employees'],
                        'max_drivers' => $pendingPlanData['drivers'],
                        'max_brokers' => $pendingPlanData['brokers'],
                        'max_loads_per_month' => null,
                        'is_custom' => true,
                        'active' => true,
                    ]);
                    
                    $plan->delete();
                    $plan = $finalPlan;
                } else {
                    // Atualizar plano existente
                    $plan->update([
                        'name' => "Plano Customizado - {$pendingPlanData['total_users']} usuários",
                        'price' => $pendingPlanData['total_price'],
                        'max_carriers' => $pendingPlanData['carriers'],
                        'max_dispatchers' => $pendingPlanData['dispatchers'],
                        'max_employees' => $pendingPlanData['employees'],
                        'max_drivers' => $pendingPlanData['drivers'],
                        'max_brokers' => $pendingPlanData['brokers'],
                        'active' => true,
                    ]);
                }
                
                session()->forget('pending_plan');
            }

            // Atualizar subscription no banco (mantém expires_at, atualiza amount para próximo ciclo)
            $currentSubscription->update([
                'plan_id' => $plan->id,
                'amount' => $planPrice, // Valor para próximo ciclo
                // expires_at permanece o mesmo - mudança só no próximo ciclo
            ]);
            
            // ⭐ CORRIGIDO: Recarregar subscription com relacionamento plan
            $currentSubscription->refresh();
            $currentSubscription->load('plan');

            // ⭐ NOVO: Se tem subscription no Stripe, atualizar para próximo ciclo
            if ($currentSubscription->stripe_subscription_id) {
                try {
                    $this->stripeService->updateSubscriptionForNextCycle(
                        $currentSubscription->stripe_subscription_id,
                        [
                            'metadata' => [
                                'plan_id' => (string) $plan->id,
                                'plan_name' => $planName,
                                'new_amount' => (string) $planPrice,
                                'downgrade' => 'true',
                            ],
                        ]
                    );
                } catch (\Exception $e) {
                    // Log erro mas não falha o processo (subscription já atualizada no banco)
                    Log::warning('Erro ao atualizar subscription no Stripe', [
                        'error' => $e->getMessage(),
                        'subscription_id' => $currentSubscription->stripe_subscription_id,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Plano reduzido com sucesso! A mudança será aplicada no próximo ciclo de cobrança.',
                'subscription' => $currentSubscription->fresh(),
                'new_amount' => $planPrice,
                'expires_at' => $currentSubscription->expires_at,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar downgrade', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'plan_id' => $request->plan_id,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao processar downgrade: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'plan_id'           => 'required|exists:plans,id',
        ]);

        try {
            $user          = auth()->user();
            $plan          = Plan::findOrFail($request->plan_id);
            $paymentIntent = $this->stripeService->retrievePaymentIntent($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pagamento não confirmado. Status: ' . $paymentIntent->status
                ], 400);
            }

            // ⭐ CORRIGIDO: Identificar usuário principal
            $billingService = app(BillingService::class);
            $mainUser = $billingService->getMainUser($user);
            
            // ⭐ CORRIGIDO: Criar/atualizar plano APENAS após pagamento confirmado
            $pendingPlanData = session('pending_plan');
            
            if ($pendingPlanData && $plan->is_custom) {
                // Verificar se é plano temporário (slug começa com 'temp-')
                $isTemporaryPlan = strpos($plan->slug, 'temp-') === 0;
                
                if ($isTemporaryPlan) {
                    // Criar plano definitivo após pagamento confirmado
                    $finalPlan = Plan::create([
                        'user_id' => $mainUser->id,
                        'name' => "Plano Customizado - {$pendingPlanData['total_users']} usuários",
                        'slug' => 'custom-user-' . $mainUser->id . '-' . time(),
                        'price' => $pendingPlanData['total_price'],
                        'max_carriers' => $pendingPlanData['carriers'],
                        'max_dispatchers' => $pendingPlanData['dispatchers'],
                        'max_employees' => $pendingPlanData['employees'],
                        'max_drivers' => $pendingPlanData['drivers'],
                        'max_brokers' => $pendingPlanData['brokers'],
                        'max_loads_per_month' => null,
                        'is_custom' => true,
                        'active' => true, // Ativar após pagamento confirmado
                    ]);
                    
                    // Deletar plano temporário
                    $plan->delete();
                    $plan = $finalPlan;
                } else {
                    // Atualizar plano existente após pagamento confirmado
                    $plan->update([
                        'name' => "Plano Customizado - {$pendingPlanData['total_users']} usuários",
                        'price' => $pendingPlanData['total_price'],
                        'max_carriers' => $pendingPlanData['carriers'],
                        'max_dispatchers' => $pendingPlanData['dispatchers'],
                        'max_employees' => $pendingPlanData['employees'],
                        'max_drivers' => $pendingPlanData['drivers'],
                        'max_brokers' => $pendingPlanData['brokers'],
                        'active' => true, // Ativar após pagamento confirmado
                    ]);
                }
                
                // Limpar dados temporários da sessão
                session()->forget('pending_plan');
            }

            // ⭐ NOVO: Verificar se é upgrade proporcional
            $currentSubscription = $mainUser->subscription;
            $isUpgrade = false;
            $preserveExpiresAt = false;
            $isFromFreemium = false;

            if ($currentSubscription && $currentSubscription->expires_at && $currentSubscription->expires_at->isFuture()) {
                $prorationInfo = $this->billingService->calculateProratedUpgradeAmount($currentSubscription, $plan);
                
                // ⭐ CORRIGIDO: Se vem do freemium, não preservar expires_at - iniciar novo período de 30 dias
                if (isset($prorationInfo['is_from_freemium']) && $prorationInfo['is_from_freemium']) {
                    $isFromFreemium = true;
                    $preserveExpiresAt = false; // Não preservar - iniciar novo período
                } 
                // ⭐ CORRIGIDO: Apenas upgrades proporcionais preservam expires_at
                // O cálculo proporcional já considera os dias restantes até o vencimento
                elseif ($prorationInfo['is_upgrade']) {
                    $isUpgrade = true;
                    $preserveExpiresAt = true; // Manter expires_at original para upgrades proporcionais
                }
            }

            // ⭐ CORRIGIDO: Vincular subscription ao usuário principal APENAS após pagamento confirmado
            $subscription = $this->billingService->createOrUpdateSubscription(
                $mainUser,
                $plan,
                [
                    'payment_intent_id'  => $request->payment_intent_id,
                    'payment_method'     => 'stripe',
                    'status'             => 'active',
                    'stripe_payment_id'  => $paymentIntent->id,
                    'amount'             => $plan->price, // Valor completo do plano (para próximo ciclo)
                    'preserve_expires_at' => $preserveExpiresAt, // ⭐ NOVO: Manter data original em upgrades
                ]
            );

            // ⭐ CORRIGIDO: Recarregar subscription com relacionamento plan para garantir dados atualizados
            $subscription->refresh();
            $subscription->load('plan');
            
            // ⭐ CORRIGIDO: Limpar relacionamento do usuário para forçar recarregamento
            $mainUser->unsetRelation('subscription');
            
            // ⭐ NOVO: Limpar URL de retorno da sessão após pagamento bem-sucedido
            // (será usado pelo JavaScript para redirecionar)
            $returnUrl = session('return_url_after_payment', route('dashboard.index'));
            session()->forget('return_url_after_payment');

            return response()->json([
                'success'      => true,
                'message'      => 'Pagamento processado com sucesso!',
                'subscription' => $subscription,
                'return_url'   => $returnUrl, // ⭐ NOVO: Incluir URL de retorno na resposta
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Erro ao processar pagamento: ' . $e->getMessage()
            ], 500);
        }
    }


    /** API: Confirma um Payment Intent (se necessário) */
    public function confirmPaymentIntent(Request $request): JsonResponse
    {
        $request->validate(['payment_intent_id' => 'required|string']);

        try {
            $intent = $this->stripeService->confirmPaymentIntent($request->payment_intent_id);

            return response()->json([
                'success' => true,
                'payment_intent' => $intent,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro ao confirmar Payment Intent: ' . $e->getMessage()
            ], 500);
        }
    }

    /** API: Processa reembolso */
    public function refund(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'amount'            => 'integer|min:1|nullable',
        ]);

        try {
            $refund = $this->stripeService->createRefund(
                $request->payment_intent_id,
                $request->input('amount')
            );

            return response()->json([
                'success' => true,
                'refund' => $refund,
                'message' => 'Reembolso processado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro ao processar reembolso: ' . $e->getMessage()
            ], 500);
        }
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        // Redirecionar para a página de checkout
        return redirect()->route('subscription.checkout', ['plan_id' => $plan->id]);
    }

    // public function blocked()
    // {
    //     $user = auth()->user();
    //     $subscription = $user->subscription;

    //     return view('subscription.blocked', compact('subscription'));
    // }

    // ⭐ REMOVIDO: Tela de success removida - redireciona direto para dashboard
    // public function success()
    // {
    //     return view('subscription.success');
    // }

    /**
     * ⭐ CORRIGIDO: Cancela assinatura mantendo uso até expirar
     */
    public function cancel()
    {
        $user = auth()->user();
        
        // ⭐ CORRIGIDO: Identificar usuário principal
        $billingService = app(BillingService::class);
        $mainUser = $billingService->getMainUser($user);
        
        $subscription = $mainUser->subscription;

        if ($subscription && $subscription->isActive()) {
            // ⭐ CORRIGIDO: Cancelar mas manter expires_at (usuário pode usar até expirar)
            $subscription->update([
                'status' => 'cancelled',
                // expires_at permanece o mesmo - usuário pode usar até a data de vencimento
            ]);
            
            $expiresAt = $subscription->expires_at ? $subscription->expires_at->format('d/m/Y') : 'a data de vencimento';
            
            return redirect()->route('subscription.index')
                ->with('success', "Assinatura cancelada. Você pode continuar usando o sistema até {$expiresAt}.");
        }

        return redirect()->route('subscription.index')
            ->with('error', 'Nenhuma assinatura ativa para cancelar.');
    }

    public function reactivate(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $user = auth()->user();
        $subscription = $user->subscription;

        if ($subscription && in_array($subscription->status, ['blocked', 'cancelled'])) {
            $subscription->update([
                'status' => 'active',
                'blocked_at' => null,
                'payment_method' => $request->payment_method,
                'expires_at' => now()->addMonth(),
            ]);

            return redirect()->route('dashboard.index')
                ->with('success', 'Assinatura reativada com sucesso!');
        }

        return back()->with('error', 'Não foi possível reativar a assinatura.');
    }

    /**
     * ⭐ NOVO: Mostra tela "Montar Seu Plano"
     */
    public function buildPlan()
    {
        // ⭐ NOVO: Se não houver URL de retorno na sessão e vier de uma página válida, armazenar
        if (!session()->has('return_url_after_payment')) {
            $previousUrl = url()->previous();
            // Se a URL anterior não for uma página de subscription/login/register, usar como retorno
            if ($previousUrl && 
                !str_contains($previousUrl, '/subscription/') && 
                !str_contains($previousUrl, '/login') &&
                !str_contains($previousUrl, '/register')) {
                session(['return_url_after_payment' => $previousUrl]);
                Log::info('buildPlan - URL de retorno armazenada', ['url' => $previousUrl]);
            }
        }
        
        $user = auth()->user();
        $currentSubscription = $user->subscription;
        $currentPlan = $currentSubscription ? $currentSubscription->plan : null;

        // ⭐ CORRIGIDO: Se tem plano ativo com limites definidos, usar os LIMITES do plano
        // Caso contrário, usar as contagens atuais de usuários
        if ($currentPlan) {
            // Verificar se o plano tem limites definidos (não são null e não são 0 para todos)
            $hasLimits = ($currentPlan->max_carriers ?? 0) > 0 || 
                         ($currentPlan->max_dispatchers ?? 0) > 0 || 
                         ($currentPlan->max_employees ?? 0) > 0 || 
                         ($currentPlan->max_drivers ?? 0) > 0 || 
                         ($currentPlan->max_brokers ?? 0) > 0;
            
            if ($hasLimits) {
                // Usar os limites do plano como valores iniciais (o que o usuário já tem contratado)
                $currentCounts = [
                    'carriers' => max(0, $currentPlan->max_carriers ?? 0),
                    'dispatchers' => max(0, $currentPlan->max_dispatchers ?? 0),
                    'employees' => max(0, $currentPlan->max_employees ?? 0),
                    'drivers' => max(0, $currentPlan->max_drivers ?? 0),
                    'brokers' => max(0, $currentPlan->max_brokers ?? 0),
                ];
            } else {
                // Plano sem limites definidos: contar usuários atuais
                $dispatcher = \App\Models\Dispatcher::where('user_id', $user->id)->first();
                $dispatcherId = $dispatcher ? $dispatcher->id : null;
                
                $carrierIds = \App\Models\Carrier::where('dispatcher_id', $dispatcherId)->pluck('id');
                
                $currentCounts = [
                    'carriers' => \App\Models\Carrier::where('dispatcher_id', $dispatcherId)->count(),
                    'dispatchers' => $dispatcher ? 1 : 0,
                    'employees' => \App\Models\Employee::where('dispatcher_id', $dispatcherId)->count(),
                    'drivers' => \App\Models\Driver::whereIn('carrier_id', $carrierIds)->count(),
                    'brokers' => \App\Models\Broker::where('user_id', $user->id)->count(),
                ];
            }
        } else {
            // Sem plano: contar usuários atuais
            $dispatcher = \App\Models\Dispatcher::where('user_id', $user->id)->first();
            $dispatcherId = $dispatcher ? $dispatcher->id : null;
            
            $carrierIds = \App\Models\Carrier::where('dispatcher_id', $dispatcherId)->pluck('id');
            
            $currentCounts = [
                'carriers' => \App\Models\Carrier::where('dispatcher_id', $dispatcherId)->count(),
                'dispatchers' => $dispatcher ? 1 : 0,
                'employees' => \App\Models\Employee::where('dispatcher_id', $dispatcherId)->count(),
                'drivers' => \App\Models\Driver::whereIn('carrier_id', $carrierIds)->count(),
                'brokers' => \App\Models\Broker::where('user_id', $user->id)->count(),
            ];
        }

        return view('subscription.build-plan', compact('currentPlan', 'currentCounts'));
    }

    /**
     * ⭐ NOVO: API - Calcula preço em tempo real
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        $request->validate([
            'carriers' => 'integer|min:0',
            'dispatchers' => 'integer|min:0',
            'employees' => 'integer|min:0',
            'drivers' => 'integer|min:0',
            'brokers' => 'integer|min:0',
        ]);

        $carriers = (int) ($request->input('carriers', 0));
        $dispatchers = (int) ($request->input('dispatchers', 0));
        $employees = (int) ($request->input('employees', 0));
        $drivers = (int) ($request->input('drivers', 0));
        $brokers = (int) ($request->input('brokers', 0));

        $totalUsers = $carriers + $dispatchers + $employees + $drivers + $brokers;

        // Mínimo 2 usuários
        if ($totalUsers < 2) {
            return response()->json([
                'success' => false,
                'error' => 'Mínimo de 2 usuários obrigatório',
                'total_users' => $totalUsers,
                'price' => 0,
            ]);
        }

        // $10 por usuário
        $pricePerUser = 10.00;
        $totalPrice = $totalUsers * $pricePerUser;

        return response()->json([
            'success' => true,
            'total_users' => $totalUsers,
            'price_per_user' => $pricePerUser,
            'total_price' => $totalPrice,
            'formatted_price' => '$' . number_format($totalPrice, 2, '.', ','),
        ]);
    }

    /**
     * ⭐ CORRIGIDO: Processa configuração do plano customizado (SEM criar plano ainda)
     * O plano só será criado/atualizado após pagamento confirmado
     */
    public function storeCustomPlan(Request $request)
    {
        $request->validate([
            'carriers' => 'required|integer|min:0',
            'dispatchers' => 'required|integer|min:0',
            'employees' => 'required|integer|min:0',
            'drivers' => 'required|integer|min:0',
            'brokers' => 'required|integer|min:0',
        ]);

        $user = auth()->user();
        
        // ⭐ CORRIGIDO: Identificar o usuário principal (Dispatcher) que possui a subscription
        $billingService = app(BillingService::class);
        $mainUser = $billingService->getMainUser($user);
        
        $carriers = (int) $request->input('carriers');
        $dispatchers = (int) $request->input('dispatchers');
        $employees = (int) $request->input('employees');
        $drivers = (int) $request->input('drivers');
        $brokers = (int) $request->input('brokers');

        $totalUsers = $carriers + $dispatchers + $employees + $drivers + $brokers;

        // Validar mínimo de 2 usuários
        if ($totalUsers < 2) {
            return redirect()->back()
                ->withErrors(['error' => 'Mínimo de 2 usuários obrigatório ($20/mês)'])
                ->withInput();
        }

        // Calcular preço
        $pricePerUser = 10.00;
        $totalPrice = $totalUsers * $pricePerUser;

        try {
            // ⭐ CORRIGIDO: Armazenar dados do plano na sessão (NÃO criar plano ainda)
            // O plano só será criado/atualizado após pagamento confirmado
            session([
                'pending_plan' => [
                    'carriers' => $carriers,
                    'dispatchers' => $dispatchers,
                    'employees' => $employees,
                    'drivers' => $drivers,
                    'brokers' => $brokers,
                    'total_users' => $totalUsers,
                    'total_price' => $totalPrice,
                    'main_user_id' => $mainUser->id,
                ]
            ]);

            // Verificar se já existe plano customizado (para calcular upgrade proporcional)
            $existingCustomPlan = Plan::where('user_id', $mainUser->id)
                ->where('is_custom', true)
                ->first();

            if ($existingCustomPlan) {
                // Usar plano existente temporariamente para checkout (será atualizado após pagamento)
                $plan = $existingCustomPlan;
            } else {
                // Criar plano temporário apenas para checkout (será recriado após pagamento)
                // Este plano será usado apenas para calcular valores no checkout
                $plan = Plan::create([
                    'user_id' => $mainUser->id,
                    'name' => "Plano Customizado - {$totalUsers} usuários",
                    'slug' => 'temp-' . $mainUser->id . '-' . time(),
                    'price' => $totalPrice,
                    'max_carriers' => $carriers,
                    'max_dispatchers' => $dispatchers,
                    'max_employees' => $employees,
                    'max_drivers' => $drivers,
                    'max_brokers' => $brokers,
                    'max_loads_per_month' => null,
                    'is_custom' => true,
                    'active' => false, // Não ativar até pagamento confirmado
                ]);
            }

            // ⭐ NOVO: Preservar URL de retorno ao redirecionar para checkout
            // Se não houver URL de retorno na sessão, tentar usar a URL anterior
            if (!session()->has('return_url_after_payment')) {
                $previousUrl = url()->previous();
                // Se a URL anterior não for uma página de subscription, usar como retorno
                if ($previousUrl && 
                    !str_contains($previousUrl, '/subscription/') && 
                    !str_contains($previousUrl, '/login') &&
                    !str_contains($previousUrl, '/register')) {
                    session(['return_url_after_payment' => $previousUrl]);
                }
            }
            
            // Redirecionar para checkout com o plano temporário
            return redirect()->route('subscription.checkout', ['plan_id' => $plan->id])
                ->with('success', 'Plano configurado! Complete o pagamento para ativar.');

        } catch (\Exception $e) {
            Log::error('Erro ao configurar plano customizado', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'data' => $request->all(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao configurar plano: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
