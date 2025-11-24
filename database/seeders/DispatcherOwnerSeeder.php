<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Dispatcher;
use App\Models\RolesUsers;
use App\Services\BillingService;
use Illuminate\Support\Facades\DB;

class DispatcherOwnerSeeder extends Seeder
{
    /**
     * Seed dispatchers owners usando os usuários existentes do AdminUserSeeder
     * Este seeder garante que os dispatchers dos usuários admin sejam criados como owners
     */
    public function run(): void
    {
        // Buscar os dois usuários admin que já foram criados pelo AdminUserSeeder
        $users = User::whereIn('email', [
            'alexandre.brito.engenharia@gmail.com',
            'flucasrodrigues@hotmail.com'
        ])->get();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️  Nenhum usuário admin encontrado. Execute AdminUserSeeder primeiro!');
            return;
        }

        foreach ($users as $user) {
            $this->ensureDispatcherOwner($user);
        }

        $this->command->info('✅ Dispatchers owners verificados/criados para ' . $users->count() . ' usuários admin!');
    }

    /**
     * Garante que o usuário tenha um dispatcher owner vinculado
     */
    private function ensureDispatcherOwner(User $user): void
    {
        DB::beginTransaction();

        try {
            // Verificar se já existe dispatcher para este usuário
            $existingDispatcher = Dispatcher::where('user_id', $user->id)->first();

            if ($existingDispatcher) {
                // Atualizar dispatcher existente para garantir que está como owner
                $existingDispatcher->update([
                    'owner_id' => $user->id,
                    'is_owner' => true,
                ]);

                // Garantir que o usuário está marcado como owner
                if (!$user->is_owner || $user->owner_id !== null) {
                    $user->update([
                        'owner_id' => null,
                        'is_owner' => true,
                    ]);
                }

                // Garantir que tem role Dispatcher
                $this->ensureDispatcherRole($user);

                DB::commit();
                $this->command->info("✅ Dispatcher owner atualizado para: {$user->name} ({$user->email})");
                return;
            }

            // Criar dispatcher owner se não existir
            $dispatcher = Dispatcher::create([
                'user_id' => $user->id,
                'owner_id' => $user->id, // Owner aponta para si mesmo
                'is_owner' => true, // Marca como dispatcher owner
                'type' => 'Individual',
                'company_name' => $user->name,
                'ssn_itin' => null,
                'ein_tax_id' => null,
                'address' => null,
                'city' => null,
                'state' => null,
                'zip_code' => null,
                'country' => null,
                'notes' => 'Dispatcher owner criado via seeder',
                'phone' => null,
                'departament' => null,
            ]);

            // Garantir que o usuário está marcado como owner
            if (!$user->is_owner || $user->owner_id !== null) {
                $user->update([
                    'owner_id' => null,
                    'is_owner' => true,
                ]);
            }

            // Garantir que tem role Dispatcher
            $this->ensureDispatcherRole($user);

            // Criar subscription de trial/freemium se não existir
            if (!$user->subscription) {
                $billingService = app(BillingService::class);
                $billingService->createTrialSubscription($user);
            }

            DB::commit();
            $this->command->info("✅ Dispatcher owner criado para: {$user->name} ({$user->email})");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Erro ao criar dispatcher owner para {$user->email}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Garante que o usuário tenha a role Dispatcher
     */
    private function ensureDispatcherRole(User $user): void
    {
        $role = DB::table('roles')->where('name', 'Dispatcher')->first();
        
        if (!$role) {
            $this->command->warn("⚠️  Role 'Dispatcher' não encontrada. Execute RolesSeeder primeiro!");
            return;
        }

        // Verificar se já tem a role
        $hasRole = DB::table('roles_users')
            ->where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->exists();

        if (!$hasRole) {
            RolesUsers::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
            $this->command->info("✅ Role 'Dispatcher' atribuída a {$user->name}");
        }
    }
}

