<?php

namespace App\Http\Controllers;

use App\Models\StripeEvent;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\CreditTransaction;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);
            return response('Invalid payload', 400);
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage()
            ]);
            return response('Invalid payload', 400);
        }

        // Verificar idempotência
        $stripeEvent = StripeEvent::where('stripe_event_id', $event->id)->first();

        if ($stripeEvent && $stripeEvent->processed) {
            // Evento já processado, retornar sucesso
            Log::info('Webhook event already processed', [
                'event_id' => $event->id,
                'event_type' => $event->type
            ]);
            return response('Webhook Handled', 200);
        }

        // Criar ou atualizar registro do evento
        if (!$stripeEvent) {
            $stripeEvent = StripeEvent::create([
                'stripe_event_id' => $event->id,
                'event_type' => $event->type,
                'event_object_id' => $event->data->object->id ?? '',
                'processed' => false,
                'raw_event' => json_decode($payload, true),
            ]);
        }

        // Marcar como processando
        $stripeEvent->markAsProcessing();

        try {
            // Processar evento
            $this->processEvent($event);
            
            // Marcar como processado com sucesso
            $stripeEvent->markAsCompleted();
            
            return response('Webhook Handled', 200);
        } catch (\Exception $e) {
            // Marcar como falhado
            $stripeEvent->markAsFailed($e->getMessage());
            
            Log::error('Webhook processing failed', [
                'event_id' => $event->id,
                'event_type' => $event->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retornar 500 para Stripe retentar
            return response('Webhook processing failed', 500);
        }
    }

    /**
     * Processa evento baseado no tipo
     */
    protected function processEvent($event): void
    {
        switch ($event->type) {
            case 'invoice.paid':
                $this->handleInvoicePaid($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;

            case 'invoice.finalized':
                $this->handleInvoiceFinalized($event->data->object);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            default:
                Log::info('Unhandled webhook event type', [
                    'event_type' => $event->type,
                    'event_id' => $event->id
                ]);
                break;
        }
    }

    /**
     * Handler: invoice.paid
     */
    protected function handleInvoicePaid($invoice): void
    {
        DB::transaction(function () use ($invoice) {
            if (!$invoice->subscription) {
                return; // Invoice não relacionada a subscription
            }

            $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

            if (!$subscription) {
                Log::warning('Subscription not found for invoice.paid', [
                    'subscription_id' => $invoice->subscription,
                    'invoice_id' => $invoice->id
                ]);
                return;
            }

            // Buscar subscription atualizada no Stripe
            $stripeSubscription = \Stripe\Subscription::retrieve($invoice->subscription, [
                'expand' => ['latest_invoice']
            ]);

            // Atualizar subscription
            $subscription->update([
                'status' => 'active',
                'stripe_status' => $stripeSubscription->status,
                'expires_at' => $stripeSubscription->current_period_end ? 
                    \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end) : null,
                'stripe_current_period_start' => $stripeSubscription->current_period_start ?
                    \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start) : null,
                'stripe_current_period_end' => $stripeSubscription->current_period_end ?
                    \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end) : null,
                'blocked_at' => null, // Reativar se estava bloqueado
            ]);

            Log::info('Invoice paid - subscription updated', [
                'subscription_id' => $subscription->id,
                'stripe_subscription_id' => $invoice->subscription,
                'invoice_id' => $invoice->id
            ]);
        });
    }

    /**
     * Handler: invoice.payment_failed
     */
    protected function handleInvoicePaymentFailed($invoice): void
    {
        DB::transaction(function () use ($invoice) {
            if (!$invoice->subscription) {
                return;
            }

            $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

            if (!$subscription) {
                return;
            }

            // Buscar subscription no Stripe para ver status atual
            $stripeSubscription = \Stripe\Subscription::retrieve($invoice->subscription);

            // Salvar tentativa de pagamento falhada
            PaymentAttempt::create([
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount_due,
                'status' => 'failed',
                'failure_reason' => $invoice->last_payment_error->message ?? 'Payment failed',
                'attempted_at' => now(),
                'processed_at' => now(),
            ]);

            // Verificar se subscription está unpaid ou past_due
            if (in_array($stripeSubscription->status, ['unpaid', 'past_due'])) {
                // Marcar como blocked apenas se realmente unpaid/past_due
                $subscription->update([
                    'status' => 'blocked',
                    'stripe_status' => $stripeSubscription->status,
                    'blocked_at' => now(),
                ]);

                Log::info('Subscription blocked due to payment failure', [
                    'subscription_id' => $subscription->id,
                    'stripe_status' => $stripeSubscription->status,
                    'invoice_id' => $invoice->id
                ]);
            }

            // NOTA: Se status ainda é active/trialing, Stripe ainda vai retentar
            // Não bloquear ainda neste caso
        });
    }

    /**
     * Handler: invoice.finalized
     */
    protected function handleInvoiceFinalized($invoice): void
    {
        // Geralmente apenas log, invoice.paid é o evento importante
        Log::info('Invoice finalized', [
            'invoice_id' => $invoice->id,
            'subscription_id' => $invoice->subscription
        ]);
    }

    /**
     * Handler: customer.subscription.updated
     */
    protected function handleSubscriptionUpdated($stripeSubscription): void
    {
        DB::transaction(function () use ($stripeSubscription) {
            $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

            if (!$subscription) {
                Log::warning('Subscription not found for customer.subscription.updated', [
                    'stripe_subscription_id' => $stripeSubscription->id
                ]);
                return;
            }

            // Atualizar subscription
            $subscription->update([
                'stripe_status' => $stripeSubscription->status,
                'status' => $this->mapStripeStatusToLocal($stripeSubscription->status),
                'stripe_current_period_start' => $stripeSubscription->current_period_start ?
                    \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start) : null,
                'stripe_current_period_end' => $stripeSubscription->current_period_end ?
                    \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end) : null,
                'expires_at' => $stripeSubscription->current_period_end ?
                    \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end) : null,
            ]);

            // Sincronizar subscription items
            $this->syncSubscriptionItems($subscription, $stripeSubscription->items->data);

            Log::info('Subscription updated from webhook', [
                'subscription_id' => $subscription->id,
                'stripe_status' => $stripeSubscription->status
            ]);
        });
    }

    /**
     * Handler: customer.subscription.deleted
     */
    protected function handleSubscriptionDeleted($stripeSubscription): void
    {
        DB::transaction(function () use ($stripeSubscription) {
            $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

            if (!$subscription) {
                return;
            }

            $subscription->update([
                'status' => 'cancelled',
                'stripe_status' => 'canceled',
                'blocked_at' => now(),
            ]);

            Log::info('Subscription cancelled from webhook', [
                'subscription_id' => $subscription->id
            ]);
        });
    }

    /**
     * Handler: payment_intent.succeeded (Recarga de Créditos)
     */
    protected function handlePaymentIntentSucceeded($paymentIntent): void
    {
        DB::transaction(function () use ($paymentIntent) {
            // Verificar se é recarga de créditos
            $metadata = is_array($paymentIntent->metadata) ? $paymentIntent->metadata : (array) $paymentIntent->metadata;
            
            if (!isset($metadata['transaction_type']) || $metadata['transaction_type'] !== 'credit_recharge') {
                return; // Não é recarga de créditos
            }

            // Verificar idempotência (já foi creditado?)
            $existingTransaction = CreditTransaction::where('stripe_payment_intent_id', $paymentIntent->id)
                ->where('transaction_type', 'credit')
                ->first();

            if ($existingTransaction) {
                Log::info('Credit recharge already processed', [
                    'payment_intent_id' => $paymentIntent->id
                ]);
                return;
            }

            $userId = $metadata['user_id'] ?? null;
            $amount = isset($metadata['amount']) ? (float) $metadata['amount'] : ($paymentIntent->amount / 100);

            if (!$userId) {
                Log::error('User ID not found in payment intent metadata', [
                    'payment_intent_id' => $paymentIntent->id
                ]);
                return;
            }

            $user = User::find($userId);
            if (!$user) {
                Log::error('User not found for credit recharge', [
                    'user_id' => $userId,
                    'payment_intent_id' => $paymentIntent->id
                ]);
                return;
            }

            // Creditar saldo
            $balanceBefore = $user->ai_voice_credits ?? 0.00;
            $balanceAfter = $balanceBefore + $amount;

            $user->update([
                'ai_voice_credits' => $balanceAfter
            ]);

            // Criar transação de crédito
            CreditTransaction::create([
                'user_id' => $user->id,
                'transaction_type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'source_type' => 'recharge',
                'stripe_payment_intent_id' => $paymentIntent->id,
                'description' => "Credit recharge: $" . number_format($amount, 2),
                'metadata' => [
                    'payment_intent_id' => $paymentIntent->id,
                    'customer_id' => $paymentIntent->customer,
                ]
            ]);

            Log::info('Credit recharge processed', [
                'user_id' => $user->id,
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'payment_intent_id' => $paymentIntent->id
            ]);
        });
    }

    /**
     * Handler: payment_intent.payment_failed (Recarga de Créditos)
     */
    protected function handlePaymentIntentFailed($paymentIntent): void
    {
        // Verificar se é recarga de créditos
        $metadata = is_array($paymentIntent->metadata) ? $paymentIntent->metadata : (array) $paymentIntent->metadata;
        
        if (!isset($metadata['transaction_type']) || $metadata['transaction_type'] !== 'credit_recharge') {
            return;
        }

        $userId = $metadata['user_id'] ?? null;
        if (!$userId) {
            return;
        }

        // Registrar tentativa falhada
        PaymentAttempt::create([
            'user_id' => $userId,
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'status' => 'failed',
            'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Payment failed',
            'attempted_at' => now(),
            'processed_at' => now(),
        ]);

        Log::info('Credit recharge payment failed', [
            'user_id' => $userId,
            'payment_intent_id' => $paymentIntent->id,
            'error' => $paymentIntent->last_payment_error->message ?? 'Unknown error'
        ]);
    }

    /**
     * Sincroniza subscription items
     */
    protected function syncSubscriptionItems(Subscription $subscription, array $stripeItems): void
    {
        // IDs dos items atuais no Stripe
        $stripeItemIds = array_map(function ($item) {
            return $item->id;
        }, $stripeItems);

        // Deletar items que não existem mais no Stripe
        SubscriptionItem::where('subscription_id', $subscription->id)
            ->whereNotIn('stripe_subscription_item_id', $stripeItemIds)
            ->delete();

        // Criar/Atualizar items
        foreach ($stripeItems as $stripeItem) {
            $itemType = $this->determineItemType($stripeItem);

            SubscriptionItem::updateOrCreate(
                [
                    'stripe_subscription_item_id' => $stripeItem->id
                ],
                [
                    'subscription_id' => $subscription->id,
                    'stripe_price_id' => $stripeItem->price->id,
                    'item_type' => $itemType,
                    'quantity' => $stripeItem->quantity ?? 1,
                    'unit_amount' => $stripeItem->price->unit_amount ?? 0,
                ]
            );
        }
    }

    /**
     * Determina o tipo do item baseado no price
     */
    protected function determineItemType($stripeItem): string
    {
        // Verificar metadata ou nome do produto
        if (isset($stripeItem->price->metadata->service_type) && 
            $stripeItem->price->metadata->service_type === 'ai_voice_monthly') {
            return 'ai_voice_service';
        }

        // Verificar nome do produto
        if (isset($stripeItem->price->product)) {
            $product = is_string($stripeItem->price->product) 
                ? \Stripe\Product::retrieve($stripeItem->price->product)
                : $stripeItem->price->product;

            if (isset($product->name) && stripos($product->name, 'AI Voice') !== false) {
                return 'ai_voice_service';
            }
        }

        // Default: plano principal
        return 'main_plan';
    }

    /**
     * Mapeia status do Stripe para status local
     */
    protected function mapStripeStatusToLocal(string $stripeStatus): string
    {
        return match($stripeStatus) {
            'active', 'trialing' => 'active',
            'past_due', 'unpaid' => 'blocked',
            'canceled', 'incomplete_expired' => 'cancelled',
            'incomplete' => 'inactive',
            default => 'inactive',
        };
    }
}
