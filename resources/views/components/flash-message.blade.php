@props(['type' => 'info'])

@php
    $config = [
        'success' => [
            'class' => 'alert-success',
            'icon' => 'fa-check-circle',
            'border' => 'border-success',
            'bg' => 'bg-light',
        ],
        'error' => [
            'class' => 'alert-danger',
            'icon' => 'fa-exclamation-circle',
            'border' => 'border-danger',
            'bg' => 'bg-light',
        ],
        'warning' => [
            'class' => 'alert-warning',
            'icon' => 'fa-exclamation-triangle',
            'border' => 'border-warning',
            'bg' => 'bg-light',
        ],
        'info' => [
            'class' => 'alert-info',
            'icon' => 'fa-info-circle',
            'border' => 'border-info',
            'bg' => 'bg-light',
        ],
    ];
    
    $style = $config[$type] ?? $config['info'];
@endphp

<div class="alert {{ $style['class'] }} alert-dismissible fade show mb-4 {{ $style['border'] }} border-start border-4 shadow-sm" role="alert" style="background-color: #fff;">
    <div class="d-flex align-items-start">
        <div class="flex-shrink-0 me-3 mt-1">
            <i class="fas {{ $style['icon'] }} fa-lg"></i>
        </div>
        <div class="flex-grow-1">
            {{ $slot }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>

