@extends('layouts.app')

@section('title', 'Recharge Credits')

@section('conteudo')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-wallet me-2"></i> Recharge Voice Call Credits</h4>
                        <a href="{{ route('voice-calls.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Error Alert - Hidden by default -->
                    <div id="error-alert" class="alert alert-danger alert-dismissible fade show d-none mb-4" role="alert">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3 mt-1">
                                <i class="fas fa-exclamation-circle fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <span id="error-alert-message"></span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>

                    <!-- Current Balance -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Current Balance</small>
                                <h3 class="mb-0">${{ number_format($creditsBalance, 2) }}</h3>
                            </div>
                            <i class="fas fa-wallet fa-2x text-primary"></i>
                        </div>
                    </div>

                    <!-- Recharge Form -->
                    <form id="recharge-form">
                        @csrf

                        <!-- Amount Input -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Amount to Recharge <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input 
                                    type="number" 
                                    id="amount" 
                                    name="amount" 
                                    class="form-control form-control-lg" 
                                    placeholder="10.00" 
                                    min="10" 
                                    step="0.01"
                                    required
                                    value="10"
                                >
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Minimum: $10.00. You can add any amount, but increments of $10 are recommended.
                            </small>
                            <div id="amount-error" class="text-danger mt-1"></div>
                        </div>

                        <!-- Quick Amount Buttons -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2">Quick Select</label>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-primary quick-amount" data-amount="10">$10</button>
                                <button type="button" class="btn btn-outline-primary quick-amount" data-amount="20">$20</button>
                                <button type="button" class="btn btn-outline-primary quick-amount" data-amount="50">$50</button>
                                <button type="button" class="btn btn-outline-primary quick-amount" data-amount="100">$100</button>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <div id="card-element" class="form-control p-3" style="height: 60px;">
                                <!-- Stripe Elements will create input fields here -->
                            </div>
                            <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                        </div>

                        <!-- Customer Info -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Name</label>
                                <input type="text" id="cardholder-name" value="{{ auth()->user()->name }}"
                                       class="form-control" placeholder="Name on card" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" id="email" value="{{ auth()->user()->email }}"
                                       class="form-control" placeholder="your@email.com" required>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="alert alert-light border mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total to pay:</span>
                                <span class="h4 mb-0 text-primary" id="total-amount">$10.00</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="submit-button" class="btn btn-primary btn-lg w-100 mb-3">
                            <span id="button-text">
                                <i class="fas fa-credit-card me-2"></i>
                                Recharge Credits - <span id="button-amount">$10.00</span>
                            </span>
                            <span id="spinner" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Processing...
                            </span>
                        </button>

                        <!-- Security Info -->
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>
                                Your payment is secured with SSL encryption
                            </small>
                        </div>
                    </form>

                    <!-- Success Message -->
                    <div id="payment-success" class="d-none text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4 class="text-success">Credits Recharged Successfully!</h4>
                            <p class="text-muted mb-4">
                                <strong id="credits-added"></strong> has been added to your account.
                                <br>Your new balance is <strong id="new-balance"></strong>.
                            </p>
                        </div>
                        <a href="{{ route('voice-calls.index') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Voice Calls
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to show error in the standard location
    function showError(message) {
        const errorAlert = document.getElementById('error-alert');
        const errorMessage = document.getElementById('error-alert-message');
        if (errorAlert && errorMessage) {
            errorMessage.textContent = message;
            errorAlert.classList.remove('d-none');
            // Scroll to error
            errorAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // Function to hide error
    function hideError() {
        const errorAlert = document.getElementById('error-alert');
        if (errorAlert) {
            errorAlert.classList.add('d-none');
        }
    }

    const stripeKey = "{{ config('services.stripe.key') }}";
    
    // Verificar se a chave do Stripe está configurada
    if (!stripeKey || stripeKey.trim() === '') {
        showError('Stripe is not configured. Please contact support.');
        document.getElementById('recharge-form').classList.add('d-none');
        return;
    }

    let stripe;
    let elements;
    let cardElement;
    let paymentIntentClientSecret = null;
    
    try {
        stripe = Stripe(stripeKey);
        
        elements = stripe.elements({
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#0d6efd',
                    colorBackground: '#ffffff',
                    colorText: '#212529',
                    colorDanger: '#dc3545',
                    fontFamily: 'system-ui, -apple-system, sans-serif',
                    spacingUnit: '4px',
                    borderRadius: '0.375rem',
                }
            }
        });

        cardElement = elements.create('card', {
            hidePostalCode: true,
            style: {
                base: {
                    fontSize: '16px',
                    color: '#212529',
                    '::placeholder': {
                        color: '#6c757d',
                    },
                },
            },
        });
    } catch (error) {
        console.error('Error initializing Stripe:', error);
        showError('Error initializing payment system: ' + error.message);
        document.getElementById('recharge-form').classList.add('d-none');
        return;
    }

    // Amount input handler
    const amountInput = document.getElementById('amount');
    const totalAmountSpan = document.getElementById('total-amount');
    const buttonAmountSpan = document.getElementById('button-amount');

    function updateAmountDisplay() {
        const amount = parseFloat(amountInput.value) || 0;
        const formatted = '$' + amount.toFixed(2);
        totalAmountSpan.textContent = formatted;
        buttonAmountSpan.textContent = formatted;
    }

    amountInput.addEventListener('input', updateAmountDisplay);
    updateAmountDisplay();

    // Quick amount buttons
    document.querySelectorAll('.quick-amount').forEach(button => {
        button.addEventListener('click', function() {
            const amount = this.getAttribute('data-amount');
            amountInput.value = amount;
            updateAmountDisplay();
            // Highlight selected button
            document.querySelectorAll('.quick-amount').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    // Mount card element on page load
    try {
        cardElement.mount('#card-element');

        // Handle real-time validation errors from the card Element
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    } catch (error) {
        console.error('Error mounting card element:', error);
        showError('Error loading payment form: ' + error.message);
        document.getElementById('recharge-form').classList.add('d-none');
        return;
    }

    // Form submission
    const form = document.getElementById('recharge-form');
    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        const amount = parseFloat(amountInput.value);
        
        // Hide previous errors
        hideError();
        document.getElementById('amount-error').textContent = '';

        // Validate amount
        if (!amount || amount < 10) {
            const errorMsg = 'Minimum amount is $10.00';
            document.getElementById('amount-error').textContent = errorMsg;
            showError(errorMsg);
            return;
        }

        const submitButton = document.getElementById('submit-button');
        const buttonText = document.getElementById('button-text');
        const spinner = document.getElementById('spinner');

        // Disable form and show loading
        submitButton.disabled = true;
        buttonText.classList.add('d-none');
        spinner.classList.remove('d-none');

        try {
            // Step 1: Create PaymentIntent
            if (!paymentIntentClientSecret) {
                const response = await fetch('{{ route('voice-calls.recharge.create-payment-intent') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: amount
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    const errorMessage = data.message || 'Failed to create payment intent';
                    showError(errorMessage);
                    throw new Error(errorMessage);
                }

                paymentIntentClientSecret = data.client_secret;
            }

            // Step 2: Confirm payment with Stripe
            const cardholderName = document.getElementById('cardholder-name').value;
            
            const {error: stripeError, paymentIntent} = await stripe.confirmCardPayment(
                paymentIntentClientSecret,
                {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: cardholderName,
                            email: document.getElementById('email').value,
                        },
                    }
                }
            );

            if (stripeError) {
                const errorMessage = stripeError.message || 'Payment failed';
                showError(errorMessage);
                throw new Error(errorMessage);
            }

            // Step 3: Process payment on backend
            const processResponse = await fetch('{{ route('voice-calls.recharge.process-payment') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    payment_intent_id: paymentIntent.id,
                    amount: amount
                })
            });

            const processData = await processResponse.json();

            if (!processData.success) {
                const errorMessage = processData.message || 'Failed to process payment';
                showError(errorMessage);
                throw new Error(errorMessage);
            }

            // Success!
            hideError(); // Hide any previous errors
            form.classList.add('d-none');
            document.getElementById('payment-success').classList.remove('d-none');
            document.getElementById('credits-added').textContent = '$' + amount.toFixed(2);
            document.getElementById('new-balance').textContent = '$' + parseFloat(processData.data.new_balance).toFixed(2);

        } catch (error) {
            console.error('Error:', error);
            
            // Show error in standard location
            // Note: Some errors may have been shown already, but this ensures all errors are displayed
            showError(error.message || 'An error occurred while processing your payment. Please try again.');

            // Re-enable form (keep form visible)
            submitButton.disabled = false;
            buttonText.classList.remove('d-none');
            spinner.classList.add('d-none');
        }
    });
});
</script>
@endsection

