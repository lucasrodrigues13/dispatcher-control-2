<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class SyncStripeSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:sync-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync subscription status between Stripe and local database';

    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        parent::__construct();
        $this->stripeService = $stripeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Stripe subscriptions synchronization...');

        // Buscar todas subscriptions locais com stripe_subscription_id
        $subscriptions = Subscription::whereNotNull('stripe_subscription_id')
            ->whereIn('status', ['active', 'blocked', 'inactive', 'past_due', 'unpaid'])
            ->get();

        $synced = 0;
        $errors = 0;
        $updated = 0;

        foreach ($subscriptions as $subscription) {
            try {
                // Buscar subscription no Stripe
                $stripeSubscription = $this->stripeService->retrieveSubscription($subscription->stripe_subscription_id);

                // Comparar status
                $localStatus = $subscription->status;
                $stripeStatus = $stripeSubscription->status;

                // Mapear status do Stripe para status local
                $mappedStatus = $this->mapStripeStatusToLocal($stripeStatus);

                // Verificar se precisa atualizar
                $needsUpdate = false;
                $updateData = [];

                if ($subscription->stripe_status !== $stripeStatus) {
                    $updateData['stripe_status'] = $stripeStatus;
                    $needsUpdate = true;
                }

                if ($subscription->status !== $mappedStatus) {
                    $updateData['status'] = $mappedStatus;
                    $needsUpdate = true;

                    // Se status mudou para blocked, adicionar blocked_at
                    if ($mappedStatus === 'blocked' && !$subscription->blocked_at) {
                        $updateData['blocked_at'] = now();
                    }

                    // Se status mudou para active, remover blocked_at
                    if ($mappedStatus === 'active' && $subscription->blocked_at) {
                        $updateData['blocked_at'] = null;
                    }
                }

                // Atualizar períodos
                if ($stripeSubscription->current_period_start) {
                    $periodStart = \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start);
                    if (!$subscription->stripe_current_period_start || 
                        $subscription->stripe_current_period_start->ne($periodStart)) {
                        $updateData['stripe_current_period_start'] = $periodStart;
                        $needsUpdate = true;
                    }
                }

                if ($stripeSubscription->current_period_end) {
                    $periodEnd = \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end);
                    if (!$subscription->stripe_current_period_end || 
                        $subscription->stripe_current_period_end->ne($periodEnd)) {
                        $updateData['stripe_current_period_end'] = $periodEnd;
                        $updateData['expires_at'] = $periodEnd;
                        $needsUpdate = true;
                    }
                }

                // Atualizar se necessário
                if ($needsUpdate) {
                    $subscription->update($updateData);
                    $updated++;
                    $this->line("Updated subscription ID {$subscription->id}: status={$mappedStatus}, stripe_status={$stripeStatus}");
                }

                $synced++;

            } catch (ApiErrorException $e) {
                if ($e->getStripeCode() === 'resource_missing') {
                    // Subscription não existe mais no Stripe
                    $subscription->update([
                        'status' => 'cancelled',
                        'stripe_status' => 'canceled',
                        'blocked_at' => now(),
                    ]);
                    $this->warn("Subscription ID {$subscription->id} not found in Stripe - marked as cancelled");
                    $updated++;
                    $synced++;
                } else {
                    $errors++;
                    $this->error("Error syncing subscription ID {$subscription->id}: {$e->getMessage()}");
                    Log::error('Error syncing subscription', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error syncing subscription ID {$subscription->id}: {$e->getMessage()}");
                Log::error('Error syncing subscription', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Synchronization completed. Synced: {$synced}, Updated: {$updated}, Errors: {$errors}");

        return Command::SUCCESS;
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
