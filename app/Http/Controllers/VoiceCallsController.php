<?php

namespace App\Http\Controllers;

use App\Models\LoadPickupConfirmation;
use App\Models\Load;
use App\Models\Subscription;
use App\Services\BillingService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoiceCallsController extends Controller
{
    protected $billingService;
    protected $stripeService;

    public function __construct(BillingService $billingService, StripeService $stripeService)
    {
        $this->billingService = $billingService;
        $this->stripeService = $stripeService;
    }

    /**
     * Display the voice calls index page
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $mainUser = $this->billingService->getMainUser($user);
        
        // Get user's credits balance
        $creditsBalance = $mainUser->ai_voice_credits ?? 0.00;
        
        // Get load IDs that belong to this user (TenantScope will filter automatically)
        $loadIds = Load::pluck('id')->toArray();
        
        // Get calls statistics
        $totalCalls = LoadPickupConfirmation::whereIn('load_id', $loadIds)->count();
        
        // Sum retorna centavos (integer), converter para dólares
        $totalCostCents = LoadPickupConfirmation::whereIn('load_id', $loadIds)
            ->whereNotNull('call_cost')
            ->sum('call_cost') ?? 0;
        $totalCost = $totalCostCents / 100;
        
        $successCalls = LoadPickupConfirmation::whereIn('load_id', $loadIds)
            ->where('vapi_call_status', 'success')
            ->count();
        
        return view('voice-calls.index', compact('creditsBalance', 'totalCalls', 'totalCost', 'successCalls'));
    }

    /**
     * Get paginated voice calls
     */
    public function getCalls(Request $request)
    {
        try {
            $user = auth()->user();
            $mainUser = $this->billingService->getMainUser($user);
            
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');

            // Get load IDs that belong to this user (TenantScope will filter automatically)
            $loadIds = Load::pluck('id')->toArray();

            $query = LoadPickupConfirmation::with(['loadRelation' => function($query) {
                $query->with('dispatcher.user');
            }])
            ->whereIn('load_id', $loadIds)
            ->orderBy('created_at', 'desc');

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('vapi_call_id', 'like', "%{$search}%")
                      ->orWhere('contact_name', 'like', "%{$search}%")
                      ->orWhere('vapi_call_status', 'like', "%{$search}%")
                      ->orWhereHas('loadRelation', function($query) use ($search) {
                          $query->where('load_id', 'like', "%{$search}%")
                                ->orWhere('internal_load_id', 'like', "%{$search}%")
                                ->orWhere('pickup_phone', 'like', "%{$search}%");
                      });
                });
            }

            $calls = $query->paginate($perPage);

            // Format data for frontend
            $formattedCalls = $calls->items();
            $formattedCalls = collect($formattedCalls)->map(function($call) {
                $load = $call->loadRelation;
                
                // Determine success evaluation based on vapi_call_status
                $successEvaluation = $call->vapi_call_status === 'success' ? 'Pass' : 'Fail';
                
                // Determine ended reason (simplified - can be enhanced later)
                $endedReason = 'Assistant Ended Call';
                if ($call->vapi_call_status === 'fail') {
                    $endedReason = 'Call Failed';
                }
                
                // Format duration
                $duration = $call->call_duration ? $this->formatDuration($call->call_duration) : '-';
                
                return [
                    'id' => $call->id,
                    'call_id' => substr($call->vapi_call_id, 0, 12) . '...',
                    'call_id_full' => $call->vapi_call_id,
                    'load_id' => $load->load_id ?? $load->internal_load_id ?? 'N/A',
                    'customer_phone' => $load->pickup_phone ?? '-',
                    'type' => 'Outbound',
                    'ended_reason' => $endedReason,
                    'success_evaluation' => $successEvaluation,
                    'start_time' => $call->created_at->format('M d, Y, H:i'),
                    'duration' => $duration,
                    'duration_seconds' => $call->call_duration,
                    'cost' => $call->call_cost ? number_format((float) $call->call_cost, 2) : '0.00', // MoneyCast retorna em dólares
                    'status' => $call->vapi_call_status,
                    'has_audio' => !empty($call->call_record_url),
                    'has_transcription' => !empty($call->call_transcription_url),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $formattedCalls,
                    'current_page' => $calls->currentPage(),
                    'per_page' => $calls->perPage(),
                    'total' => $calls->total(),
                    'last_page' => $calls->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching voice calls: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching calls: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format duration in seconds to human readable format (e.g., "1m 31s")
     */
    private function formatDuration($seconds)
    {
        if (!$seconds || $seconds < 1) {
            return '0s';
        }
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        if ($minutes > 0) {
            return $minutes . 'm ' . $remainingSeconds . 's';
        }
        
        return $remainingSeconds . 's';
    }

    /**
     * Show recharge credits page
     */
    public function showRecharge()
    {
        $user = auth()->user();
        $mainUser = $this->billingService->getMainUser($user);
        
        // Get user's credits balance
        $creditsBalance = $mainUser->ai_voice_credits ?? 0.00;
        
        return view('voice-calls.recharge', compact('creditsBalance'));
    }

    /**
     * Create PaymentIntent for credits recharge
     * ⚠️ VALIDAÇÕES OBRIGATÓRIAS: Plano ativo + Serviço de IA ativo
     */
    public function createPaymentIntentForCredits(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:10|max:10000',
            ], [
                'amount.required' => 'Amount is required',
                'amount.numeric' => 'Amount must be a number',
                'amount.min' => 'Minimum amount is $10.00',
                'amount.max' => 'Maximum amount is $10,000.00',
            ]);

            $user = auth()->user();
            $mainUser = $this->billingService->getMainUser($user);
            
            $amount = (float) $request->input('amount');

            // ⚠️ VALIDAÇÕES OBRIGATÓRIAS ANTES DE CRIAR PAYMENT INTENT
            $validationResult = $this->validateCreditRecharge($mainUser);
            if (!$validationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validationResult['message'],
                    'code' => $validationResult['code'] ?? 'validation_failed'
                ], 400);
            }

            $amountInCents = (int) round($amount * 100);

            // Criar/Recuperar Customer no Stripe se necessário
            $customerId = $this->getOrCreateStripeCustomer($mainUser);

            // Create PaymentIntent via Stripe
            $paymentIntent = $this->stripeService->createPaymentIntent(
                $amountInCents,
                [
                    'customer' => $customerId,
                    'user_id' => (string) $mainUser->id,
                    'transaction_type' => 'credit_recharge',
                    'amount' => (string) $amount,
                ],
                'usd'
            );

            Log::info('Payment Intent created for credit recharge', [
                'user_id' => $mainUser->id,
                'amount' => $amount,
                'payment_intent_id' => $paymentIntent->id
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating payment intent for credits: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'amount' => $request->input('amount'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating payment intent: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valida se pode realizar recarga de créditos
     * ⚠️ CRÍTICO: Implementar ANTES de criar Payment Intent
     */
    protected function validateCreditRecharge($user): array
    {
        // 1. Validar amount mínimo
        // (já validado no request, mas pode adicionar lógica adicional)

        // 2. Buscar subscription ativa
        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('stripe_status')
                  ->orWhereIn('stripe_status', ['active', 'trialing']);
            })
            ->first();

        if (!$subscription) {
            return [
                'valid' => false,
                'message' => 'Your plan is inactive. Please resolve payment to use AI Voice Service.',
                'code' => 'plan_inactive'
            ];
        }

        // 3. Validar serviço de IA ativo no plano
        // Usar o método do BillingService que verifica corretamente se o plano tem o serviço
        if (!$this->billingService->hasAiVoiceService($user)) {
            return [
                'valid' => false,
                'message' => 'Activate AI Voice Service to add credits.',
                'code' => 'service_not_active'
            ];
        }

        // 4. Validação dupla: verificar status no Stripe se subscription tem stripe_subscription_id
        if ($subscription->stripe_subscription_id) {
            try {
                $stripeSubscription = $this->stripeService->retrieveSubscription($subscription->stripe_subscription_id);
                
                if (!in_array($stripeSubscription->status, ['active', 'trialing'])) {
                    return [
                        'valid' => false,
                        'message' => 'Recharge blocked: service unavailable at this time.',
                        'code' => 'stripe_subscription_inactive'
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Error checking Stripe subscription status', [
                    'subscription_id' => $subscription->stripe_subscription_id,
                    'error' => $e->getMessage()
                ]);
                // Continuar mesmo com erro (pode ser temporário)
            }
        }

        // Todas validações passaram
        return [
            'valid' => true,
            'message' => 'Validation passed'
        ];
    }

    /**
     * Obtém ou cria Customer no Stripe
     */
    protected function getOrCreateStripeCustomer($user): ?string
    {
        // Se já tem customer_id, retornar
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        // Criar customer no Stripe
        try {
            $customer = $this->stripeService->createCustomer([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'internal_user_id' => (string) $user->id,
                    'user_type' => 'owner',
                ]
            ]);

            // Salvar customer_id
            $user->update([
                'stripe_customer_id' => $customer->id
            ]);

            return $customer->id;
        } catch (\Exception $e) {
            Log::error('Error creating Stripe customer', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process credits payment and add credits to user
     */
    public function processCreditsPayment(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'payment_intent_id' => 'required|string',
                'amount' => 'required|numeric|min:10',
            ]);

            $user = auth()->user();
            $mainUser = $this->billingService->getMainUser($user);
            
            $amount = (float) $request->input('amount');
            $paymentIntentId = $request->input('payment_intent_id');

            // Verificar PaymentIntent no Stripe
            $paymentIntent = $this->stripeService->retrievePaymentIntent($paymentIntentId);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not completed. Status: ' . $paymentIntent->status
                ], 400);
            }

            // Adicionar créditos ao usuário
            $currentCredits = $mainUser->ai_voice_credits ?? 0.00;
            $newBalance = $currentCredits + $amount;
            
            $mainUser->update([
                'ai_voice_credits' => $newBalance
            ]);

            Log::info('Credits recharged successfully', [
                'user_id' => $mainUser->id,
                'amount_added' => $amount,
                'previous_balance' => $currentCredits,
                'new_balance' => $newBalance,
                'payment_intent_id' => $paymentIntentId,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully added $" . number_format($amount, 2) . " to your credits balance.",
                'data' => [
                    'credits_added' => $amount,
                    'previous_balance' => $currentCredits,
                    'new_balance' => $newBalance,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error processing credits payment: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'payment_intent_id' => $request->input('payment_intent_id'),
                'amount' => $request->input('amount'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }
}
