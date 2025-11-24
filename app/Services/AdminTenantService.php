<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Session;

class AdminTenantService
{
    const SESSION_KEY = 'admin_viewing_tenant_id';
    const SESSION_KEY_ALL = 'admin_viewing_all';

    /**
     * Define qual tenant o admin está visualizando
     * Se $tenantId for null ou 'all', visualiza todos os dados
     */
    public function setViewingTenant(?int $tenantId = null): void
    {
        if ($tenantId === null || $tenantId === 'all') {
            Session::put(self::SESSION_KEY_ALL, true);
            Session::forget(self::SESSION_KEY);
        } else {
            Session::put(self::SESSION_KEY, $tenantId);
            Session::forget(self::SESSION_KEY_ALL);
        }
    }

    /**
     * Retorna o ID do tenant que o admin está visualizando
     * Retorna null se estiver visualizando todos
     */
    public function getViewingTenantId(): ?int
    {
        if (Session::get(self::SESSION_KEY_ALL, false)) {
            return null; // Visualizando todos
        }

        return Session::get(self::SESSION_KEY);
    }

    /**
     * Verifica se o admin está visualizando todos os tenants
     */
    public function isViewingAll(): bool
    {
        return Session::get(self::SESSION_KEY_ALL, false) || !Session::has(self::SESSION_KEY);
    }

    /**
     * Retorna o usuário do tenant que está sendo visualizado
     */
    public function getViewingTenantUser(): ?User
    {
        $tenantId = $this->getViewingTenantId();
        
        if ($tenantId === null) {
            return null;
        }

        return User::find($tenantId);
    }

    /**
     * Limpa a visualização (volta para visualizar todos)
     */
    public function clearViewingTenant(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::forget(self::SESSION_KEY_ALL);
    }
}

