<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAgreements
{
    /**
     * Handle an incoming request.
     *
     * Only Owners, Subowners and Admins can access Agreements (Deals and Commissions)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user has permission to access agreements
        if (!$user->is_owner && !$user->is_subowner && !$user->is_admin) {
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
