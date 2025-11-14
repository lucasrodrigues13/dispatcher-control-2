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
        $user         = auth()->user();
        $subscription = $user->subscription;
        $plans        = Plan::where('active', true)
            ->where('is_trial', false)
            ->get();

        return view('subscription.index', compact('subscription', 'plans'));
    }

    /** Listagem de planos */
    public function plans()
    {
        $user                 = auth()->user();
        $currentSubscription  = $user->subscription;
        $plans                = Plan::where('active', true)->get();

        return view('subscription.plans', compact('plans', 'currentSubscription'));
    }

    /** Rota intermediária de checkout (form GET) */
    public function checkout(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);

        $plan                 = Plan::findOrFail($request->plan_id);
        $user                 = auth()->user();
        $currentSubscription  = $user->subscription;

        return view('subscription.checkout', compact('plan', 'currentSubscription'));
    }

    /** API: cria o PaymentIntent no Stripe */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);

        try {
            $user   = auth()->user();
            $plan   = Plan::findOrFail($request->plan_id);
            $amount = intval(round($plan->price * 100)); // Converter para centavos

            // Metadata conforme documentação do Stripe
            // Máximo 50 chaves, cada chave/valor até 500 caracteres
            $metadata = [
                'user_id' => (string) $user->id,
                'user_email' => $user->email,
                'plan_id' => (string) $plan->id,
                'plan_name' => $plan->name,
                'billing_cycle' => $plan->billing_cycle ?? 'month',
                'transaction_type' => 'subscription_payment',
            ];

            $intent = $this->stripeService->createPaymentIntent($amount, $metadata);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'amount' => $amount,
                'plan_name' => $plan->name,
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

            // --- Include o amount aqui ---
            $subscription = $this->billingService->createOrUpdateSubscription(
                $user,
                $plan,
                [
                    'payment_intent_id'  => $request->payment_intent_id,
                    'payment_method'     => 'stripe',
                    'status'             => 'active',
                    'stripe_payment_id'  => $paymentIntent->id,
                    'amount'             => $plan->price,          // <— ADICIONADO
                ]
            );

            return response()->json([
                'success'      => true,
                'message'      => 'Pagamento processado com sucesso!',
                'subscription' => $subscription,
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

    public function success()
    {
        return view('subscription.success');
    }

    public function cancel()
    {
        $user = auth()->user();
        $subscription = $user->subscription;

        if ($subscription && $subscription->isActive()) {
            $subscription->update(['status' => 'cancelled']);
            return redirect()->route('subscription.index')
                ->with('success', 'Assinatura cancelada com sucesso.');
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
        $user = auth()->user();
        $currentSubscription = $user->subscription;
        $currentPlan = $currentSubscription ? $currentSubscription->plan : null;

        // Contar usuários atuais
        $dispatcher = $user->dispatchers()->first();
        $dispatcherId = $dispatcher ? $dispatcher->id : null;
        
        $carrierIds = \App\Models\Carrier::where('dispatcher_id', $dispatcherId)->pluck('id');
        
        $currentCounts = [
            'carriers' => \App\Models\Carrier::where('dispatcher_id', $dispatcherId)->count(),
            'dispatchers' => $dispatcher ? 1 : 0,
            'employees' => \App\Models\Employee::where('dispatcher_id', $dispatcherId)->count(),
            'drivers' => \App\Models\Driver::whereIn('carrier_id', $carrierIds)->count(),
            'brokers' => \App\Models\Broker::where('user_id', $user->id)->count(),
        ];

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
     * ⭐ NOVO: Processa criação do plano customizado
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
            // Verificar se já existe plano customizado para este usuário
            $existingCustomPlan = Plan::where('user_id', $user->id)
                ->where('is_custom', true)
                ->first();

            if ($existingCustomPlan) {
                // Atualizar plano existente
                $existingCustomPlan->update([
                    'name' => "Plano Customizado - {$totalUsers} usuários",
                    'price' => $totalPrice,
                    'max_carriers' => $carriers,
                    'max_dispatchers' => $dispatchers,
                    'max_employees' => $employees,
                    'max_drivers' => $drivers,
                    'max_brokers' => $brokers,
                    'max_loads_per_month' => null, // Ilimitado para premium
                ]);
                $plan = $existingCustomPlan;
            } else {
                // Criar novo plano customizado
                $plan = Plan::create([
                    'user_id' => $user->id,
                    'name' => "Plano Customizado - {$totalUsers} usuários",
                    'slug' => 'custom-user-' . $user->id . '-' . time(),
                    'price' => $totalPrice,
                    'max_carriers' => $carriers,
                    'max_dispatchers' => $dispatchers,
                    'max_employees' => $employees,
                    'max_drivers' => $drivers,
                    'max_brokers' => $brokers,
                    'max_loads_per_month' => null, // Ilimitado para premium
                    'is_custom' => true,
                    'active' => true,
                ]);
            }

            // Redirecionar para checkout com o plano customizado
            return redirect()->route('subscription.checkout', ['plan_id' => $plan->id])
                ->with('success', 'Plano configurado! Complete o pagamento para ativar.');

        } catch (\Exception $e) {
            Log::error('Erro ao criar plano customizado', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'data' => $request->all(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar plano: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
