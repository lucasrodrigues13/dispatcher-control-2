<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Permissões de visualização
            ['name' => 'pode_visualizar_dashboard', 'description' => 'pode_visualizar_dashboard'],
            ['name' => 'pode_visualizar_despesas_diarias', 'description' => 'pode_visualizar_despesas_diarias'],
            ['name' => 'pode_visualizar_upload', 'description' => 'pode_visualizar_upload'],
            ['name' => 'pode_visualizar_despesa', 'description' => 'pode_visualizar_despesa'],
            ['name' => 'pode_visualizar_documento', 'description' => 'pode_visualizar_documento'],
            ['name' => 'pode_visualizar_faturamentos_diarios', 'description' => 'pode_visualizar_faturamentos_diarios'],
            ['name' => 'pode_visualizar_suporte', 'description' => 'pode_visualizar_suporte'],
            ['name' => 'pode_visualizar_relatorio', 'description' => 'pode_visualizar_relatorio'],
            ['name' => 'pode_visualizar_permissao', 'description' => 'pode_visualizar_permissao'],
            ['name' => 'pode_visualizar_usuario', 'description' => 'pode_visualizar_usuario'],
            ['name' => 'pode_visualizar_dispatchers', 'description' => 'pode_visualizar_dispatchers'],
            ['name' => 'pode_visualizar_employees', 'description' => 'pode_visualizar_employees'],
            ['name' => 'pode_visualizar_carriers', 'description' => 'pode_visualizar_carriers'],
            ['name' => 'pode_visualizar_drivers', 'description' => 'pode_visualizar_drivers'],
            ['name' => 'pode_visualizar_brokers', 'description' => 'pode_visualizar_brokers'],
            ['name' => 'pode_visualizar_deals', 'description' => 'pode_visualizar_deals'],
            ['name' => 'pode_visualizar_commissions', 'description' => 'pode_visualizar_commissions'],
            ['name' => 'pode_visualizar_loads', 'description' => 'pode_visualizar_loads'],
            ['name' => 'pode_visualizar_invoices.create', 'description' => 'pode_visualizar_invoices.create'],
            ['name' => 'pode_visualizar_invoices.index', 'description' => 'pode_visualizar_invoices.index'],
            ['name' => 'pode_visualizar_charges_setups.index', 'description' => 'pode_visualizar_charges_setups.index'],
            ['name' => 'pode_visualizar_permissions_roles', 'description' => 'pode_visualizar_permissions_roles'],
            ['name' => 'pode_visualizar_roles_users', 'description' => 'pode_visualizar_roles_users'],
            
            // Permissões de registro (criação)
            ['name' => 'pode_registrar_employees', 'description' => 'pode_registrar_employees'],
            ['name' => 'pode_registrar_dashboard', 'description' => 'pode_registrar_dashboard'],
            ['name' => 'pode_registrar_dispatchers', 'description' => 'pode_registrar_dispatchers'],
            ['name' => 'pode_registrar_carriers', 'description' => 'pode_registrar_carriers'],
            ['name' => 'pode_registrar_drivers', 'description' => 'pode_registrar_drivers'],
            ['name' => 'pode_registrar_brokers', 'description' => 'pode_registrar_brokers'],
            ['name' => 'pode_registrar_deals', 'description' => 'pode_registrar_deals'],
            ['name' => 'pode_registrar_commissions', 'description' => 'pode_registrar_commissions'],
            ['name' => 'pode_registrar_loads', 'description' => 'pode_registrar_loads'],
            ['name' => 'pode_registrar_invoices.create', 'description' => 'pode_registrar_invoices.create'],
            ['name' => 'pode_registrar_invoices.index', 'description' => 'pode_registrar_invoices.index'],
            ['name' => 'pode_registrar_charges_setups.index', 'description' => 'pode_registrar_charges_setups.index'],
            ['name' => 'pode_registrar_permissions_roles', 'description' => 'pode_registrar_permissions_roles'],
            ['name' => 'pode_registrar_roles_users', 'description' => 'pode_registrar_roles_users'],
            
            // Permissões de edição
            ['name' => 'pode_editar_dashboard', 'description' => 'pode_editar_dashboard'],
            ['name' => 'pode_editar_dispatchers', 'description' => 'pode_editar_dispatchers'],
            ['name' => 'pode_editar_employees', 'description' => 'pode_editar_employees'],
            ['name' => 'pode_editar_carriers', 'description' => 'pode_editar_carriers'],
            ['name' => 'pode_editar_drivers', 'description' => 'pode_editar_drivers'],
            ['name' => 'pode_editar_brokers', 'description' => 'pode_editar_brokers'],
            ['name' => 'pode_editar_deals', 'description' => 'pode_editar_deals'],
            ['name' => 'pode_editar_commissions', 'description' => 'pode_editar_commissions'],
            ['name' => 'pode_editar_loads', 'description' => 'pode_editar_loads'],
            ['name' => 'pode_editar_invoices.create', 'description' => 'pode_editar_invoices.create'],
            ['name' => 'pode_editar_invoices.index', 'description' => 'pode_editar_invoices.index'],
            ['name' => 'pode_editar_charges_setups.index', 'description' => 'pode_editar_charges_setups.index'],
            ['name' => 'pode_editar_permissions_roles', 'description' => 'pode_editar_permissions_roles'],
            ['name' => 'pode_editar_roles_users', 'description' => 'pode_editar_roles_users'],
            
            // Permissões de eliminação (deletar)
            ['name' => 'pode_eliminar_dashboard', 'description' => 'pode_eliminar_dashboard'],
            ['name' => 'pode_eliminar_dispatchers', 'description' => 'pode_eliminar_dispatchers'],
            ['name' => 'pode_eliminar_employees', 'description' => 'pode_eliminar_employees'],
            ['name' => 'pode_eliminar_carriers', 'description' => 'pode_eliminar_carriers'],
            ['name' => 'pode_eliminar_drivers', 'description' => 'pode_eliminar_drivers'],
            ['name' => 'pode_eliminar_brokers', 'description' => 'pode_eliminar_brokers'],
            ['name' => 'pode_eliminar_deals', 'description' => 'pode_eliminar_deals'],
            ['name' => 'pode_eliminar_commissions', 'description' => 'pode_eliminar_commissions'],
            ['name' => 'pode_eliminar_loads', 'description' => 'pode_eliminar_loads'],
            ['name' => 'pode_eliminar_invoices.create', 'description' => 'pode_eliminar_invoices.create'],
            ['name' => 'pode_eliminar_invoices.index', 'description' => 'pode_eliminar_invoices.index'],
            ['name' => 'pode_eliminar_charges_setups.index', 'description' => 'pode_eliminar_charges_setups.index'],
            ['name' => 'pode_eliminar_permissions_roles', 'description' => 'pode_eliminar_permissions_roles'],
            ['name' => 'pode_eliminar_roles_users', 'description' => 'pode_eliminar_roles_users'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }

        $this->command->info('Permissions criadas com sucesso!');
        $this->command->info('Total: ' . count($permissions) . ' permissions');
    }
}

