<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
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


        try {
            $permissions = Permission::with('roles')->get();

            foreach ($permissions as $permission) {
                Gate::define($permission->name, function(User $user) use ($permission) {
                    foreach ($user->roles as $role) {
                        // Verifica se a role tem a permissão e se pertence à empresa do usuário
                        if ($role->permissions->contains('name', $permission->name)) {
                            return true;
                        }
                    }
                    return false;
                });
            }
        } catch (Exception $e) {
            // Handle exception or log error if necessary
        }

        //
    }
}
