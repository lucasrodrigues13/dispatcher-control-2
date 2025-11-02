<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Permission;
use App\Models\User;
use Exception;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Carregar permissions apenas se a tabela existir e tiver dados
        try {
            // Verificar se a tabela existe antes de tentar carregar
            if (Schema::hasTable('permissions')) {
                $permissions = Permission::with('roles')->get();

                foreach ($permissions as $permission) {
                    Gate::define($permission->name, function(User $user) use ($permission) {
                        // Verificar se o usuário está autenticado
                        if (!$user) {
                            return false;
                        }

                        // Carregar roles do usuário se não estiverem carregadas
                        if (!$user->relationLoaded('roles')) {
                            $user->load('roles');
                        }

                        foreach ($user->roles as $role) {
                            // Verificar se a role tem a permissão
                            if ($role->permissions->contains('name', $permission->name)) {
                                return true;
                            }
                        }
                        return false;
                    });
                }
            }
        } catch (\Exception $e) {
            // Log do erro mas não quebra a aplicação
            Log::warning('Erro ao carregar permissions no AuthServiceProvider: ' . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
