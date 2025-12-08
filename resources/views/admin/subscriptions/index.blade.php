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
                                            $parentName = $userObj->parent_name ?? null;
                                            $parentId = $userObj->parent_id ?? null;
                                            $subUsersCount = $userObj->sub_users_count ?? 0;
                                            
                                            // Verificar se é admin
                                            $isAdmin = $userObj->is_admin ?? false;
                                            
                                            // Gerar iniciais do nome
                                            $nameParts = explode(' ', $userObj->name);
                                            $initials = '';
                                            if (count($nameParts) >= 2) {
                                                $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
                                            } else {
                                                $initials = strtoupper(substr($userObj->name, 0, 2));
                                            }
                                            
                                            // Gerar cor de fundo baseada no nome (consistente para o mesmo usuário)
                                            $colors = ['#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#e67e22'];
                                            $colorIndex = ord(strtoupper($userObj->name[0])) % count($colors);
                                            $avatarColor = $colors[$colorIndex];
                                            
                                            // Foto do perfil (pode ser avatar, profile_photo, photo, etc)
                                            $profilePhoto = $userObj->avatar ?? $userObj->profile_photo ?? $userObj->photo ?? null;
                                        @endphp
                                        
                                        <tr class="user-row {{ $userType === 'sub' ? 'sub-user-row' : '' }}" 
                                            data-user-id="{{ $userObj->id }}"
                                            data-user-type="{{ $userType }}"
                                            data-parent-id="{{ $parentId }}"
                                            style="{{ $userType === 'sub' ? 'display: none;' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center" style="padding-left: {{ $userType === 'sub' ? '50px' : '0' }};">
                                                    {{-- Botão de expandir/colapsar (apenas para Owners com sub-users) --}}
                                                    @if($userType === 'main' && $subUsersCount > 0)
                                                        <button class="btn btn-sm btn-link p-0 toggle-sub-users me-2" 
                                                                data-owner-id="{{ $userObj->id }}"
                                                                style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; text-decoration: none; flex-shrink: 0;">
                                                            <i class="fas fa-plus-circle text-primary" style="font-size: 16px;"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    {{-- Foto do usuário ou iniciais --}}
                                                    <div class="user-avatar" style="width: 40px; height: 40px; flex-shrink: 0; margin-right: 12px;">
                                                        @if($profilePhoto)
                                                            <img src="{{ Storage::url($profilePhoto) }}" 
                                                                 alt="{{ $userObj->name }}" 
                                                                 class="rounded-circle"
                                                                 style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #dee2e6;"
                                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                            {{-- Fallback para iniciais caso a imagem não carregue --}}
                                                            <div class="rounded-circle d-none align-items-center justify-content-center text-white fw-bold" 
                                                                 style="width: 40px; height: 40px; background-color: {{ $avatarColor }}; font-size: 14px;">
                                                                {{ $initials }}
                                                            </div>
                                                        @else
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" 
                                                                 style="width: 40px; height: 40px; background-color: {{ $avatarColor }}; font-size: 14px;">
                                                                {{ $initials }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <div style="flex-grow: 1; min-width: 0;">
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <strong style="white-space: nowrap;">{{ $userObj->name }}</strong>
                                                            
                                                            {{-- Badge de ADMIN --}}
                                                            @if($isAdmin)
                                                                <span class="badge bg-danger">
                                                                    <i class="fas fa-shield-alt me-1"></i>ADMIN
                                                                </span>
                                                            {{-- Badge de OWNER --}}
                                                            @elseif($userType === 'main')
                                                                <span class="badge bg-warning text-dark">
                                                                    <i class="fas fa-crown me-1"></i>OWNER
                                                                </span>
                                                                @if($subUsersCount > 0)
                                                                    <span class="badge bg-primary">
                                                                        {{ $subUsersCount }} sub-user{{ $subUsersCount > 1 ? 's' : '' }}
                                                                    </span>
                                                                @endif
                                                            {{-- Badge de SUB-USER --}}
                                                            @elseif($userType === 'sub')
                                                                <span class="badge bg-info">
                                                                    <i class="fas fa-user me-1"></i>SUB-USER
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <small class="text-muted">{{ $userObj->email }}</small>
                                                        @if($parentName)
                                                            <br><small class="text-muted">
                                                                <i class="fas fa-link me-1"></i>Owner: <strong>{{ $parentName }}</strong>
                                                            </small>
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
                                                @if($isAdmin)
                                                    <span class="badge bg-dark">System Access</span>
                                                @elseif($subscription)
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
                                                @elseif($userType === 'sub')
                                                    {{-- Sub-users mostram seu próprio is_active --}}
                                                    @php
                                                        $isActive = $userObj->is_active ?? true;
                                                    @endphp
                                                    @if($isActive)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($subscription && isset($subscription['started_at']))
                                                    {{ \Carbon\Carbon::parse($subscription['started_at'])->format('M d, Y') }}
                                                @elseif($userType === 'sub')
                                                    {{-- Mostrar data de criação do sub-user --}}
                                                    {{ \Carbon\Carbon::parse($userObj->created_at)->format('M d, Y') }}
                                                    <br><small class="text-muted"><i class="fas fa-user-plus"></i> Added</small>
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
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="{{ route('admin.subscriptions.show', $userObj->id) }}">
                                                            <i class="fas fa-eye me-2"></i>View Details
                                                        </a></li>
                                                        
                                                        @if($userType === 'sub')
                                                            <li><hr class="dropdown-divider"></li>
                                                            @if($userObj->is_active ?? true)
                                                                <li>
                                                                    <form action="{{ route('admin.subscriptions.toggle-user-status', $userObj->id) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button type="submit" class="dropdown-item text-danger">
                                                                            <i class="fas fa-ban me-2"></i>Deactivate User
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            @else
                                                                <li>
                                                                    <form action="{{ route('admin.subscriptions.toggle-user-status', $userObj->id) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button type="submit" class="dropdown-item text-success">
                                                                            <i class="fas fa-check-circle me-2"></i>Activate User
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            @endif
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No users found</td>
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

{{-- Custom Styles --}}
<style>
    .toggle-sub-users {
        transition: all 0.2s ease;
        border: none;
    }
    .toggle-sub-users:hover i {
        transform: scale(1.15);
    }
    .toggle-sub-users:focus {
        box-shadow: none;
        outline: none;
    }
    .sub-user-row {
        background-color: #f8f9fa !important;
    }
    .sub-user-row:hover {
        background-color: #e9ecef !important;
    }
    .user-row {
        vertical-align: middle;
    }
    .user-row td {
        padding: 12px 8px;
        vertical-align: middle;
    }
    .user-avatar {
        position: relative;
    }
</style>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Toggle sub-users visibility
    $('.toggle-sub-users').click(function(e) {
        e.preventDefault();
        const ownerId = $(this).data('owner-id');
        const icon = $(this).find('i');
        const subUsers = $(`.sub-user-row[data-parent-id="${ownerId}"]`);
        
        // Toggle visibility
        subUsers.toggle();
        
        // Toggle icon between + and -
        if (icon.hasClass('fa-plus-circle')) {
            icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
        } else {
            icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
        }
    });

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
