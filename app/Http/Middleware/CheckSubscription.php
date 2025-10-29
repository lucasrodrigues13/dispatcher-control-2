<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\BillingService;
use Illuminate\Support\Facades\Log;

class CheckSubscription
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // DEBUG: Log informações da subscription
        $subscription = $user->subscription;
        Log::info('CheckSubscription Debug', [
            'user_id' => $user->id,
            'has_subscription' => $subscription ? true : false,
            'subscription_status' => $subscription ? $subscription->status : 'none',
            'trial_ends_at' => $subscription ? $subscription->trial_ends_at : 'none',
            'blocked_at' => $subscription ? $subscription->blocked_at : 'none',
            'isOnTrial' => $subscription ? $subscription->isOnTrial() : false,
            'isActive' => $subscription ? $subscription->isActive() : false,
            'isBlocked' => $subscription ? $subscription->isBlocked() : false,
            'canAccessSystem' => $user->canAccessSystem(),
        ]);

        // Verificar se tem assinatura ativa
        if (!$user->canAccessSystem()) {
            Log::warning('User blocked access', ['user_id' => $user->id]);
            return redirect()->route('subscription.blocked')
                           ->with('error', 'Your subscription is inactive. Please update your payment method.');
        }

        // Verificar limites de uso
        $usageCheck = $this->billingService->checkUsageLimits($user);

        if (!$usageCheck['allowed']) {
            return redirect()->route('subscription.plans')
                           ->with('warning', $usageCheck['reason']);
        }

        // Adicionar warning se necessário
        if (isset($usageCheck['warning']) && $usageCheck['warning']) {
            session()->flash('usage_warning', $usageCheck['message']);
        }

        return $next($request);
    }
}
