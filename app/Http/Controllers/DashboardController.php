<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carrier;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Load;
use App\Models\Plan;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $total_carriers = Carrier::count();
        $total_drivers = Driver::count();
        $total_employes = Employee::count();
        $total_loads = Load::count();

        $carriers = Carrier::with('user')->get();

        $user         = auth()->user();
        $resourceType = 'carrier';
        $subscription = $user->subscription;
        $plans        = Plan::where('active', true)
                            ->where('is_trial', false)
                            ->get();

        $billingService = app(\App\Services\BillingService::class);
        $usageStats = $billingService->getUsageStats($user);
        $usageCheck = $billingService->checkUsageLimits($user, 'carrier');

        return view("dashboard", compact(
            "total_carriers",
            "total_drivers",
            "total_employes",
            "total_loads",
            "carriers",
            "subscription",
            "plans",
            "usageStats",
            "usageCheck",
        ));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
