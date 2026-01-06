{{-- resources/views/subscription/build-plan.blade.php --}}
@extends('layouts.app')

@section('title', 'Build Your Plan')

@section('conteudo')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="text-center mb-5">
                <h2 class="mb-3">Build Your Premium Plan</h2>
                <p class="text-muted">Choose how many users of each type you need. Minimum 2 users ($20/month)</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form id="buildPlanForm" method="POST" action="{{ route('subscription.store-custom-plan') }}">
                @csrf

                <div class="row">
                    <!-- Formulário de Seleção -->
                    <div class="col-lg-7 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-users me-2"></i>
                                    Configure Users
                                </h5>
                            </div>
                            <div class="card-body" style="padding: 1.5rem;">
                                <!-- Dispatchers -->
                                <div class="mb-4">
                                    <label for="dispatchers" class="form-label fw-bold mb-3 d-block text-center">
                                        <i class="fas fa-user-tie text-primary me-2"></i>
                                        Dispatchers
                                    </label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary" style="width: 50px; height: 50px; font-size: 22px; font-weight: bold; margin-right: 15px;" onclick="decrement('dispatchers')">
                                            −
                                        </button>
                                        <input type="number" 
                                               class="form-control text-center" 
                                               style="width: 100px; font-size: 20px; font-weight: bold; padding: 12px;" 
                                               id="dispatchers" 
                                               name="dispatchers" 
                                               value="{{ old('dispatchers', $currentCounts['dispatchers'] ?? 0) }}" 
                                               min="0" 
                                               onchange="calculatePrice()"
                                               required>
                                        <button type="button" class="btn btn-outline-secondary" style="width: 50px; height: 50px; font-size: 22px; font-weight: bold; margin-left: 15px;" onclick="increment('dispatchers')">
                                            +
                                        </button>
                                    </div>
                                    <small class="text-muted d-block text-center mt-2">Current: {{ $currentCounts['dispatchers'] ?? 0 }}</small>
                                </div>

                                <hr class="my-4">
                                
                                <!-- Employees -->
                                <div class="mb-4">
                                    <label for="employees" class="form-label fw-bold mb-3 d-block text-center">
                                        <i class="fas fa-users text-primary me-2"></i>
                                        Employees
                                    </label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary" style="width: 50px; height: 50px; font-size: 22px; font-weight: bold; margin-right: 15px;" onclick="decrement('employees')">
                                            −
                                        </button>
                                        <input type="number" 
                                               class="form-control text-center" 
                                               style="width: 100px; font-size: 20px; font-weight: bold; padding: 12px;" 
                                               id="employees" 
                                               name="employees" 
                                               value="{{ old('employees', $currentCounts['employees'] ?? 0) }}" 
                                               min="0" 
                                               onchange="calculatePrice()"
                                               required>
                                        <button type="button" class="btn btn-outline-secondary" style="width: 50px; height: 50px; font-size: 22px; font-weight: bold; margin-left: 15px;" onclick="increment('employees')">
                                            +
                                        </button>
                                    </div>
                                    <small class="text-muted d-block text-center mt-2">Current: {{ $currentCounts['employees'] ?? 0 }}</small>
                                </div>

                                <hr class="my-4">
                                
                                <!-- Carriers -->
                                <div class="mb-4">
                                    <label for="carriers" class="form-label fw-bold mb-3 d-block text-center">
                                        <i class="fas fa-truck text-primary me-2"></i>
                                        Carriers
                                    </label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary" style="width: 50px; height: 50px; font-size: 22px; font-weight: bold; margin-right: 15px;" onclick="decrement('carriers')">
                                            −
                                        </button>
                                        <input type="number" 
                                               class="form-control text-center" 
                                               style="width: 100px; font-size: 20px; font-weight: bold; padding: 12px;" 
                                               id="carriers" 
                                               name="carriers" 
                                               value="{{ old('carriers', $currentCounts['carriers'] ?? 0) }}" 
                                               min="0" 
                                               onchange="calculatePrice()"
                                               required>
                                        <button type="button" class="btn btn-outline-secondary" style="width: 50px; height: 50px; font-size: 22px; font-weight: bold; margin-left: 15px;" onclick="increment('carriers')">
                                            +
                                        </button>
                                    </div>
                                    <small class="text-muted d-block text-center mt-2">Current: {{ $currentCounts['carriers'] ?? 0 }}</small>
                                </div>

                                <hr class="my-4">
                                
                                <!-- Drivers -->
                                <div class="mb-4">
                                    <label for="drivers" class="form-label fw-bold mb-3 d-block text-center">
                                        <i class="fas fa-user text-primary me-2"></i>
                                        Drivers
                                    </label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary" style="width: 50px; height: 50px; font-size: 22px; font-weight: bold; margin-right: 15px;" onclick="decrement('drivers')">
                                            −
                                        </button>
                                        <input type="number" 
                                               class="form-control text-center" 
                                               style="width: 100px; font-size: 20px; font-weight: bold; padding: 12px;" 
                                               id="drivers" 
                                               name="drivers" 
                                               value="{{ old('drivers', $currentCounts['drivers'] ?? 0) }}" 
                                               min="0" 
                                               onchange="calculatePrice()"
                                               required>
                                        <button type="button" class="btn btn-outline-secondary" style="width: 50px; height: 50px; font-size: 22px; font-weight: bold; margin-left: 15px;" onclick="increment('drivers')">
                                            +
                                        </button>
                                    </div>
                                    <small class="text-muted d-block text-center mt-2">Current: {{ $currentCounts['drivers'] ?? 0 }}</small>
                                </div>

                                <hr class="my-4">
                                
                                <!-- Brokers -->
                                <div class="mb-4">
                                    <label for="brokers" class="form-label fw-bold mb-3 d-block text-center">
                                        <i class="fas fa-handshake text-primary me-2"></i>
                                        Brokers
                                    </label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary" style="width: 50px; height: 50px; font-size: 22px; font-weight: bold; margin-right: 15px;" onclick="decrement('brokers')">
                                            −
                                        </button>
                                        <input type="number" 
                                               class="form-control text-center" 
                                               style="width: 100px; font-size: 20px; font-weight: bold; padding: 12px;" 
                                               id="brokers" 
                                               name="brokers" 
                                               value="{{ old('brokers', $currentCounts['brokers'] ?? 0) }}" 
                                               min="0" 
                                               onchange="calculatePrice()"
                                               required>
                                        <button type="button" class="btn btn-outline-secondary" style="width: 50px; height: 50px; font-size: 22px; font-weight: bold; margin-left: 15px;" onclick="increment('brokers')">
                                            +
                                        </button>
                                    </div>
                                    <small class="text-muted d-block text-center mt-2">Current: {{ $currentCounts['brokers'] ?? 0 }}</small>
                                </div>
                                
                                <hr class="my-4">

                                <!-- AI Voice Call Service -->
                                <div class="mb-4">
                                    <div class="card border border-info bg-light">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="flex-grow-1">
                                                    <label for="ai_voice_service" class="form-label fw-bold mb-2 d-block">
                                                        <i class="fas fa-phone-alt text-info me-2"></i>
                                                        AI Voice Call Service
                                                    </label>
                                                    <p class="text-muted small mb-2">
                                                        Automatic AI-powered phone calls via VAPI integration
                                                    </p>
                                                    <p class="text-muted small mb-0">
                                                        <strong>$20/month</strong> to enable the service. Calls are charged separately via prepaid credits (average cost: $0.20/minute).
                                                    </p>
                                                </div>
                                                <div class="ms-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               id="ai_voice_service" 
                                                               name="ai_voice_service" 
                                                               value="1"
                                                               {{ old('ai_voice_service', $currentAiVoiceService ?? false) ? 'checked' : '' }}
                                                               onchange="calculatePrice()"
                                                               style="width: 3rem; height: 1.5rem;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumo e Preço -->
                    <div class="col-lg-5 mb-4">
                        <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-calculator me-2"></i>
                                    Plan Summary
                                </h5>
                            </div>
                            <div class="card-body" style="padding: 1.25rem;">
                                <div class="text-center mb-3">
                                    <div class="h1 fw-bold text-primary mb-1" id="totalPrice" style="font-size: 2.5rem;">
                                        $0.00
                                    </div>
                                    <small class="text-muted">/ month</small>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Users:</span>
                                        <strong id="totalUsers">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Price per User:</span>
                                        <strong>$10.00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2" id="aiServiceRow" style="display: none !important;">
                                        <span>AI Voice Service:</span>
                                        <strong id="aiServicePrice">$0.00</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Total:</span>
                                        <strong class="text-primary" id="formattedPrice">$0.00</strong>
                                    </div>
                                </div>

                                <div class="alert alert-info mb-3">
                                    <small>
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Includes:</strong> Unlimited loads per month
                                    </small>
                                </div>

                                <div id="errorMessage" class="alert alert-warning d-none mb-3">
                                    <small id="errorText"></small>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn" disabled>
                                    <i class="fas fa-credit-card me-2"></i>
                                    Continue to Payment
                                </button>

                                <a href="{{ route('subscription.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Back
                                </a>
                            </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Loading Overlay -->
                <div id="loadingOverlay" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999;">
                    <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">
                        <div class="text-center bg-white rounded p-4 shadow-lg">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mb-0 fw-bold">Calculating price...</p>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<script>
function increment(fieldId) {
    const input = document.getElementById(fieldId);
    const currentValue = parseInt(input.value) || 0;
    input.value = currentValue + 1;
    calculatePrice();
}

function decrement(fieldId) {
    const input = document.getElementById(fieldId);
    const currentValue = parseInt(input.value) || 0;
    if (currentValue > 0) {
        input.value = currentValue - 1;
        calculatePrice();
    }
}

// Calculate price on page load
document.addEventListener('DOMContentLoaded', function() {
    calculatePrice();
});

function calculatePrice() {
    // Show loading overlay
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.classList.remove('d-none');

    const carriers = parseInt(document.getElementById('carriers').value) || 0;
    const dispatchers = parseInt(document.getElementById('dispatchers').value) || 0;
    const employees = parseInt(document.getElementById('employees').value) || 0;
    const drivers = parseInt(document.getElementById('drivers').value) || 0;
    const brokers = parseInt(document.getElementById('brokers').value) || 0;
    const aiVoiceService = document.getElementById('ai_voice_service').checked;

    // Make AJAX request to calculate price
    fetch('{{ route("subscription.calculate-price") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            carriers: carriers,
            dispatchers: dispatchers,
            employees: employees,
            drivers: drivers,
            brokers: brokers,
            ai_voice_service: aiVoiceService
        })
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading overlay
        loadingOverlay.classList.add('d-none');

        const errorDiv = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        const submitBtn = document.getElementById('submitBtn');
        const totalUsersEl = document.getElementById('totalUsers');
        const totalPriceEl = document.getElementById('totalPrice');
        const formattedPriceEl = document.getElementById('formattedPrice');
        const aiServiceRow = document.getElementById('aiServiceRow');
        const aiServicePrice = document.getElementById('aiServicePrice');

        if (data.success) {
            // Sucesso
            errorDiv.classList.add('d-none');
            submitBtn.disabled = false;
            totalUsersEl.textContent = data.total_users;
            
            // Mostrar/ocultar linha do serviço de IA
            if (data.ai_voice_service_enabled) {
                aiServiceRow.style.display = 'flex';
                aiServicePrice.textContent = '$20.00';
            } else {
                aiServiceRow.style.display = 'none';
            }
            
            totalPriceEl.textContent = data.formatted_price;
            formattedPriceEl.textContent = data.formatted_price;
        } else {
            // Erro (menos de 2 usuários)
            errorDiv.classList.remove('d-none');
            errorText.textContent = data.error;
            submitBtn.disabled = true;
            totalUsersEl.textContent = data.total_users || 0;
            aiServiceRow.style.display = 'none';
            totalPriceEl.textContent = '$0.00';
            formattedPriceEl.textContent = '$0.00';
        }
    })
    .catch(error => {
        console.error('Error calculating price:', error);
        // Hide loading overlay on error
        loadingOverlay.classList.add('d-none');
    });
}

// Calculate price on page load
document.addEventListener('DOMContentLoaded', function() {
    calculatePrice();
});
</script>
@endsection

