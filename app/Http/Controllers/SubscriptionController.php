<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Services\BillingService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;

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
            \Log::error('Error creating Payment Intent', [
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

    public function blocked()
    {
        $user = auth()->user();
        $subscription = $user->subscription;

        return view('subscription.blocked', compact('subscription'));
    }

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

            return redirect()->route('dashboard')
                           ->with('success', 'Assinatura reativada com sucesso!');
        }

        return back()->with('error', 'Não foi possível reativar a assinatura.');
    }
}
