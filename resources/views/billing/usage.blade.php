{{-- resources/views/billing/usage.blade.php --}}
@extends('layouts.app')

@section('title', 'Usage Statistics')

@section('conteudo')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-bar"></i> Usage Statistics</h2>
        <a href="{{ route('subscription.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Subscription
        </a>
    </div>

    <div class="row">
        <!-- Current Plan Info -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Current Plan</h5>
                </div>
                <div class="card-body">
                    @if($subscription)
                        <h6>{{ $subscription->plan->name }}</h6>
                        <p class="text-muted mb-1">${{ number_format($subscription->amount, 2) }}/month</p>
                        <span class="badge bg-{{ $subscription->isActive() ? 'success' : 'warning' }}">
                            {{ ucfirst($subscription->status) }}
                        </span>

                        @if($subscription->isOnTrial())
                            <div class="mt-3">
                                <small class="text-muted">Trial ends: {{ $subscription->trial_ends_at->format('M d, Y') }}</small>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">No active subscription</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Usage Overview -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Usage Overview</h5>
                </div>
                <div class="card-body">
                    @if($subscription)
                        <div class="row">
                            <div class="col-md-6">
                                <h6>This Week</h6>
                                <div class="progress mb-2">
                                    @php
                                        $weeklyLimit = $subscription->plan->max_loads_per_week ?? 100;
                                        $weeklyUsage = $currentUsage['weekly_loads'] ?? 0;
                                        $weeklyPercent = $weeklyLimit > 0 ? min(($weeklyUsage / $weeklyLimit) * 100, 100) : 0;
                                    @endphp
                                    <div class="progress-bar" style="width: {{ $weeklyPercent }}%"></div>
                                </div>
                                <small class="text-muted">
                                    {{ $weeklyUsage }} / {{ $weeklyLimit == 100 ? 'Unlimited' : $weeklyLimit }} loads
                                </small>
                            </div>
                            <div class="col-md-6">
                                <h6>This Month</h6>
                                <div class="progress mb-2">
                                    @php
                                        $monthlyLimit = $subscription->plan->max_loads_per_month ?? 200;
                                        $monthlyUsage = $currentUsage['monthly_loads'] ?? 0;
                                        $monthlyPercent = $monthlyLimit > 0 ? min(($monthlyUsage / $monthlyLimit) * 100, 100) : 0;
                                    @endphp
                                    <div class="progress-bar" style="width: {{ $monthlyPercent }}%"></div>
                                </div>
                                <small class="text-muted">
                                    {{ $monthlyUsage }} / {{ $monthlyLimit == 200 ? 'Unlimited' : $monthlyLimit }} loads
                                </small>
                            </div>
                        </div>

                        <!-- Limits Status -->
                        <div class="row mt-4">
                            <div class="col-12">
                                @if(!$usageCheck['allowed'])
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Limit Reached:</strong> {{ $usageCheck['reason'] }}
                                        @if(isset($usageCheck['remaining_loads']))
                                            <br><small>Remaining loads: {{ $usageCheck['remaining_loads'] }}</small>
                                        @endif
                                    </div>
                                @elseif(isset($usageCheck['warning']) && $usageCheck['warning'])
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $usageCheck['message'] }}
                                    </div>
                                @else
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i>
                                        You're within your plan limits. Keep up the good work!
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No usage data available without an active subscription.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Resource Usage -->
    @if($subscription)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Resource Usage</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-truck fa-2x text-primary mb-2"></i>
                                    <h6>Carriers</h6>
                                    <p class="mb-0">
                                        {{ auth()->user()->carriers()->count() }} / {{ $subscription->plan->max_carriers }}
                                    </p>
                                    @if(auth()->user()->carriers()->count() > $subscription->plan->max_carriers)
                                        <small class="text-warning">+${{ (auth()->user()->carriers()->count() - $subscription->plan->max_carriers) * 10 }}/month</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                    <h6>Employees</h6>
                                    <p class="mb-0">
                                        {{ auth()->user()->employees()->count() }} / {{ $subscription->plan->max_employees }}
                                    </p>
                                    @if(auth()->user()->employees()->count() > $subscription->plan->max_employees)
                                        <small class="text-warning">+${{ (auth()->user()->employees()->count() - $subscription->plan->max_employees) * 10 }}/month</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-user fa-2x text-primary mb-2"></i>
                                    <h6>Drivers</h6>
                                    <p class="mb-0">
                                        {{ auth()->user()->drivers()->count() }} / {{ $subscription->plan->max_drivers }}
                                    </p>
                                    @if(auth()->user()->drivers()->count() > $subscription->plan->max_drivers)
                                        <small class="text-warning">+${{ (auth()->user()->drivers()->count() - $subscription->plan->max_drivers) * 10 }}/month</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                    <h6>Loads (Month)</h6>
                                    <p class="mb-0">
                                        {{ $currentUsage['monthly_loads'] ?? 0 }} / {{ $subscription->plan->max_loads_per_month ?? 'Unlimited' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Upgrade Suggestion -->
    @if($subscription && ($subscription->isOnTrial() || (isset($usageCheck['warning']) && $usageCheck['warning'])))
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <i class="fas fa-arrow-up"></i> Consider Upgrading
                        </h5>
                        <p class="card-text">
                            @if($subscription->isOnTrial())
                                Your trial period is active. Upgrade to a paid plan to continue using the system after your trial expires.
                            @else
                                You're approaching your usage limits. Upgrade to get more capacity and avoid interruptions.
                            @endif
                        </p>
                        <a href="{{ route('subscription.plans') }}" class="btn btn-warning">
                            <i class="fas fa-arrow-up"></i> View Plans & Upgrade
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
