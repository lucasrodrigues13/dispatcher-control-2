<?php

// app/Services/StripeService.php

namespace App\Services;

use Stripe\StripeClient;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Cria um Payment Intent
     * Conforme documentação: https://docs.stripe.com/api/payment_intents/create
     */
    public function createPaymentIntent(int $amount, array $metadata = [], string $currency = 'usd')
    {
        $params = [
            'amount' => $amount,
            'currency' => $currency,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ];

        // Adiciona metadata conforme documentação do Stripe
        // https://docs.stripe.com/api/metadata
        if (!empty($metadata)) {
            $params['metadata'] = $metadata;
        }

        return $this->stripe->paymentIntents->create($params);
    }

    /**
     * Recupera um Payment Intent
     */
    public function retrievePaymentIntent(string $paymentIntentId)
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Confirma um Payment Intent (caso seja necessário)
     */
    public function confirmPaymentIntent(string $paymentIntentId)
    {
        return $this->stripe->paymentIntents->confirm($paymentIntentId);
    }

    /**
     * Processa reembolso
     */
    public function createRefund(string $paymentIntentId, int $amount = null)
    {
        $params = ['payment_intent' => $paymentIntentId];
        if ($amount) {
            $params['amount'] = $amount;
        }
        return $this->stripe->refunds->create($params);
    }

    /**
     * Cria um Customer no Stripe
     */
    public function createCustomer(array $customerData)
    {
        return $this->stripe->customers->create($customerData);
    }

    /**
     * Atualiza um Customer no Stripe
     */
    public function updateCustomer(string $customerId, array $customerData)
    {
        return $this->stripe->customers->update($customerId, $customerData);
    }

    /**
     * Recupera um Customer do Stripe
     */
    public function retrieveCustomer(string $customerId)
    {
        return $this->stripe->customers->retrieve($customerId);
    }
}
