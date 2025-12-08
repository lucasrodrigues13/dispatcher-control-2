@props(['user', 'route'])

@php
    $isActive = $user->is_active ?? true;
    $isOwner = $user->is_owner ?? false;
    $isCurrentUser = $user->id === auth()->id();
@endphp

@if(!$isOwner && !$isCurrentUser)
    <form action="{{ $route }}" method="POST" class="d-inline">
        @csrf
        @method('PATCH')
        @if($isActive)
            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to deactivate this user?')">
                <i class="fas fa-ban me-2"></i>Deactivate
            </button>
        @else
            <button type="submit" class="dropdown-item text-success" onclick="return confirm('Are you sure you want to activate this user?')">
                <i class="fas fa-check-circle me-2"></i>Activate
            </button>
        @endif
    </form>
@endif

