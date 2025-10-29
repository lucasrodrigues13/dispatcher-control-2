<?php
// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o usuário está autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Verificar se é admin por role (se você estiver usando o sistema de roles)
        if ($user->roles()->where('name', 'admin')->exists()) {
            return $next($request);
        }

        // Verificar por campo is_admin (se você tiver esse campo)
        if (property_exists($user, 'is_admin') && $user->is_admin) {
            return $next($request);
        }

        // Verificar por email específico (método simples)
        $adminEmails = [
            'admin@example.com',
            'your-admin-email@gmail.com', // Adicione seu email aqui
        ];

        if (in_array($user->email, $adminEmails)) {
            return $next($request);
        }

        // Se não for admin, bloquear acesso
        abort(403, 'Unauthorized. Admin access required.');
    }
}
