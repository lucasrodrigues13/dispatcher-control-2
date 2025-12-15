<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Map roles to permissions automatically
     * 
     * NOTA: Um usuário pode ter múltiplas roles (ex: Owner + Dispatcher).
     * Nesse caso, ele terá a UNIÃO de todas as permissões das roles.
     * Exemplo: Se Owner tem todas as permissões e Dispatcher tem algumas,
     * o usuário terá TODAS as permissões (Owner prevalece).
     */
    public function run(): void
    {
        $this->command->info('Starting role-permission mapping...');
        
        // 1. Criar roles adicionais se não existirem
        $this->createRoles();
        
        // 2. Mapear permissões para cada role
        $this->mapPermissions();
        
        $this->command->info('Role-permission mapping completed!');
        $this->command->info('💡 Lembre-se: Usuários podem ter múltiplas roles e terão a união de todas as permissões.');
    }
    
    /**
     * Criar todas as roles necessárias
     */
    private function createRoles()
    {
        $roles = [
            [
                'name' => 'Owner',
                'description' => 'Proprietário da conta com acesso total',
            ],
            [
                'name' => 'Subowner',
                'description' => 'Sub-proprietário com acesso administrativo limitado',
            ],
            [
                'name' => 'Admin',
                'description' => 'Administrador com acesso total ao sistema',
            ],
            [
                'name' => 'Dispatcher',
                'description' => 'Despachante que gerencia cargas e transportadoras',
            ],
            [
                'name' => 'Carrier',
                'description' => 'Transportadora que recebe e gerencia suas cargas',
            ],
            [
                'name' => 'Broker',
                'description' => 'Corretor que intermedia cargas',
            ],
            [
                'name' => 'Employee',
                'description' => 'Funcionário com acesso limitado',
            ],
            [
                'name' => 'Driver',
                'description' => 'Motorista com acesso muito limitado',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
        
        $this->command->info('Roles criadas/atualizadas!');
    }
    
    /**
     * Mapear permissões para roles
     */
    private function mapPermissions()
    {
        // Limpar mapeamentos existentes
        DB::table('permissions_roles')->truncate();
        
        // OWNER - Acesso Total
        $this->assignAllPermissionsToRole('Owner');
        
        // SUBOWNER - Acesso Total exceto gerenciamento de roles/permissions
        $this->assignPermissionsToRole('Subowner', [
            'pode_visualizar_dashboard',
            'pode_visualizar_usuario',
            'pode_visualizar_dispatchers',
            'pode_visualizar_employees',
            'pode_visualizar_carriers',
            'pode_visualizar_drivers',
            'pode_visualizar_brokers',
            'pode_visualizar_deals',
            'pode_visualizar_commissions',
            'pode_visualizar_loads',
            'pode_visualizar_invoices.create',
            'pode_visualizar_invoices.index',
            'pode_visualizar_charges_setups.index',
            'pode_visualizar_relatorio',
            // Criar
            'pode_registrar_dashboard',
            'pode_registrar_dispatchers',
            'pode_registrar_carriers',
            'pode_registrar_employees',
            'pode_registrar_drivers',
            'pode_registrar_brokers',
            'pode_registrar_deals',
            'pode_registrar_commissions',
            'pode_registrar_loads',
            'pode_registrar_invoices.create',
            'pode_registrar_invoices.index',
            'pode_registrar_charges_setups.index',
            // Editar
            'pode_editar_dashboard',
            'pode_editar_dispatchers',
            'pode_editar_employees',
            'pode_editar_carriers',
            'pode_editar_drivers',
            'pode_editar_brokers',
            'pode_editar_deals',
            'pode_editar_commissions',
            'pode_editar_loads',
            'pode_editar_invoices.create',
            'pode_editar_invoices.index',
            'pode_editar_charges_setups.index',
            // Deletar
            'pode_eliminar_dashboard',
            'pode_eliminar_dispatchers',
            'pode_eliminar_employees',
            'pode_eliminar_carriers',
            'pode_eliminar_drivers',
            'pode_eliminar_brokers',
            'pode_eliminar_deals',
            'pode_eliminar_commissions',
            'pode_eliminar_loads',
            'pode_eliminar_invoices.create',
            'pode_eliminar_invoices.index',
            'pode_eliminar_charges_setups.index',
        ]);
        
        // ADMIN - Igual ao Owner (acesso total)
        $this->assignAllPermissionsToRole('Admin');
        
        // DISPATCHER - Acesso a todas loads do tenant, pode gerenciar tudo exceto outros dispatchers
        $this->assignPermissionsToRole('Dispatcher', [
            'pode_visualizar_dashboard',
            'pode_visualizar_usuario', // Ver, mas não editar
            'pode_visualizar_employees',
            'pode_visualizar_carriers',
            'pode_visualizar_drivers',
            'pode_visualizar_brokers',
            'pode_visualizar_deals',
            'pode_visualizar_commissions',
            'pode_visualizar_loads',
            'pode_visualizar_invoices.create',
            'pode_visualizar_invoices.index',
            'pode_visualizar_charges_setups.index',
            'pode_visualizar_relatorio',
            // Criar
            'pode_registrar_employees',
            'pode_registrar_carriers',
            'pode_registrar_drivers',
            'pode_registrar_brokers',
            'pode_registrar_deals',
            'pode_registrar_commissions',
            'pode_registrar_loads',
            'pode_registrar_invoices.create',
            'pode_registrar_invoices.index',
            'pode_registrar_charges_setups.index',
            // Editar
            'pode_editar_employees',
            'pode_editar_carriers',
            'pode_editar_drivers',
            'pode_editar_brokers',
            'pode_editar_deals',
            'pode_editar_commissions',
            'pode_editar_loads',
            'pode_editar_invoices.create',
            'pode_editar_invoices.index',
            'pode_editar_charges_setups.index',
            // Deletar (limitado)
            'pode_eliminar_employees',
            'pode_eliminar_drivers',
            'pode_eliminar_deals',
            'pode_eliminar_commissions',
            'pode_eliminar_loads',
        ]);
        
        // CARRIER - Ver apenas suas loads
        $this->assignPermissionsToRole('Carrier', [
            'pode_visualizar_dashboard',
            'pode_visualizar_usuario', // Ver, mas não editar
            'pode_visualizar_drivers',
            'pode_visualizar_loads', // Filtrado por carrier_id
            'pode_visualizar_invoices.index',
            // Editar apenas suas próprias loads e drivers
            'pode_editar_drivers',
            'pode_editar_loads',
        ]);
        
        // BROKER - Ver apenas loads que ele intermedia
        $this->assignPermissionsToRole('Broker', [
            'pode_visualizar_dashboard',
            'pode_visualizar_usuario', // Ver, mas não editar
            'pode_visualizar_loads', // Filtrado por broker
            'pode_visualizar_deals',
            'pode_visualizar_commissions',
            // Criar deals
            'pode_registrar_deals',
            'pode_editar_deals',
        ]);
        
        // EMPLOYEE - Acesso limitado
        $this->assignPermissionsToRole('Employee', [
            'pode_visualizar_dashboard',
            'pode_visualizar_usuario', // Ver apenas
            'pode_visualizar_loads', // Filtrado por employee_id
            'pode_visualizar_commissions', // Suas próprias comissões
            // Editar apenas suas loads atribuídas
            'pode_editar_loads',
        ]);
        
        // DRIVER - Acesso muito limitado
        $this->assignPermissionsToRole('Driver', [
            'pode_visualizar_dashboard',
            'pode_visualizar_loads', // Apenas loads atribuídas a ele
        ]);
        
        $this->command->info('Permissões mapeadas para roles!');
    }
    
    /**
     * Atribuir todas as permissões a uma role
     */
    private function assignAllPermissionsToRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->command->error("Role '{$roleName}' não encontrada!");
            return;
        }
        
        $permissions = Permission::all();
        $mappings = [];
        
        foreach ($permissions as $permission) {
            $mappings[] = [
                'role_id' => $role->id,
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('permissions_roles')->insert($mappings);
        $this->command->info("Role '{$roleName}': " . count($permissions) . " permissões atribuídas");
    }
    
    /**
     * Atribuir permissões específicas a uma role
     */
    private function assignPermissionsToRole($roleName, array $permissionNames)
    {
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->command->error("Role '{$roleName}' não encontrada!");
            return;
        }
        
        $permissions = Permission::whereIn('name', $permissionNames)->get();
        $mappings = [];
        
        foreach ($permissions as $permission) {
            $mappings[] = [
                'role_id' => $role->id,
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (!empty($mappings)) {
            DB::table('permissions_roles')->insert($mappings);
        }
        
        $this->command->info("Role '{$roleName}': " . count($mappings) . " permissões atribuídas");
    }
}

