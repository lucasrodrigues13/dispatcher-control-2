@extends('layouts.app2')

@section('conteudo')
<div class="container-fluid">
    <div class="page-inner">

        {{-- Header --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">Subscription Management</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Admin</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Subscriptions</a></li>
            </ul>
        </div>

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card card-stats card-primary">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Total Users</p>
                                    <h4 class="card-title">{{ $stats['total_users'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card card-stats card-success">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Active</p>
                                    <h4 class="card-title">{{ $stats['active_subscriptions'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card card-stats card-warning">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Trial</p>
                                    <h4 class="card-title">{{ $stats['trial_subscriptions'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card card-stats card-danger">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-ban"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Blocked</p>
                                    <h4 class="card-title">{{ $stats['blocked_subscriptions'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card card-stats card-secondary">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Expired</p>
                                    <h4 class="card-title">{{ $stats['expired_subscriptions'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card card-stats card-info">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Monthly Revenue</p>
                                    <h4 class="card-title">${{ number_format($stats['total_revenue_month'], 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters and Actions --}}
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title">Users & Subscriptions</h4>
                            <div class="card-tools">
                                <a href="{{ route('admin.subscriptions.export') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-download"></i> Export CSV
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Filters --}}
                        <form method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial</option>
                                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="plan" class="form-select">
                                        <option value="">All Plans</option>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}" {{ request('plan') == $plan->id ? 'selected' : '' }}>
                                                {{ $plan->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- Users Table --}}
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Plan</th>
                                        <th>Status</th>
                                        <th>Started</th>
                                        <th>Expires</th>
                                        <th>Usage</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        @php
                                            $userObj = (object) $user;
                                            $subscription = $userObj->subscription ?? null;
                                            $plan = $subscription['plan'] ?? null;
                                            $userType = $userObj->user_type ?? 'main';
                                            $level = $userObj->level ?? 0;
                                            $roleName = $userObj->role_name ?? 'User';
                                            $parentName = $userObj->parent_name ?? null;

                                            // Definir ícones e cores baseados no tipo de usuário
                                            $iconClass = match($userType) {
                                                'main' => 'fas fa-crown text-warning',
                                                'sub' => 'fas fa-user text-info',
                                                'standalone' => 'fas fa-user-circle text-muted',
                                                default => 'fas fa-user text-secondary'
                                            };

                                            $bgClass = match($userType) {
                                                'main' => 'bg-warning',
                                                'sub' => 'bg-info',
                                                'standalone' => 'bg-secondary',
                                                default => 'bg-primary'
                                            };
                                        @endphp
                                        <tr class="{{ $userType === 'sub' ? 'table-light' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center" style="margin-left: {{ $level * 20 }}px;">
                                                    @if($level > 0)
                                                        <div class="me-2">
                                                            <i class="fas fa-level-up-alt fa-rotate-90 text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-title rounded-circle {{ $bgClass }}">
                                                            <i class="{{ $iconClass }}"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center">
                                                            <strong>{{ $userObj->name }}</strong>
                                                            <span class="badge badge-sm ms-2 {{ $userType === 'main' ? 'bg-success' : ($userType === 'sub' ? 'bg-info' : 'bg-secondary') }}">
                                                                {{ $roleName }}
                                                            </span>
                                                        </div>
                                                        <small class="text-muted">{{ $userObj->email }}</small>
                                                        @if($parentName)
                                                            <br><small class="text-info"><i class="fas fa-arrow-up"></i> Under: {{ $parentName }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($subscription && $plan)
                                                    <span class="badge bg-info">{{ $plan['name'] }}</span><br>
                                                    <small class="text-muted">${{ $subscription['amount'] }}/month</small>
                                                @else
                                                    @if($userType === 'sub')
                                                        <span class="text-muted"><i class="fas fa-link"></i> Inherited</span>
                                                    @else
                                                        <span class="text-muted">No Plan</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if($subscription)
                                                    @php
                                                        $status = $subscription['status'];
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
                                                    @if($userType === 'sub')
                                                        <span class="badge bg-light text-dark"><i class="fas fa-link"></i> Sub-user</span>
                                                    @else
                                                        <span class="badge bg-secondary">No Subscription</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if($subscription && isset($subscription['started_at']))
                                                    {{ \Carbon\Carbon::parse($subscription['started_at'])->format('M d, Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($subscription && isset($subscription['expires_at']))
                                                    {{ \Carbon\Carbon::parse($subscription['expires_at'])->format('M d, Y') }}
                                                    @if(\Carbon\Carbon::parse($subscription['expires_at'])->diffInDays(now()) <= 7)
                                                        <br><small class="text-warning">Expires soon</small>
                                                    @endif
                                                @else
                                                    @if($userType === 'sub')
                                                        <span class="text-muted"><i class="fas fa-link"></i> Inherited</span>
                                                    @else
                                                        -
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($userObj->usage_tracking) && $userObj->usage_tracking)
                                                    <div class="progress mb-1" style="height: 6px;">
                                                        @php
                                                            $usage = (object) $userObj->usage_tracking;
                                                            $carriersUsed = $usage->carriers_count ?? 0;
                                                            $carriersLimit = $plan ? ($plan['carriers_limit'] ?? 0) : 0;
                                                            $carriersPercent = $carriersLimit > 0 ? min(($carriersUsed / $carriersLimit) * 100, 100) : 0;
                                                        @endphp
                                                        <div class="progress-bar bg-primary" style="width: {{ $carriersPercent }}%"></div>
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $carriersUsed }}/{{ $carriersLimit ?: '∞' }} carriers
                                                    </small>
                                                @else
                                                    @if($userType === 'sub')
                                                        <span class="text-muted"><i class="fas fa-share-alt"></i> Shared</span>
                                                    @else
                                                        <span class="text-muted">No usage data</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="{{ route('admin.subscriptions.show', $userObj->id) }}">View Details</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No users found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        {{ $users->appends(request()->except('page'))->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
@include('admin.subscriptions.modals.block-user')
{{-- @include('admin.subscriptions.modals.change-plan')
@include('admin.subscriptions.modals.delete-user') --}}

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Block user
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
                alert('Error blocking user: ' + xhr.responseJSON.error);
            }
        });
    });

    // Unblock user
    $('.unblock-btn').click(function() {
        const userId = $(this).data('user-id');

        if (confirm('Are you sure you want to unblock this user?')) {
            $.ajax({
                url: `/admin/subscriptions/${userId}/unblock`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error unblocking user: ' + xhr.responseJSON.error);
                }
            });
        }
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
        const extendsCurrent = $('#extendsCurrent').is(':checked');

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
