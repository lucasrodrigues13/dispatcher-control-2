@extends('layouts.app')

@section('conteudo')
<div class="container-fluid">
    <div class="page-inner">

        {{-- Header --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">User Details: {{ $user->name }}</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('admin.subscriptions.index') }}">Subscriptions</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item">User Details</li>
            </ul>
        </div>

        <div class="row">
            {{-- User Information --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fas fa-user me-2"></i>
                            User Information
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-4"><strong>Name:</strong></div>
                            <div class="col-8">{{ $user->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>Email:</strong></div>
                            <div class="col-8">{{ $user->email }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>Joined:</strong></div>
                            <div class="col-8">{{ $user->created_at->format('M d, Y') }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>Status:</strong></div>
                            <div class="col-8">
                                @if($user->subscription)
                                    @php
                                        $status = $user->subscription->status;
                                        $badgeClass = match($status) {
                                            'active' => 'bg-success',
                                            'trial' => 'bg-warning',
                                            'blocked' => 'bg-danger',
                                            'cancelled' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                @else
                                    <span class="badge bg-secondary">No Subscription</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fas fa-tools me-2"></i>
                            Quick Actions
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($user->subscription)
                                @if($user->subscription->status === 'blocked')
                                    <button class="btn btn-success unblock-btn" data-user-id="{{ $user->id }}">
                                        <i class="fas fa-unlock me-2"></i>
                                        Unblock Subscription
                                    </button>
                                @else
                                    <button class="btn btn-warning block-btn" data-user-id="{{ $user->id }}">
                                        <i class="fas fa-ban me-2"></i>
                                        Block Subscription
                                    </button>
                                @endif

                                <button class="btn btn-primary edit-plan-btn" data-user-id="{{ $user->id }}">
                                    <i class="fas fa-edit me-2"></i>
                                    Change Plan
                                </button>

                                <button class="btn btn-success extend-btn" data-user-id="{{ $user->id }}">
                                    <i class="fas fa-calendar-plus me-2"></i>
                                    Extend Subscription
                                </button>
                            @endif

                            <button class="btn btn-danger delete-user-btn" data-user-id="{{ $user->id }}">
                                <i class="fas fa-trash me-2"></i>
                                Delete User
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Subscription Details --}}
            <div class="col-md-6">
                @if($user->subscription)
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="fas fa-credit-card me-2"></i>
                                Current Subscription
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-4"><strong>Plan:</strong></div>
                                <div class="col-8">{{ $user->subscription->plan->name ?? 'Unknown' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4"><strong>Amount:</strong></div>
                                <div class="col-8">${{ $user->subscription->amount }}/month</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4"><strong>Started:</strong></div>
                                <div class="col-8">{{ $user->subscription->started_at?->format('M d, Y') ?? '-' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4"><strong>Expires:</strong></div>
                                <div class="col-8">
                                    {{ $user->subscription->expires_at?->format('M d, Y') ?? '-' }}
                                    @if($user->subscription->expires_at && $user->subscription->expires_at->diffInDays(now()) <= 7)
                                        <span class="badge bg-warning ms-2">Expires Soon</span>
                                    @endif
                                </div>
                            </div>
                            @if($user->subscription->trial_ends_at)
                                <div class="row mb-3">
                                    <div class="col-4"><strong>Trial Ends:</strong></div>
                                    <div class="col-8">{{ $user->subscription->trial_ends_at->format('M d, Y') }}</div>
                                </div>
                            @endif
                            @if($user->subscription->blocked_at)
                                <div class="row mb-3">
                                    <div class="col-4"><strong>Blocked Since:</strong></div>
                                    <div class="col-8 text-danger">{{ $user->subscription->blocked_at->format('M d, Y H:i') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                            <h5>No Active Subscription</h5>
                            <p class="text-muted">This user doesn't have an active subscription.</p>
                        </div>
                    </div>
                @endif


            </div>
        </div>

        {{-- Payment History --}}
        @if($user->subscription && $user->subscription->payments->count() > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="fas fa-history me-2"></i>
                                Recent Payment History
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Method</th>
                                            <th>Transaction ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->subscription->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                                <td>${{ $payment->amount }}</td>
                                                <td>
                                                    @php
                                                        $statusClass = match($payment->status) {
                                                            'paid' => 'bg-success',
                                                            'pending' => 'bg-warning',
                                                            'failed' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $statusClass }}">{{ ucfirst($payment->status) }}</span>
                                                </td>
                                                <td>{{ $payment->payment_method ?? '-' }}</td>
                                                <td><small>{{ $payment->transaction_id ?? '-' }}</small></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif


    </div>
</div>

{{-- Include Modals --}}
@include('admin.subscriptions.modals.block-user')

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');



    // Block subscription
    $('.block-btn').click(function() {
        const userId = $(this).data('user-id');
        $('#blockUserModal').modal('show');
        $('#confirmBlockBtn').data('user-id', userId);
    });

    $('#confirmBlockBtn').click(function() {
        const userId = $(this).data('user-id');
        const reason = $('#blockReason').val();

        $.ajax({
            url: `/admin/subscriptions/${userId}/block`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { reason },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error blocking subscription: ' + xhr.responseJSON.error);
            }
        });
    });

    // Unblock subscription
    $('.unblock-btn').click(function() {
        const userId = $(this).data('user-id');

        $.ajax({
            url: `/admin/subscriptions/${userId}/unblock`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error unblocking subscription: ' + xhr.responseJSON.error);
            }
        });
    });

    // Change plan
    $('.edit-plan-btn').click(function() {
        const userId = $(this).data('user-id');
        $('#changePlanModal').modal('show');
        $('#confirmChangePlanBtn').data('user-id', userId);
    });

    $('#confirmChangePlanBtn').click(function() {
        const userId = $(this).data('user-id');
        const planId = $('#newPlanSelect').val();
        const extendsCurrent = $('#extendsCurrent').is(':checked') ? 1 : 0;

        $.ajax({
            url: `/admin/subscriptions/${userId}/change-plan`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                plan_id: planId,
                extends_current: extendsCurrent
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error changing plan: ' + xhr.responseJSON.error);
            }
        });
    });

    // Extend subscription
    $('.extend-btn').click(function() {
        const userId = $(this).data('user-id');
        $('#extendSubscriptionModal').modal('show');
        $('#confirmExtendBtn').data('user-id', userId);
    });

    $('#confirmExtendBtn').click(function() {
        const userId = $(this).data('user-id');
        const period = $('#extendPeriod').val();
        const periodType = $('#extendPeriodType').val();

        $.ajax({
            url: `/admin/subscriptions/${userId}/extend`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                period: period,
                period_type: periodType
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error extending subscription: ' + xhr.responseJSON.error);
            }
        });
    });

    // Delete user
    $('.delete-user-btn').click(function() {
        const userId = $(this).data('user-id');
        $('#deleteUserModal').modal('show');
        $('#confirmDeleteBtn').data('user-id', userId);
    });

    $('#confirmDeleteBtn').click(function() {
        const userId = $(this).data('user-id');
        const reason = $('#deleteReason').val();

        $.ajax({
            url: `/admin/subscriptions/${userId}/delete`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { reason },
            success: function(response) {
                window.location.href = '/admin/subscription';
            },
            error: function(xhr) {
                alert('Error deleting user: ' + xhr.responseJSON.error);
            }
        });
    });
});
</script>

@endsection
