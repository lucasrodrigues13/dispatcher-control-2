<?php
namespace App\Services;

use App\Models\Subscription;
use App\Models\BillingNotification;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendPaymentFailedNotification(Subscription $subscription, $daysOverdue)
    {
        $type = $this->getNotificationType($daysOverdue);
        $message = $this->getNotificationMessage($type, $daysOverdue);

        // Verificar se já foi enviada
        $alreadySent = BillingNotification::where('subscription_id', $subscription->id)
            ->where('type', $type)
            ->whereDate('sent_at', now())
            ->exists();

        if ($alreadySent) {
            return;
        }

        // Enviar email
        Mail::send('emails.billing-notification', [
            'subscription' => $subscription,
            'message' => $message,
            'type' => $type
        ], function ($email) use ($subscription, $type) {
            $email->to($subscription->user->email)
                  ->subject($this->getEmailSubject($type));
        });

        // Registrar notificação
        BillingNotification::create([
            'subscription_id' => $subscription->id,
            'type' => $type,
            'sent_at' => now(),
            'email' => $subscription->user->email,
            'message' => $message,
        ]);
    }

    private function getNotificationType($daysOverdue)
    {
        return match($daysOverdue) {
            1 => 'payment_failed',
            3 => 'reminder_3_days',
            5 => 'reminder_5_days',
            6 => 'final_warning',
            7 => 'account_blocked',
            default => 'reminder_generic'
        };
    }

    private function getNotificationMessage($type, $daysOverdue)
    {
        return match($type) {
            'payment_failed' => 'Your payment has failed. Please update your payment method.',
            'reminder_3_days' => 'Your payment failed 3 days ago. Please update your payment method to avoid service interruption.',
            'reminder_5_days' => 'Your payment failed 5 days ago. Your account will be blocked in 2 days if payment is not received.',
            'final_warning' => 'Final warning: Your account will be blocked tomorrow due to failed payment.',
            'account_blocked' => 'Your account has been blocked due to failed payment. Please contact support.',
            default => "Payment overdue for {$daysOverdue} days. Please update your payment method."
        };
    }

    private function getEmailSubject($type)
    {
        return match($type) {
            'payment_failed' => 'Payment Failed - Action Required',
            'reminder_3_days' => 'Payment Reminder - Update Required',
            'reminder_5_days' => 'Urgent: Payment Required',
            'final_warning' => 'Final Warning - Account Will Be Blocked',
            'account_blocked' => 'Account Blocked - Payment Required',
            default => 'Payment Notification'
        };
    }
}
