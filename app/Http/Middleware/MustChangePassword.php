<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MustChangePassword
{
    public function handle(Request $request, Closure $next)
    {
        // Verificar se usuário está autenticado e sessão está disponível
        if (auth()->check() && $request->hasSession()) {
            $user = auth()->user();
            
            if ($user->must_change_password) {
                // Verificar se já tem warning para evitar múltiplos flashes
                if (!$request->session()->has('warning')) {
                    $request->session()->flash('warning', 'Your password is still the default one. Please change it as soon as possible for security reasons.');
                }
            }
        }

        return $next($request);
    }
}
