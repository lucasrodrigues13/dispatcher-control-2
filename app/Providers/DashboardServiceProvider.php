<?php

// =============================================================================
// ADDITIONAL CONFIGURATIONS AND OPTIMIZATIONS
// =============================================================================

/**
 * 1. SERVICE PROVIDER (Optional) - Create a Dashboard Service Provider
 * File: app/Providers/DashboardServiceProvider.php
 */
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DashboardService;

class DashboardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DashboardService::class, function ($app) {
            return new DashboardService();
        });
    }

    public function boot()
    {
        //
    }
}
