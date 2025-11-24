<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Se não houver usuário autenticado, não aplicar scope
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // ⭐ NOVO: Se for Admin master, verificar se está visualizando um tenant específico
        if ($user->isAdmin()) {
            $adminTenantService = app(\App\Services\AdminTenantService::class);
            
            // Se estiver visualizando todos (ou não selecionou nenhum), não aplicar filtro
            if ($adminTenantService->isViewingAll()) {
                return;
            }
            
            // Se estiver visualizando um tenant específico, aplicar filtro para aquele tenant
            $viewingTenantId = $adminTenantService->getViewingTenantId();
            if ($viewingTenantId) {
                $this->applyTenantFilter($builder, $model, $viewingTenantId);
            }
            
            return;
        }

        // Para usuários normais, aplicar filtro normalmente
        $ownerId = $user->getOwnerId();

        if (!$ownerId) {
            // Se não tem owner_id, não aplicar filtro (pode ser owner sem dados ainda)
            return;
        }

        // Aplicar filtro baseado no tipo de modelo
        $this->applyTenantFilter($builder, $model, $ownerId);
    }

    /**
     * Aplica filtro de tenant baseado no tipo de modelo
     */
    protected function applyTenantFilter(Builder $builder, Model $model, int $ownerId): void
    {
        $modelClass = get_class($model);

        // Models que têm owner_id direto
        if (in_array($modelClass, [
            \App\Models\User::class,
        ])) {
            $builder->where(function ($query) use ($ownerId) {
                // Incluir usuários do tenant (owner_id = $ownerId) OU o próprio owner (id = $ownerId)
                $query->where(function ($q) use ($ownerId) {
                    $q->where('owner_id', $ownerId)
                      ->orWhere('id', $ownerId);
                })
                // ⭐ CORRIGIDO: Excluir Admins masters (is_admin = true)
                // Isso garante que dispatchers owners não vejam os dois usuários Admin do seed
                ->where('is_admin', false);
            });
            return;
        }

        // Dispatcher: filtra diretamente pelo owner_id
        if ($modelClass === \App\Models\Dispatcher::class) {
            $builder->where('owner_id', $ownerId);
            return;
        }

        // Driver: filtra através de carrier (não tem user_id direto)
        if ($modelClass === \App\Models\Driver::class) {
            $builder->whereHas('carrier', function ($query) use ($ownerId) {
                // Carrier tem dispatcher_id, então filtra diretamente pelo dispatcher do owner
                $query->whereHas('dispatcher', function ($q) use ($ownerId) {
                    $q->where('owner_id', $ownerId);
                });
            });
            return;
        }

        // Models que têm dispatcher_id e precisam filtrar pelo dispatcher do owner
        if (method_exists($model, 'dispatcher')) {
            $builder->whereHas('dispatcher', function ($query) use ($ownerId) {
                $query->where('owner_id', $ownerId);
            });
            return;
        }

        // Models que têm owner_id direto (se adicionado no futuro)
        if (in_array('owner_id', $model->getFillable())) {
            $builder->where('owner_id', $ownerId);
            return;
        }

        // Models que têm user_id e precisam filtrar pelo user do owner
        // Mas só se realmente têm user_id na tabela (não apenas método user())
        if (method_exists($model, 'user') && in_array('user_id', $model->getFillable())) {
            $builder->whereHas('user', function ($query) use ($ownerId) {
                $query->where('owner_id', $ownerId)
                      ->orWhere('id', $ownerId);
            });
            return;
        }
    }

    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(Builder $builder): void
    {
        // Adicionar método withoutTenantScope() para queries que precisam ignorar o scope
        $scope = $this;
        $builder->macro('withoutTenantScope', function (Builder $builder) use ($scope) {
            return $builder->withoutGlobalScope($scope);
        });
    }
}

