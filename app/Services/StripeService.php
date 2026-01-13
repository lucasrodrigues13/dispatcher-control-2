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

    /**
     * Cria uma Subscription no Stripe
     * 
     * @param string $customerId ID do customer no Stripe
     * @param array $items Array de items (price_data ou price)
     * @param array $metadata Metadata adicional
     * @return \Stripe\Subscription
     */
    public function createSubscription(string $customerId, array $items, array $metadata = [])
    {
        $params = [
            'customer' => $customerId,
            'items' => $items,
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => [
                'payment_method_types' => ['card'],
                'save_default_payment_method' => 'on_subscription',
            ],
        ];

        if (!empty($metadata)) {
            $params['metadata'] = $metadata;
        }

        return $this->getStripeClient()->subscriptions->create($params);
    }

    /**
     * Recupera uma Subscription do Stripe
     */
    public function retrieveSubscription(string $subscriptionId)
    {
        return $this->getStripeClient()->subscriptions->retrieve($subscriptionId);
    }

    /**
     * Atualiza uma Subscription no Stripe
     * 
     * @param string $subscriptionId
     * @param array $updateData Dados para atualizar
     * @param bool $proration Se deve aplicar proration (default: true para upgrades)
     * @return \Stripe\Subscription
     */
    public function updateSubscription(string $subscriptionId, array $updateData, bool $proration = true)
    {
        if (!isset($updateData['proration_behavior'])) {
            $updateData['proration_behavior'] = $proration ? 'always_invoice' : 'none';
        }

        return $this->getStripeClient()->subscriptions->update($subscriptionId, $updateData);
    }

    /**
     * Cria um Subscription Item
     */
    public function createSubscriptionItem(string $subscriptionId, array $itemData)
    {
        $params = array_merge($itemData, [
            'subscription' => $subscriptionId,
        ]);

        return $this->getStripeClient()->subscriptionItems->create($params);
    }

    /**
     * Atualiza um Subscription Item
     */
    public function updateSubscriptionItem(string $subscriptionItemId, array $updateData, bool $proration = true)
    {
        if (!isset($updateData['proration_behavior'])) {
            $updateData['proration_behavior'] = $proration ? 'always_invoice' : 'none';
        }

        return $this->getStripeClient()->subscriptionItems->update($subscriptionItemId, $updateData);
    }

    /**
     * Deleta um Subscription Item
     */
    public function deleteSubscriptionItem(string $subscriptionItemId, bool $proration = false)
    {
        $params = [
            'proration_behavior' => $proration ? 'always_invoice' : 'none',
        ];

        return $this->getStripeClient()->subscriptionItems->delete($subscriptionItemId, $params);
    }

    /**
     * Cria um Price dinâmico (para planos customizados)
     */
    public function createPrice(array $priceData)
    {
        return $this->getStripeClient()->prices->create($priceData);
    }

    /**
     * Recupera um Price do Stripe
     */
    public function retrievePrice(string $priceId)
    {
        return $this->getStripeClient()->prices->retrieve($priceId);
    }
}
