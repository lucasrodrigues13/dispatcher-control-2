@props(['user'])

@php
    $isActive = $user->is_active ?? true;
@endphp

@if($isActive)
    <span class="badge bg-success">Active</span>
@else
    <span class="badge bg-danger">Inactive</span>
@endif

