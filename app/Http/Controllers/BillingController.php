<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Services\BillingService;

class BillingController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index()
    {
        $user = auth()->user();
        $subscription = $user->subscription;
        $payments = Payment::whereHas('subscription', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->orderBy('created_at', 'desc')->paginate(10);

        return view('billing.index', compact('subscription', 'payments'));
    }

    public function updatePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $user = auth()->user();
        $subscription = $user->subscription;

        if ($subscription) {
            $subscription->update(['payment_method' => $request->payment_method]);
            return back()->with('success', 'Payment method updated successfully.');
        }

        return back()->with('error', 'No subscription found.');
    }

    public function usage()
    {
        $user = auth()->user();
        $subscription = $user->subscription;
        $usageStats = $this->billingService->getUsageStats($user);
        $usageCheck = $this->billingService->checkUsageLimits($user, 'carrier');
        
        // Manter compatibilidade com código antigo
        $currentUsage = [
            'monthly_loads' => $usageStats['loads_this_month']['used'] ?? 0,
            'weekly_loads' => 0, // Não usado mais
        ];

        return view('billing.usage', compact('subscription', 'usageCheck', 'currentUsage', 'usageStats'));
    }
}
