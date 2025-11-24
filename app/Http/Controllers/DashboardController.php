<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $user = auth()->user();
        $adminTenantService = app(\App\Services\AdminTenantService::class);
        
        // ⭐ NOVO: Se for admin, verificar se está visualizando um tenant específico
        $viewingTenant = null;
        $isAdminViewingAll = false;
        
        if ($user->isAdmin()) {
            $viewingTenantId = $adminTenantService->getViewingTenantId();
            $isAdminViewingAll = $adminTenantService->isViewingAll();
            
            if ($viewingTenantId) {
                $viewingTenant = \App\Models\User::find($viewingTenantId);
                // Usar dados do tenant selecionado
                $user = $viewingTenant;
            }
        }

        // Contar recursos (já filtrados pelo TenantScope se necessário)
        $total_carriers = Carrier::count();
        $total_drivers = Driver::count();
        $total_employes = Employee::count();
        $total_loads = Load::count();

        $carriers = Carrier::with('user')->get();

        $resourceType = 'carrier';
        
        // ⭐ NOVO: Se for admin, criar um plano "Admin" virtual
        if (auth()->user()->isAdmin()) {
            if ($isAdminViewingAll) {
                // Criar plano Admin virtual quando visualizando todos
                $subscription = (object) [
                    'plan' => (object) [
                        'name' => 'Admin Plan',
                        'slug' => 'admin',
                        'description' => 'Full system access - Viewing all tenants',
                        'price' => 0,
                        'billing_cycle' => 'N/A',
                        'max_dispatchers' => 'Unlimited',
                        'max_employees' => 'Unlimited',
                        'max_carriers' => 'Unlimited',
                        'max_drivers' => 'Unlimited',
                        'max_brokers' => 'Unlimited',
                        'max_loads_per_month' => 'Unlimited',
                    ],
                ];
            } else {
                // Quando visualizando um tenant específico, usar subscription do tenant
                $subscription = $user->subscription;
            }
        } else {
            $subscription = $user->subscription;
        }
        
        $plans = Plan::where('active', true)
                    ->where('is_trial', false)
                    ->get();

        $billingService = app(\App\Services\BillingService::class);
        
        // Tratamento seguro para usageStats e usageCheck
        try {
            $usageStats = $billingService->getUsageStats($user);
            $usageCheck = $billingService->checkUsageLimits($user, 'carrier');
        } catch (\Exception $e) {
            // Log do erro mas não quebra a aplicação
            Log::warning('Erro ao obter usage stats no DashboardController: ' . $e->getMessage());
            $usageStats = null;
            $usageCheck = ['allowed' => true];
        }

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
            "viewingTenant",
            "isAdminViewingAll",
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
