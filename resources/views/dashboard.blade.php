@extends("layouts.app")

@section('conteudo')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <!-- ⭐ NOVO: Banner Admin quando estiver visualizando tenant -->
            @if(auth()->user()->isAdmin())
                @if(isset($viewingTenant) && $viewingTenant)
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-eye me-2"></i>
                            <div>
                                <strong>Admin Mode:</strong> Visualizando tenant <strong>{{ $viewingTenant->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $viewingTenant->email }}</small>
                            </div>
                        </div>
                    </div>
                @elseif(isset($isAdminViewingAll) && $isAdminViewingAll)
                    <div class="alert alert-warning mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt me-2"></i>
                            <div>
                                <strong>Admin Mode:</strong> Visualizando todos os tenants
                                <br>
                                <small class="text-muted">Selecione um tenant específico no dropdown acima para visualizar dados filtrados</small>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Welcome Message -->
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-2">Welcome!</h2>
                <p class="text-muted">Below you'll find your subscription information</p>
            </div>

            @if($subscription && $subscription->plan)
            <!-- Plan Card -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-body p-0">
                    <div class="bg-gradient-primary text-white p-5 text-center">
                        <div class="mb-3">
                            <i class="fas fa-star fa-3x opacity-50"></i>
                        </div>
                        <h3 class="fw-bold mb-2">{{ $subscription->plan->name }}</h3>
                        <p class="mb-4 opacity-90">
                            {{ $subscription->plan->description ?? 'Enjoy all features of your current plan' }}
                        </p>
                        @if($subscription->plan->slug !== 'admin')
                        <div class="d-flex justify-content-center align-items-baseline">
                            <h2 class="mb-0 me-2 fw-bold">${{ number_format($subscription->plan->price, 2) }}</h2>
                            <span class="opacity-75">/ {{ $subscription->plan->billing_cycle ?? 'month' }}</span>
                        </div>
                        @else
                        <div class="d-flex justify-content-center align-items-baseline">
                            <h2 class="mb-0 me-2 fw-bold">Admin</h2>
                            <span class="opacity-75">Full Access</span>
                        </div>
                        @endif
                    </div>

                    <!-- Plan Information -->
                    <div class="card-body">
                        <div class="row">
                            <!-- Limites do Plano (Esquerda) -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3 fw-bold">Plan Limits</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-user-tie text-primary me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>
                                                @if($subscription->plan->slug == 'admin')
                                                    <span class="text-muted">Depende do tenant selecionado</span>
                                                @elseif($subscription->plan->slug == 'dispatcher-pro')
                                                    Unlimited
                                                @else
                                                    {{ $subscription->plan->max_dispatchers ?? 1 }}
                                                @endif
                                            </strong> Dispatcher{{ ($subscription->plan->slug == 'admin' || $subscription->plan->slug == 'dispatcher-pro' || ($subscription->plan->max_dispatchers ?? 1) > 1) ? 's' : '' }}
                                        </span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-users text-primary me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>
                                                @if($subscription->plan->slug == 'admin')
                                                    <span class="text-muted">Depende do tenant selecionado</span>
                                                @else
                                                    {{ $subscription->plan->max_employees }}
                                                @endif
                                            </strong> Employee{{ ($subscription->plan->slug == 'admin' || $subscription->plan->max_employees != 1) ? 's' : '' }}
                                        </span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-truck text-primary me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>
                                                @if($subscription->plan->slug == 'admin')
                                                    <span class="text-muted">Depende do tenant selecionado</span>
                                                @elseif($subscription->plan->slug == 'dispatcher-pro')
                                                    Unlimited
                                                @else
                                                    {{ $subscription->plan->max_carriers }}
                                                @endif
                                            </strong> Carrier{{ ($subscription->plan->slug == 'admin' || $subscription->plan->slug == 'dispatcher-pro' || $subscription->plan->max_carriers > 1) ? 's' : '' }}
                                        </span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-user text-primary me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>
                                                @if($subscription->plan->slug == 'admin')
                                                    <span class="text-muted">Depende do tenant selecionado</span>
                                                @else
                                                    {{ $subscription->plan->max_drivers }}
                                                @endif
                                            </strong> Driver{{ ($subscription->plan->slug == 'admin' || $subscription->plan->max_drivers != 1) ? 's' : '' }}
                                        </span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-handshake text-primary me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>
                                                @if($subscription->plan->slug == 'admin')
                                                    <span class="text-muted">Depende do tenant selecionado</span>
                                                @else
                                                    {{ $subscription->plan->max_brokers ?? 0 }}
                                                @endif
                                            </strong> Broker{{ ($subscription->plan->slug == 'admin' || ($subscription->plan->max_brokers ?? 0) != 1) ? 's' : '' }}
                                        </span>
                                    </li>
                                    <li class="mb-0 d-flex align-items-center">
                                        <i class="fas fa-boxes text-primary me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>
                                                @if($subscription->plan->slug == 'admin')
                                                    <span class="text-muted">Depende do tenant selecionado</span>
                                                @elseif($subscription->plan->slug == 'dispatcher-pro')
                                                    Unlimited
                                                @elseif(method_exists($subscription, 'isOnTrial') && $subscription->isOnTrial())
                                                    Unlimited <span class="text-muted">(only in the first month after that it will be a maximum of {{ $subscription->plan->max_loads_per_month }} loads)</span>
                                                @else
                                                    {{ $subscription->plan->max_loads_per_month }}
                                                @endif
                                            </strong> Loads/Month
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Uso Atual (Direita) -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3 fw-bold">Current Usage</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-user-tie text-success me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>{{ $usageStats['dispatchers']['used'] ?? 0 }}</strong> Dispatcher{{ ($usageStats['dispatchers']['used'] ?? 0) != 1 ? 's' : '' }}
                                            @if(isset($usageStats['dispatchers']['limit']) && $usageStats['dispatchers']['limit'] !== null)
                                                <span class="text-muted">/ {{ $usageStats['dispatchers']['limit'] }}</span>
                                            @endif
                                        </span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-users text-success me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>{{ $usageStats['employees']['used'] ?? 0 }}</strong> Employee{{ ($usageStats['employees']['used'] ?? 0) != 1 ? 's' : '' }}
                                            @if(isset($usageStats['employees']['limit']) && $usageStats['employees']['limit'] !== null)
                                                <span class="text-muted">/ {{ $usageStats['employees']['limit'] }}</span>
                                            @endif
                                        </span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-truck text-success me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>{{ $usageStats['carriers']['used'] ?? 0 }}</strong> Carrier{{ ($usageStats['carriers']['used'] ?? 0) != 1 ? 's' : '' }}
                                            @if(isset($usageStats['carriers']['limit']) && $usageStats['carriers']['limit'] !== null)
                                                <span class="text-muted">/ {{ $usageStats['carriers']['limit'] }}</span>
                                            @endif
                                        </span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-user text-success me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>{{ $usageStats['drivers']['used'] ?? 0 }}</strong> Driver{{ ($usageStats['drivers']['used'] ?? 0) != 1 ? 's' : '' }}
                                            @if(isset($usageStats['drivers']['limit']) && $usageStats['drivers']['limit'] !== null)
                                                <span class="text-muted">/ {{ $usageStats['drivers']['limit'] }}</span>
                                            @endif
                                        </span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-handshake text-success me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>{{ $usageStats['brokers']['used'] ?? 0 }}</strong> Broker{{ ($usageStats['brokers']['used'] ?? 0) != 1 ? 's' : '' }}
                                            @if(isset($usageStats['brokers']['limit']) && $usageStats['brokers']['limit'] !== null)
                                                <span class="text-muted">/ {{ $usageStats['brokers']['limit'] }}</span>
                                            @endif
                                        </span>
                                    </li>
                                    <li class="mb-0 d-flex align-items-center">
                                        <i class="fas fa-boxes text-success me-2" style="width: 20px;"></i>
                                        <span>
                                            <strong>{{ $usageStats['loads_this_month']['used'] ?? 0 }}</strong> Loads
                                            @if(isset($usageStats['loads_this_month']['limit']) && $usageStats['loads_this_month']['limit'] !== null)
                                                <span class="text-muted">/ {{ $usageStats['loads_this_month']['limit'] }}</span>
                                            @else
                                                <span class="text-muted">(Unlimited)</span>
                                            @endif
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($subscription)
            <!-- Next Billing Card -->
            <div class="card border-0 shadow">
                <div class="card-header bg-white border-0 pt-4">
                    <h6 class="mb-0 text-center fw-bold">Next Billing</h6>
                </div>
                <div class="card-body text-center pb-4">
                    @if($subscription->plan->slug == 'admin')
                        <p class="text-muted mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            Admin Plan - No billing required
                        </p>
                    @elseif(method_exists($subscription, 'isOnTrial') && $subscription->isOnTrial())
                        <p class="text-muted mb-1">Trial ends:</p>
                        <h5 class="fw-bold">{{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('M d, Y') : 'N/A' }}</h5>
                    @else
                        @php
                            $nextBilling = method_exists($subscription, 'getNextBillingDate') ? $subscription->getNextBillingDate() : null;
                        @endphp
                        @if($nextBilling)
                            <h5 class="fw-bold mb-2">{{ $nextBilling->format('M d, Y') }}</h5>
                            <p class="text-muted mb-0">
                                <small>${{ number_format($subscription->amount ?? 0, 2) }} will be charged</small>
                            </p>
                        @else
                            <p class="text-muted">No billing date available</p>
                        @endif
                    @endif
                </div>
            </div>
            @endif

            <!-- @if(isset($usageCheck['suggest_upgrade']) && $usageCheck['suggest_upgrade'])
                <div class="alert alert-warning">
                    {{ $usageCheck['message'] }}
                    <a href="{{ route('subscription.build-plan') }}" class="btn btn-primary btn-sm">Upgrade</a>
                </div>
            @elseif(isset($usageCheck['warning']) && $usageCheck['warning'])
                <div class="alert alert-warning">
                    {{ $usageCheck['message'] }}
                </div>
            @endif -->
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.shadow-lg {
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
}

.shadow {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
@endsection
