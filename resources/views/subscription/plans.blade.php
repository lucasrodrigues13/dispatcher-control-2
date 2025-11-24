{{-- resources/views/subscription/plans.blade.php --}}
@extends('layouts.app')

@section('title', 'Choose Your Plan')

@section('conteudo')
<div class="container">
    <div class="text-center mb-5">
        <h2>Choose Your Plan</h2>
        <p class="text-muted">Select the plan that best fits your business needs</p>
    </div>

    <div class="row justify-content-center">
        @foreach($plans->where('is_trial', false) as $plan)
            <div class="col-md-4 mb-4">
                <div class="card {{ $currentSubscription && $currentSubscription->plan_id == $plan->id ? 'border-primary' : '' }}">
                    @if($currentSubscription && $currentSubscription->plan_id == $plan->id)
                        <div class="card-header bg-primary text-white text-center">
                            <small><i class="fas fa-check"></i> Current Plan</small>
                        </div>
                    @endif

                    <div class="card-body text-center">
                        <h4 class="card-title">{{ $plan->name }}</h4>
                        <h2 class="text-primary">${{ number_format($plan->price, 0) }}<small class="text-muted">/month</small></h2>

                        <ul class="list-unstyled mt-4">
                            <li class="mb-2">
                                <i class="fas fa-user-tie text-success"></i>
                                {{ $plan->max_dispatchers ?? 1 }} Dispatcher{{ ($plan->max_dispatchers ?? 1) != 1 ? 's' : '' }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-users text-success"></i>
                                {{ $plan->max_employees }} Employee{{ $plan->max_employees != 1 ? 's' : '' }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-truck text-success"></i>
                                {{ $plan->max_carriers }} Carrier{{ $plan->max_carriers > 1 ? 's' : '' }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-user text-success"></i>
                                {{ $plan->max_drivers }} Driver{{ $plan->max_drivers != 1 ? 's' : '' }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-handshake text-success"></i>
                                {{ $plan->max_brokers ?? 0 }} Broker{{ ($plan->max_brokers ?? 0) != 1 ? 's' : '' }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-boxes text-success"></i>
                                {{ $plan->max_loads_per_month ?? 'Unlimited' }} Loads/Month
                            </li>
                            <!-- <li class="mb-2">
                                <i class="fas fa-calendar-week text-success"></i>
                                {{ $plan->max_loads_per_week ?? 'Unlimited' }} Loads/Week
                            </li> -->
                        </ul>

                        <div class="mt-4">
                            @if($currentSubscription && $currentSubscription->plan_id == $plan->id)
                                <button class="btn btn-outline-primary" disabled>Current Plan</button>
                            @else
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#upgradeModal{{ $plan->id }}">
                                    @if($currentSubscription && $currentSubscription->isOnTrial())
                                        Upgrade to {{ $plan->name }}
                                    @else
                                        Choose {{ $plan->name }}
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>

                    @if($plan->slug == 'dispatcher-pro')
                        <div class="card-footer">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Additional charges: +$10/month per extra carrier, employee, or driver
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upgrade Modal for each plan -->
            <div class="modal fade" id="upgradeModal{{ $plan->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Upgrade to {{ $plan->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('subscription.upgrade') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                            <div class="modal-body">
                                <div class="mb-3">
                                    <h6>Plan Summary</h6>
                                    <ul class="list-unstyled">
                                        <li>Base price: ${{ number_format($plan->price, 2) }}/month</li>
                                        <li>{{ $plan->max_dispatchers ?? 1 }} Dispatcher(s) included</li>
                                        <li>{{ $plan->max_employees }} Employee(s) included</li>
                                        <li>{{ $plan->max_carriers }} Carrier(s) included</li>
                                        <li>{{ $plan->max_drivers }} Driver(s) included</li>
                                        <li>{{ $plan->max_brokers ?? 0 }} Broker(s) included</li>
                                    </ul>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_method{{ $plan->id }}" class="form-label">Payment Method</label>
                                    <select class="form-select" id="payment_method{{ $plan->id }}" name="payment_method" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="credit_card">Credit Card</option>
                                        {{-- <option value="debit_card">Debit Card</option>
                                        <option value="bank_transfer">Bank Transfer</option> --}}
                                    </select>
                                </div>

                                <div class="alert alert-info">
                                    <small>
                                        <i class="fas fa-info-circle"></i>
                                        Your billing cycle will start immediately and you'll be charged monthly.
                                    </small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Confirm Upgrade</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($currentSubscription && $currentSubscription->isOnTrial())
        <div class="row mt-5">
            <div class="col-12">
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-1">Trial Period Active</h6>
                            <p class="mb-0">
                                Your trial expires on {{ $currentSubscription->trial_ends_at->format('M d, Y') }}
                                ({{ $currentSubscription->trial_ends_at->diffForHumans() }}).
                                Upgrade now to continue using the system without interruption.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
