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
                    {{-- ⭐ NOVO: Informações de downgrade (redução de plano) --}}
                    @if(isset($isDowngrade) && $isDowngrade)
                        <div class="alert alert-success mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-arrow-down fa-2x me-3 mt-1"></i>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-2">Redução de Plano</h6>
                                    <p class="mb-2">
                                        Você está reduzindo seu plano atual. <strong>Não há cobrança imediata.</strong>
                                    </p>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Plano Atual:</small>
                                            <strong>${{ number_format($currentSubscription->amount ?? $currentSubscription->plan->price ?? 0, 2) }}/mês</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Novo Plano:</small>
                                            <strong>${{ number_format($planForCalculation->price ?? $displayPlanPrice, 2) }}/mês</strong>
                                        </div>
                                    </div>
                                    <div class="alert alert-info mb-0 p-2">
                                        <small>
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <strong>Importante:</strong> A mudança será aplicada no próximo ciclo de cobrança 
                                            ({{ $currentSubscription->expires_at ? $currentSubscription->expires_at->format('d/m/Y') : 'próximo mês' }}). 
                                            Você continuará usando o plano atual até lá.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($prorationInfo && $prorationInfo['is_upgrade'])
                        {{-- ⭐ NOVO: Informações de upgrade proporcional --}}
                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle fa-2x me-3 mt-1"></i>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-2">Upgrade Proporcional</h6>
                                    <p class="mb-2">
                                        Você já possui um plano ativo. Está adicionando mais usuários ao seu plano atual.
                                    </p>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Dias restantes:</small>
                                            <strong>{{ $prorationInfo['days_remaining'] }} dias</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Valor completo:</small>
                                            <strong>${{ number_format($prorationInfo['full_amount'], 2) }}/mês</strong>
                                        </div>
                                    </div>
                                    <div class="alert alert-warning mb-0 p-2">
                                        <small>
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <strong>Importante:</strong> A data de vencimento permanece a mesma 
                                            ({{ $prorationInfo['expires_at']->format('d/m/Y') }}). 
                                            No próximo ciclo será cobrado o valor completo de ${{ number_format($prorationInfo['full_amount'], 2) }}/mês.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card bg-gradient bg-primary text-white mb-4">
                        <div class="card-body">
                            @php
                                // ⭐ CORRIGIDO: Usar dados da sessão se disponíveis (plano configurado), senão usar plano vinculado
                                $displayPlanName = $pendingPlanData ? "Plano Customizado - {$pendingPlanData['total_users']} usuários" : $plan->name;
                                $displayPlanPrice = $pendingPlanData ? $pendingPlanData['total_price'] : $plan->price;
                            @endphp
                            <h4 class="card-title mb-2">{{ $displayPlanName }}</h4>
                            <p class="card-text mb-3 opacity-75">{{ $plan->description ?? 'Plano personalizado conforme sua configuração' }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    @if($prorationInfo && $prorationInfo['is_upgrade'])
                                        <h3 class="mb-0">${{ number_format($prorationInfo['amount'], 2) }}</h3>
                                        <small class="opacity-75">Valor proporcional ({{ $prorationInfo['days_remaining'] }} dias restantes)</small>
                                        <div class="mt-1">
                                            <small class="opacity-75 text-decoration-line-through">${{ number_format($displayPlanPrice, 2) }}</small>
                                            <small class="opacity-75 ms-2">/ mês (valor completo)</small>
                                        </div>
                                    @else
                                        <h3 class="mb-0">${{ number_format($displayPlanPrice, 2) }}</h3>
                                        <small class="opacity-75">/ {{ $plan->billing_cycle ?? 'month' }}</small>
                                    @endif
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

                    {{-- ⭐ NOVO: Botão para downgrade (sem pagamento) --}}
                    @if(isset($isDowngrade) && $isDowngrade)
                        <div class="text-center py-4">
                            <button type="button" id="downgrade-button" class="btn btn-success btn-lg w-100 mb-3">
                                <span id="downgrade-button-text">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Confirmar Redução de Plano
                                </span>
                                <span id="downgrade-spinner" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Processando...
                                </span>
                            </button>
                            <small class="text-muted d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Não há cobrança imediata. A mudança será aplicada no próximo ciclo.
                            </small>
                        </div>
                    @else
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
                                <span class="h4 mb-0 text-primary">
                                    @php
                                        $displayPrice = $pendingPlanData ? $pendingPlanData['total_price'] : $plan->price;
                                        $finalPrice = ($prorationInfo && $prorationInfo['is_upgrade']) ? $prorationInfo['amount'] : $displayPrice;
                                    @endphp
                                    ${{ number_format($finalPrice, 2) }}
                                </span>
                            </div>
                            @if($prorationInfo && $prorationInfo['is_upgrade'])
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Valor proporcional para {{ $prorationInfo['days_remaining'] }} dias restantes
                                </small>
                                <small class="text-muted d-block mt-1">
                                    Próximo pagamento: ${{ number_format($prorationInfo['full_amount'], 2) }} em {{ $prorationInfo['expires_at']->format('d/m/Y') }}
                                </small>
                            @elseif($plan->trial_days > 0 && !$currentSubscription)
                                <small class="text-muted d-block mt-1">
                                    First payment will be charged after {{ $plan->trial_days }} days
                                </small>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="submit-button" class="btn btn-primary btn-lg w-100 mb-3">
                            <span id="button-text">
                                <i class="fas fa-lock me-2"></i>
                                Confirm Payment - 
                                @php
                                    $displayPrice = $pendingPlanData ? $pendingPlanData['total_price'] : $plan->price;
                                    $finalPrice = ($prorationInfo && $prorationInfo['is_upgrade']) ? $prorationInfo['amount'] : $displayPrice;
                                @endphp
                                ${{ number_format($finalPrice, 2) }}
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
                    @endif

                    <!-- Success Message -->
                    <div id="payment-success" class="d-none text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4 class="text-success">Payment Successful!</h4>
                            <p class="text-muted mb-4">Your subscription has been activated successfully.</p>
                        </div>
                        <a href="{{ $returnUrl ?? route('dashboard.index') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            Return to Previous Page
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
            <a href="{{ route('subscription.build-plan') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Voltar
            </a>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    const stripeKey = "{{ config('services.stripe.key') }}";
    
    // ⭐ NOVO: Verificar se a chave do Stripe está configurada
    if (!stripeKey || stripeKey.trim() === '') {
        document.getElementById('loading-state').classList.add('d-none');
        showError('Stripe is not configured. Please contact support.');
        return;
    }

    let stripe;
    let elements;
    let cardElement;
    
    try {
        stripe = Stripe(stripeKey);
        console.log('Stripe initialized successfully');
        
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
        document.getElementById('loading-state').classList.add('d-none');
        showError('Erro ao inicializar o sistema de pagamento: ' + error.message);
        return;
    }

    let paymentIntentClientSecret = null;
    const planId = {{ $plan->id }};

    // ⭐ NOVO: Verificar se é downgrade
    const isDowngrade = {{ isset($isDowngrade) && $isDowngrade ? 'true' : 'false' }};
    
    // Initialize form immediately (remove loading state)
    if (!isDowngrade) {
        initializeForm();
    } else {
        // Se é downgrade, apenas remover loading state
        document.getElementById('loading-state').classList.add('d-none');
    }

    function initializeForm() {
        try {
            // Mount card element and show form immediately
            cardElement.mount('#card-element');
            document.getElementById('loading-state').classList.add('d-none');
            const paymentForm = document.getElementById('payment-form');
            if (paymentForm) {
                paymentForm.classList.remove('d-none');
            }
        } catch (error) {
            console.error('Error mounting card element:', error);
            document.getElementById('loading-state').classList.add('d-none');
            showError('Error loading payment form: ' + error.message);
        }
    }

    // ⭐ NOVO: Handler para downgrade (sem pagamento)
    if (isDowngrade) {
        const downgradeButton = document.getElementById('downgrade-button');
        if (downgradeButton) {
            downgradeButton.addEventListener('click', async () => {
                const button = document.getElementById('downgrade-button');
                const buttonText = document.getElementById('downgrade-button-text');
                const spinner = document.getElementById('downgrade-spinner');
                
                button.disabled = true;
                buttonText.classList.add('d-none');
                spinner.classList.remove('d-none');
                
                try {
                    const response = await fetch('/api/subscription/process-downgrade', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ plan_id: planId })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // ⭐ NOVO: Redirecionar para URL de origem (se existir) ou dashboard
                        const returnUrl = "{{ $returnUrl ?? route('dashboard.index') }}";
                        window.location.href = returnUrl;
                    } else {
                        throw new Error(data.message || data.error || 'Erro ao processar downgrade');
                    }
                } catch (error) {
                    showError('Erro ao processar downgrade: ' + error.message);
                    button.disabled = false;
                    buttonText.classList.remove('d-none');
                    spinner.classList.add('d-none');
                }
            });
        }
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
            if (!stripe || !cardElement) {
                throw new Error('Stripe was not initialized correctly. Please reload the page.');
            }
            
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
                // ⭐ NOVO: Usar URL de retorno da resposta do servidor se disponível
                const returnUrlFromServer = data.return_url;
                if (returnUrlFromServer) {
                    // Atualizar a URL de retorno com a do servidor
                    window.returnUrlAfterPayment = returnUrlFromServer;
                }
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

        // ⭐ NOVO: Redirecionar para URL de origem (se existir) ou dashboard
        // Priorizar URL do servidor (se disponível), senão usar a da view
        const returnUrl = window.returnUrlAfterPayment || "{{ $returnUrl ?? route('dashboard.index') }}";
        console.log('Redirecionando para:', returnUrl); // Debug
        console.log('URL da view:', "{{ $returnUrl ?? route('dashboard.index') }}"); // Debug
        console.log('URL do servidor:', window.returnUrlAfterPayment); // Debug
        setTimeout(() => {
            window.location.href = returnUrl;
        }, 2000);
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
