<?php

namespace App\Http\Controllers\Traits;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

trait ToggleUserStatus
{
    /**
     * Toggle status de um usuário associado a um registro
     * 
     * @param int $recordId ID do registro (dispatcher, carrier, etc)
     * @param string $modelClass Classe do modelo (Dispatcher::class, Carrier::class, etc)
     * @param string $userRelation Nome do relacionamento com User no modelo
     * @return RedirectResponse
     */
    public function toggleStatus($recordId)
    {
        try {
            // Determinar o modelo baseado no controller
            $modelClass = $this->getModelClass();
            
            // Buscar o registro
            $record = $modelClass::findOrFail($recordId);
            
            // Buscar o usuário associado
            $user = $record->user;
            
            if (!$user) {
                return redirect()->back()->with('error', 'User not found for this record');
            }
            
            // Verificar se o usuário logado é o owner deste usuário
            $authUser = auth()->user();
            
            // Impedir que o owner desative a si mesmo
            if ($user->id === $authUser->id) {
                return redirect()->back()->with('error', 'You cannot deactivate yourself');
            }
            
            // Verificar se é owner tentando desativar outro owner ou admin
            if ($authUser->is_owner) {
                // Só pode desativar seus próprios sub-users
                if ($user->owner_id !== $authUser->id) {
                    return redirect()->back()->with('error', 'You can only manage your own sub-users');
                }
                
                // Não pode desativar outro owner ou admin
                if ($user->is_owner || $user->is_admin) {
                    return redirect()->back()->with('error', 'Cannot deactivate owner or admin users');
                }
            }
            
            // Se for admin, pode fazer tudo
            // Se for owner, já validou acima
            
            // Toggle do status
            $user->is_active = !$user->is_active;
            $user->save();
            
            $message = $user->is_active 
                ? 'User activated successfully' 
                : 'User deactivated successfully';
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error toggling user status: ' . $e->getMessage());
        }
    }
    
    /**
     * Determina a classe do modelo baseado no controller
     * 
     * @return string
     */
    protected function getModelClass(): string
    {
        $controllerClass = get_class($this);
        $controllerName = class_basename($controllerClass);
        
        // Remove "Controller" do nome
        $modelName = str_replace('Controller', '', $controllerName);
        
        return "App\\Models\\{$modelName}";
    }
}

