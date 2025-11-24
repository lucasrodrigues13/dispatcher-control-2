<?php

// app/Services/StripeService.php

namespace App\Services;

use Stripe\StripeClient;

class StripeService
{
    private ?StripeClient $stripe = null;

    public function __construct()
    {
        // Lazy initialization - só cria o StripeClient quando necessário
        // Isso evita erro quando STRIPE_SECRET não está configurado
    }

    /**
     * Get or initialize Stripe client
     */
    private function getStripeClient(): StripeClient
    {
        if ($this->stripe === null) {
            $secret = config('services.stripe.secret');
            
            if (empty($secret)) {
                throw new \RuntimeException(
                    'Stripe secret key não configurada. Por favor, defina STRIPE_SECRET no arquivo .env'
                );
            }
            
            $this->stripe = new StripeClient($secret);
        }
        
        return $this->stripe;
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

        return $this->getStripeClient()->paymentIntents->create($params);
    }

    /**
     * Recupera um Payment Intent
     */
    public function retrievePaymentIntent(string $paymentIntentId)
    {
        return $this->getStripeClient()->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Confirma um Payment Intent (caso seja necessário)
     */
    public function confirmPaymentIntent(string $paymentIntentId)
    {
        return $this->getStripeClient()->paymentIntents->confirm($paymentIntentId);
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
        return $this->getStripeClient()->refunds->create($params);
    }

    /**
     * Cria um Customer no Stripe
     */
    public function createCustomer(array $customerData)
    {
        return $this->getStripeClient()->customers->create($customerData);
    }

    /**
     * Atualiza um Customer no Stripe
     */
    public function updateCustomer(string $customerId, array $customerData)
    {
        return $this->getStripeClient()->customers->update($customerId, $customerData);
    }

    /**
     * Recupera um Customer do Stripe
     */
    public function retrieveCustomer(string $customerId)
    {
        return $this->getStripeClient()->customers->retrieve($customerId);
    }

    /**
     * ⭐ NOVO: Atualiza uma Subscription no Stripe para o próximo ciclo
     * Usado para downgrades onde não há pagamento imediato
     */
    public function updateSubscriptionForNextCycle(string $subscriptionId, array $updateData)
    {
        // Atualizar subscription no Stripe para que a mudança seja aplicada no próximo ciclo
        // Isso é feito através do update da subscription com proration_behavior = 'none'
        // para que não cobre nada agora, apenas no próximo ciclo
        $params = array_merge($updateData, [
            'proration_behavior' => 'none', // Não aplicar proratação, mudança só no próximo ciclo
        ]);

        return $this->getStripeClient()->subscriptions->update($subscriptionId, $params);
    }
}
