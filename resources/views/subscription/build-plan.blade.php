{{-- resources/views/subscription/build-plan.blade.php --}}
@extends('layouts.app')

@section('title', 'Montar Seu Plano')

@section('conteudo')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="text-center mb-5">
                <h2 class="mb-3">Montar Seu Plano Premium</h2>
                <p class="text-muted">Escolha quantos usuários de cada tipo você precisa. Mínimo 2 usuários ($20/mês)</p>
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
                                    Configurar Usuários
                                </h5>
                            </div>
                            <div class="card-body" style="padding: 1.5rem;">
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
                                    <small class="text-muted d-block text-center mt-2">Atual: {{ $currentCounts['carriers'] ?? 0 }}</small>
                                </div>

                                <hr class="my-4">
                                
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
                                    <small class="text-muted d-block text-center mt-2">Atual: {{ $currentCounts['dispatchers'] ?? 0 }}</small>
                                </div>

                                <hr class="my-4">
                                
                                <!-- Employees -->
                                <div class="mb-4">
                                    <label for="employees" class="form-label fw-bold mb-3 d-block text-center">
                                        <i class="fas fa-user-friends text-primary me-2"></i>
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
                                    <small class="text-muted d-block text-center mt-2">Atual: {{ $currentCounts['employees'] ?? 0 }}</small>
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
                                    <small class="text-muted d-block text-center mt-2">Atual: {{ $currentCounts['drivers'] ?? 0 }}</small>
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
                                    <small class="text-muted d-block text-center mt-2">Atual: {{ $currentCounts['brokers'] ?? 0 }}</small>
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
                                    Resumo do Plano
                                </h5>
                            </div>
                            <div class="card-body" style="padding: 1.25rem;">
                                <div class="text-center mb-3">
                                    <div class="h1 fw-bold text-primary mb-1" id="totalPrice" style="font-size: 2.5rem;">
                                        $0.00
                                    </div>
                                    <small class="text-muted">/ mês</small>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total de Usuários:</span>
                                        <strong id="totalUsers">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Preço por Usuário:</span>
                                        <strong>$10.00</strong>
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
                                        <strong>Inclui:</strong> Cargas ilimitadas por mês
                                    </small>
                                </div>

                                <div id="errorMessage" class="alert alert-warning d-none mb-3">
                                    <small id="errorText"></small>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn" disabled>
                                    <i class="fas fa-credit-card me-2"></i>
                                    Continuar para Pagamento
                                </button>

                                <a href="{{ route('subscription.plans') }}" class="btn btn-outline-secondary w-100 mt-2">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Voltar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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

function calculatePrice() {
    const carriers = parseInt(document.getElementById('carriers').value) || 0;
    const dispatchers = parseInt(document.getElementById('dispatchers').value) || 0;
    const employees = parseInt(document.getElementById('employees').value) || 0;
    const drivers = parseInt(document.getElementById('drivers').value) || 0;
    const brokers = parseInt(document.getElementById('brokers').value) || 0;

    // Fazer requisição AJAX para calcular preço
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
            brokers: brokers
        })
    })
    .then(response => response.json())
    .then(data => {
        const errorDiv = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        const submitBtn = document.getElementById('submitBtn');
        const totalUsersEl = document.getElementById('totalUsers');
        const totalPriceEl = document.getElementById('totalPrice');
        const formattedPriceEl = document.getElementById('formattedPrice');

        if (data.success) {
            // Sucesso
            errorDiv.classList.add('d-none');
            submitBtn.disabled = false;
            totalUsersEl.textContent = data.total_users;
            totalPriceEl.textContent = data.formatted_price;
            formattedPriceEl.textContent = data.formatted_price;
        } else {
            // Erro (menos de 2 usuários)
            errorDiv.classList.remove('d-none');
            errorText.textContent = data.error;
            submitBtn.disabled = true;
            totalUsersEl.textContent = data.total_users || 0;
            totalPriceEl.textContent = '$0.00';
            formattedPriceEl.textContent = '$0.00';
        }
    })
    .catch(error => {
        console.error('Erro ao calcular preço:', error);
    });
}

// Calcular preço ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    calculatePrice();
});
</script>
@endsection

