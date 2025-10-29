<script src="https://js.stripe.com/v3/"></script>
<script>
  const stripe = Stripe("{{ config('services.stripe.key') }}");

  async function pay(amount) {
    const res = await fetch('/api/payments/create-intent', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ amount })
    });
    const { clientSecret } = await res.json();

    const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
      payment_method: {
        card: elements.getElement(CardElement)
      }
    });

    if (error) {
      console.error(error.message);
    } else if (paymentIntent && paymentIntent.status === 'succeeded') {
      console.log('Pagamento concluído!');
    }
  }
</script>
