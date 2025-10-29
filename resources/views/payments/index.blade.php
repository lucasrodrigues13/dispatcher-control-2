<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento</title>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <form id="payment-form">
        <div id="card-element"></div>
        <button type="submit" id="submit-button">Pagar R$1,00</button>
    </form>

    <script>
        const stripe = Stripe("{{ config('services.stripe.key') }}");
        const elements = stripe.elements();
        const card = elements.create('card');
        card.mount('#card-element');

        document.getElementById('payment-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            // Cria o Payment Intent no backend
            const { clientSecret } = await fetch('/api/payments/create-intent', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount: 100 })
            }).then(res => res.json());

            // Confirma o pagamento
            const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
                payment_method: { card }
            });

            if (error) {
                alert('Erro: ' + error.message);
            } else if (paymentIntent && paymentIntent.status === 'succeeded') {
                alert('Pagamento concluído com sucesso!');
            }
        });
    </script>
</body>
</html>
