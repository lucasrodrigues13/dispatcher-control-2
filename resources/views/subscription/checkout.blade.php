{{-- resources/views/subscription/checkout.blade.php --}}
@extends('layouts.subscription')

@section('title', 'Complete Subscription')

@section('conteudo')
<div class="container">
    <div class="text-center mb-5">
        <h2>Complete Your Subscription</h2>
        <p class="text-muted">Complete your payment securely</p>
    </div>

    <div class="row justify-content-center">
        <!-- Plan Summary -->
        <div class="col-lg-5 col-md-6 mb-4">
            <div class="card border-0 shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Plan Summary
                    </h5>
                </div>

                <div class="card-body">
                    <div class="card bg-gradient bg-primary text-white mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-2">{{ $plan->name }}</h4>
                            <p class="card-text mb-3 opacity-75">{{ $plan->description }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0">${{ number_format($plan->price, 2) }}</h3>
                                    <small class="opacity-75">/ {{ $plan->billing_cycle ?? 'month' }}</small>
                                </div>
                                @if($plan->trial_days > 0 && !$currentSubscription)
                                    <div class="bg-white bg-opacity-25 px-3 py-1 rounded-pill">
                                        <small class="fw-bold">{{ $plan->trial_days }} days free</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($plan->features)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">What's included:</h6>
                            <ul class="list-unstyled">
                                @foreach(json_decode($plan->features, true) as $feature)
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small>{{ $feature }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Security Badge -->
                    <div class="alert alert-light border">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            <small class="text-muted">
                                Payment protected by SSL and 256-bit encryption
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="col-lg-7 col-md-6">
            <div class="card border-0 shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Payment Information
                    </h5>
                </div>

                <div class="card-body">
                    <!-- Loading State -->
                    <div id="loading-state" class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">Loading payment form...</p>
                    </div>

                    <!-- Payment Form -->
                    <form id="payment-form" class="d-none">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Card Information</label>
                            <div id="card-element" class="form-control p-3" style="height: auto; min-height: 45px;">
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
                                <span class="h4 mb-0 text-primary">${{ number_format($plan->price, 2) }}</span>
                            </div>
                            @if($plan->trial_days > 0 && !$currentSubscription)
                                <small class="text-muted d-block mt-1">
                                    First payment will be charged after {{ $plan->trial_days }} days
                                </small>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="submit-button" class="btn btn-primary btn-lg w-100 mb-3">
                            <span id="button-text">
                                <i class="fas fa-lock me-2"></i>
                                Confirm Payment - ${{ number_format($plan->price, 2) }}
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
                                Your data is protected with SSL encryption
                            </small>
                        </div>
                    </form>

                    <!-- Success Message -->
                    <div id="payment-success" class="d-none text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4 class="text-success">Payment Successful!</h4>
                            <p class="text-muted mb-4">Your subscription has been activated successfully.</p>
                        </div>
                        <a href="{{ route('dashboard') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Go to Dashboard
                        </a>
                    </div>

                    <!-- Error Message -->
                    <div id="payment-error" class="d-none">
                        <div class="alert alert-danger" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span id="error-message"></span>
                            </div>
                        </div>
                        <button onclick="resetForm()" class="btn btn-secondary w-100">
                            <i class="fas fa-redo me-2"></i>
                            Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="row justify-content-center mt-4">
        <div class="col-auto">
            <a href="{{ route('subscription.plans') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Plans
            </a>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe("{{ config('services.stripe.key') }}");
    const elements = stripe.elements({
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

    const cardElement = elements.create('card', {
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

    let paymentIntentClientSecret = null;
    const planId = {{ $plan->id }};

    // Initialize form immediately (remove loading state)
    initializeForm();

    function initializeForm() {
        // Mount card element and show form immediately
        cardElement.mount('#card-element');
        document.getElementById('loading-state').classList.add('d-none');
        document.getElementById('payment-form').classList.remove('d-none');
    }

    // Handle form submission
    document.getElementById('payment-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        setLoading(true);

        const cardholderName = document.getElementById('cardholder-name').value;
        const email = document.getElementById('email').value;

        try {
            // Create payment intent only when user submits
            if (!paymentIntentClientSecret) {
                const response = await fetch('/api/subscription/create-payment-intent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ plan_id: planId })
                });

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                paymentIntentClientSecret = data.client_secret;
            }

            // Confirm payment
            const {error, paymentIntent} = await stripe.confirmCardPayment(paymentIntentClientSecret, {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: cardholderName,
                        email: email,
                    },
                }
            });

            if (error) {
                throw new Error(error.message);
            }

            if (paymentIntent.status === 'succeeded') {
                // Process payment on backend
                await processPayment(paymentIntent.id);
            } else {
                throw new Error('Payment was not confirmed. Status: ' + paymentIntent.status);
            }

        } catch (error) {
            setLoading(false);
            showError(error.message);
        }
    });

    async function processPayment(paymentIntentId) {
        try {
            const response = await fetch('/api/subscription/process-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    payment_intent_id: paymentIntentId,
                    plan_id: planId
                })
            });

            const data = await response.json();

            if (data.success) {
                showSuccess();
            } else {
                throw new Error(data.message || 'Error processing payment');
            }

        } catch (error) {
            setLoading(false);
            showError('Error confirming payment: ' + error.message);
        }
    }

    function setLoading(isLoading) {
        const submitButton = document.getElementById('submit-button');
        const buttonText = document.getElementById('button-text');
        const spinner = document.getElementById('spinner');

        submitButton.disabled = isLoading;

        if (isLoading) {
            buttonText.classList.add('d-none');
            spinner.classList.remove('d-none');
        } else {
            buttonText.classList.remove('d-none');
            spinner.classList.add('d-none');
        }
    }

    function showSuccess() {
        document.getElementById('payment-form').classList.add('d-none');
        document.getElementById('payment-success').classList.remove('d-none');

        // Redirect after 3 seconds
        setTimeout(() => {
            window.location.href = "{{ route('subscription.success') }}";
        }, 3000);
    }

    function showError(message) {
        document.getElementById('error-message').textContent = message;
        document.getElementById('payment-form').classList.add('d-none');
        document.getElementById('payment-error').classList.remove('d-none');
    }

    window.resetForm = function() {
        document.getElementById('payment-error').classList.add('d-none');
        document.getElementById('payment-form').classList.remove('d-none');
        setLoading(false);
        // Reset payment intent for retry
        paymentIntentClientSecret = null;
    }

    // Handle real-time validation errors from the card Element
    cardElement.on('change', ({error}) => {
        const displayError = document.getElementById('card-errors');
        if (error) {
            displayError.textContent = error.message;
        } else {
            displayError.textContent = '';
        }
    });
});
</script>
@endsection
