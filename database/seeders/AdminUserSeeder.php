<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolesUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar ou criar role Admin
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['description' => 'Administrador com acesso total ao sistema']
        );

        // Buscar todas as permissions
        $permissions = Permission::all();

        // Criar usuário admin 1
        $user1 = User::updateOrCreate(
            ['email' => 'alexandre.brito.engenharia@gmail.com'],
            [
                'name' => 'Alexandre Brito',
                'password' => Hash::make('dispatcher123'),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        // Criar usuário admin 2
        $user2 = User::updateOrCreate(
            ['email' => 'flucasrodrigues@hotmail.com'],
            [
                'name' => 'Fernando Lucas Rodrigues Simões',
                'password' => Hash::make('dispatcher123'),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        // Atribuir role Admin aos usuários
        $this->assignRole($user1, $adminRole);
        $this->assignRole($user2, $adminRole);

        // Atribuir todas as permissions ao role Admin
        if ($permissions->count() > 0) {
            // Usar sync para garantir que todas as permissions sejam atribuídas
            // sync remove as que não estão na lista e adiciona as novas
            $adminRole->permissions()->sync($permissions->pluck('id'));
            
            $totalPermissions = $adminRole->permissions()->count();
            $this->command->info("✅ Todas as permissions atribuídas ao role Admin! ({$totalPermissions} permissions)");
        } else {
            $this->command->warn("⚠️  Nenhuma permission encontrada. Execute PermissionsSeeder primeiro!");
        }

        $this->command->info('Usuários admin criados com sucesso!');
        $this->command->info('Email 1: alexandre.brito.engenharia@gmail.com');
        $this->command->info('Email 2: flucasrodrigues@hotmail.com');
        $this->command->info('Senha: dispatcher123');
    }

    /**
     * Atribuir role a um usuário
     */
    private function assignRole(User $user, Role $role): void
    {
        // Verificar se o usuário já tem essa role
        if (!$user->roles()->where('roles.id', $role->id)->exists()) {
            // Usar o relacionamento do Eloquent (mais elegante)
            $user->roles()->attach($role->id);
            $this->command->info("Role '{$role->name}' atribuída ao usuário '{$user->email}'");
        } else {
            $this->command->info("Usuário '{$user->email}' já possui a role '{$role->name}'");
        }
    }
}

