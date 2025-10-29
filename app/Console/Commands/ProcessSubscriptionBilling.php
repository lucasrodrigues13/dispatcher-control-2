<?php
namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Services\BillingService;
use App\Services\NotificationService;

class ProcessSubscriptionBilling extends Command
{
    protected $signature = 'billing:process';
    protected $description = 'Process subscription billing and notifications';

    public function handle()
    {
        $billingService = app(BillingService::class);
        $notificationService = app(NotificationService::class);

        // Processar assinaturas que precisam de cobrança
        $subscriptions = Subscription::where('status', 'active')
            ->whereDate('expires_at', '<=', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            // Processar cobrança
            $this->processPayment($subscription);
        }

        // Verificar pagamentos falhados e enviar notificações
        $this->processFailedPayments($notificationService);

        $this->info('Billing processing completed.');
    }

    private function processPayment($subscription)
    {
        // Aqui você integraria com o gateway de pagamento
        // Por exemplo: Stripe, PayPal, etc.

        // Simular processamento
        $success = true; // Resultado do gateway

        if ($success) {
            $subscription->update([
                'expires_at' => now()->addMonth(),
                'status' => 'active'
            ]);
        } else {
            // Criar registro de pagamento falhado
            Payment::create([
                'subscription_id' => $subscription->id,
                'amount' => $subscription->amount,
                'status' => 'failed',
                'payment_method' => $subscription->payment_method,
                'attempted_at' => now(),
                'failed_at' => now(),
                'failure_reason' => 'Payment gateway declined',
            ]);
        }
    }

    private function processFailedPayments($notificationService)
    {
        $failedPayments = Payment::where('status', 'failed')
            ->with('subscription')
            ->get();

        foreach ($failedPayments as $payment) {
            $daysOverdue = now()->diffInDays($payment->failed_at);

            // Enviar notificações baseadas nos dias em atraso
            if (in_array($daysOverdue, [1, 3, 5, 6])) {
                $notificationService->sendPaymentFailedNotification(
                    $payment->subscription,
                    $daysOverdue
                );
            }

            // Bloquear conta após 7 dias
            if ($daysOverdue >= 7) {
                $payment->subscription->update([
                    'status' => 'blocked',
                    'blocked_at' => now()
                ]);

                $notificationService->sendPaymentFailedNotification(
                    $payment->subscription,
                    $daysOverdue
                );
            }
        }
    }
}
