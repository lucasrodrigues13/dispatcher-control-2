<?php

// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    private StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Rota para criar um Payment Intent
     */
    public function index()
    {
        return view('payments.index');
    }

    public function createIntent(Request $request): JsonResponse
    {
        $request->validate([ 'amount' => 'required|integer|min:1' ]);
        $intent = $this->stripeService->createPaymentIntent($request->amount);
        return response()->json([
            'clientSecret' => $intent->client_secret,
        ]);
    }

    /**
     * Rota para confirmar um Payment Intent (opcional)
     */
    public function confirmIntent(Request $request): JsonResponse
    {
        $request->validate([ 'payment_intent_id' => 'required|string' ]);
        $intent = $this->stripeService->confirmPaymentIntent($request->payment_intent_id);
        return response()->json($intent);
    }

    /**
     * Rota para reembolso
     */
    public function refund(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'amount'            => 'integer|min:1',
        ]);
        $refund = $this->stripeService->createRefund(
            $request->payment_intent_id,
            $request->input('amount')
        );
        return response()->json($refund);
    }
}
