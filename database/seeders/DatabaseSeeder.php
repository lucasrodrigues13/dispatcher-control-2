<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ordem de execução dos seeders:
        // 1. Plans (valores fixos de planos)
        // 2. Roles (valores fixos de roles)
        // 3. Permissions (valores fixos de permissions)
        // 4. AdminUser (usuários admin - depende de Roles e Permissions)

        $this->call([
            PlansSeeder::class,
            RolesSeeder::class,
            PermissionsSeeder::class,
            AdminUserSeeder::class,
            DispatcherOwnerSeeder::class, // Dispatchers owners (simula registro pelo site)
        ]);
    }
}
