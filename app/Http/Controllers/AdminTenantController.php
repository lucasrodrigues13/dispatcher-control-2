<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdminTenantService;
use App\Models\User;

class AdminTenantController extends Controller
{
    protected $adminTenantService;

    public function __construct(AdminTenantService $adminTenantService)
    {
        $this->adminTenantService = $adminTenantService;
    }

    /**
     * Alterna o tenant que o admin está visualizando
     */
    public function switchTenant(Request $request)
    {
        $user = auth()->user();

        // Apenas admins podem usar esta funcionalidade
        if (!$user->isAdmin()) {
            return redirect()->back()
                ->with('error', 'Acesso negado. Apenas administradores podem alternar tenants.');
        }

        $tenantId = $request->input('tenant_id');

        // Se for 'all' ou null, visualizar todos
        if ($tenantId === 'all' || $tenantId === null) {
            $this->adminTenantService->setViewingTenant(null);
            return redirect()->back()
                ->with('success', 'Visualizando todos os tenants.');
        }

        // Validar que o tenant existe e é realmente um owner
        $tenant = User::find($tenantId);
        if (!$tenant || !$tenant->is_owner || $tenant->is_admin) {
            return redirect()->back()
                ->with('error', 'Tenant inválido.');
        }

        $this->adminTenantService->setViewingTenant($tenantId);

        return redirect()->back()
            ->with('success', "Visualizando tenant: {$tenant->name}");
    }
}

