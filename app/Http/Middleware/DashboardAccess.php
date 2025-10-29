<?


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DashboardAccess
{
    // public function handle(Request $request, Closure $next)
    // {
    //     // Check if user has permission to access dashboard
    //     if (!auth()->user()->canAccessDashboard()) {
    //         abort(403, 'Access denied to dashboard');
    //     }

    //     return $next($request);
    // }
}
