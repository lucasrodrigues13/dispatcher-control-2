<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\UsageTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionManagementController extends Controller
{
    /**
     * Lista todos os usuários com suas subscrições em formato hierárquico
     */
    public function index(Request $request)
    {
        $query = User::with([
            'subscription.plan', 
            'roles',
            'usageTracking' => function($q) {
                $q->where('year', now()->year)
                  ->where('month', now()->month);
            },
            'carriers',
            'employees',
            'drivers',
            'dispatchers'
        ]);

        // Filtros
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereHas('subscription', function($q) {
                    $q->where('status', 'active');
                });
            } elseif ($request->status === 'trial') {
                $query->whereHas('subscription', function($q) {
                    $q->where('status', 'trial');
                });
            } elseif ($request->status === 'blocked') {
                $query->whereHas('subscription', function($q) {
                    $q->where('status', 'blocked');
                });
            } elseif ($request->status === 'expired') {
                $query->whereHas('subscription', function($q) {
                    $q->where('expires_at', '<', now())
                      ->where('status', '!=', 'active');
                });
            }
        }

        if ($request->filled('plan')) {
            $query->whereHas('subscription', function($q) use ($request) {
                $q->where('plan_id', $request->plan);
            });
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $allUsers = $query->get();
        
        // Organizar usuários hierarquicamente
        $hierarchicalUsers = $this->organizeUsersHierarchically($allUsers);
        
        // Paginação manual dos usuários organizados
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedUsers = array_slice($hierarchicalUsers, $offset, $perPage);
        
        $users = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedUsers,
            count($hierarchicalUsers),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        $plans = Plan::where('active', true)->get();

        // Estatísticas
        $stats = $this->getSubscriptionStats();

        return view('admin.subscriptions.index', compact('users', 'plans', 'stats'));
    }

    /**
     * Mostra detalhes de um usuário específico
     */
    public function show($userId)
    {
        $user = User::with([
            'subscription.plan',
            'subscriptions.plan',
            'subscription.payments' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(10);
            },
            'usageTracking' => function($q) {
                $q->orderBy('year', 'desc')->orderBy('month', 'desc')->limit(12);
            }
        ])->findOrFail($userId);

        $plans = Plan::where('active', true)->get();

        return view('admin.subscriptions.show', compact('user', 'plans'));
    }

    /**
     * Organiza usuários hierarquicamente baseado em roles e relações
     */
    private function organizeUsersHierarchically($users)
    {
        $organized = [];
        $processedUsers = [];
        
        // Primeiro, processar usuários principais (Dispatchers com assinatura)
        foreach ($users as $user) {
            if (in_array($user->id, $processedUsers)) continue;
            
            if ($user->hasRole('Dispatcher') && $user->subscription) {
                $userArray = $user->toArray();
                $userArray['user_type'] = 'main';
                $userArray['role_name'] = 'Dispatcher (Main Account)';
                $userArray['level'] = 0;
                $organized[] = $userArray;
                $processedUsers[] = $user->id;
                
                // Adicionar carriers relacionados a este dispatcher
                $relatedCarriers = $users->filter(function($u) use ($user) {
                    return $u->hasRole('Carrier') && 
                           $u->carriers->where('dispatcher_id', $user->dispatchers->first()->id ?? null)->count() > 0;
                });
                
                foreach ($relatedCarriers as $carrier) {
                    if (in_array($carrier->id, $processedUsers)) continue;
                    
                    $carrierArray = $carrier->toArray();
                    $carrierArray['user_type'] = 'sub';
                    $carrierArray['role_name'] = 'Carrier';
                    $carrierArray['level'] = 1;
                    $carrierArray['parent_name'] = $user->name;
                    $organized[] = $carrierArray;
                    $processedUsers[] = $carrier->id;
                    
                    // Adicionar employees e drivers relacionados ao carrier
                    $this->addSubUsers($carrier, $users, $organized, $processedUsers, 2);
                }
            }
        }
        
        // Processar Carriers independentes (com assinatura própria)
        foreach ($users as $user) {
            if (in_array($user->id, $processedUsers)) continue;
            
            if ($user->hasRole('Carrier') && $user->subscription) {
                $userArray = $user->toArray();
                $userArray['user_type'] = 'main';
                $userArray['role_name'] = 'Carrier (Main Account)';
                $userArray['level'] = 0;
                $organized[] = $userArray;
                $processedUsers[] = $user->id;
                
                // Adicionar sub-usuários
                $this->addSubUsers($user, $users, $organized, $processedUsers, 1);
            }
        }
        
        // Processar usuários restantes sem assinatura
        foreach ($users as $user) {
            if (in_array($user->id, $processedUsers)) continue;
            
            $userArray = $user->toArray();
            $userArray['user_type'] = 'standalone';
            $userArray['level'] = 0;
            
            if ($user->hasRole('Dispatcher')) {
                $userArray['role_name'] = 'Dispatcher (No Subscription)';
            } elseif ($user->hasRole('Carrier')) {
                $userArray['role_name'] = 'Carrier (No Subscription)';
            } else {
                $userArray['role_name'] = 'User (No Role)';
            }
            
            $organized[] = $userArray;
            $processedUsers[] = $user->id;
        }
        
        return $organized;
    }
    
    /**
     * Adiciona sub-usuários (employees, drivers) relacionados a um usuário principal
     */
    private function addSubUsers($mainUser, $allUsers, &$organized, &$processedUsers, $level)
    {
        // Adicionar employees
        foreach ($mainUser->employees as $employee) {
            $employeeUser = $allUsers->find($employee->user_id);
            if ($employeeUser && !in_array($employeeUser->id, $processedUsers)) {
                $empArray = $employeeUser->toArray();
                $empArray['user_type'] = 'sub';
                $empArray['role_name'] = 'Employee';
                $empArray['level'] = $level;
                $empArray['parent_name'] = $mainUser->name;
                $organized[] = $empArray;
                $processedUsers[] = $employeeUser->id;
            }
        }
        
        // Adicionar drivers
        foreach ($mainUser->drivers as $driver) {
            $driverUser = $allUsers->find($driver->user_id);
            if ($driverUser && !in_array($driverUser->id, $processedUsers)) {
                $driverArray = $driverUser->toArray();
                $driverArray['user_type'] = 'sub';
                $driverArray['role_name'] = 'Driver';
                $driverArray['level'] = $level;
                $driverArray['parent_name'] = $mainUser->name;
                $organized[] = $driverArray;
                $processedUsers[] = $driverUser->id;
            }
        }
    }

    /**
     * Bloqueia uma subscrição
     */
    public function blockSubscription($userId, Request $request)
    {
        $user = User::findOrFail($userId);
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json(['error' => 'User has no active subscription'], 404);
        }

        $subscription->update([
            'status' => 'blocked',
            'blocked_at' => now(),
        ]);

        // Log da ação
        \Log::info('Subscription blocked by admin', [
            'admin_user_id' => auth()->id(),
            'target_user_id' => $userId,
            'subscription_id' => $subscription->id,
            'reason' => $request->input('reason', 'No reason provided')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription blocked successfully',
            'status' => 'blocked'
        ]);
    }

    /**
     * Desbloqueia uma subscrição
     */
    public function unblockSubscription($userId)
    {
        $user = User::findOrFail($userId);
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json(['error' => 'User has no subscription'], 404);
        }

        $newStatus = 'active';
        if ($subscription->trial_ends_at && now()->isBefore($subscription->trial_ends_at)) {
            $newStatus = 'trial';
        }

        $subscription->update([
            'status' => $newStatus,
            'blocked_at' => null,
        ]);

        // Log da ação
        \Log::info('Subscription unblocked by admin', [
            'admin_user_id' => auth()->id(),
            'target_user_id' => $userId,
            'subscription_id' => $subscription->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription unblocked successfully',
            'status' => $newStatus
        ]);
    }

    /**
     * Altera o plano de um usuário
     */
    public function changePlan($userId, Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'extends_current' => 'boolean',
        ]);

        $user = User::findOrFail($userId);
        $subscription = $user->subscription;
        $newPlan = Plan::findOrFail($request->plan_id);

        if (!$subscription) {
            return response()->json(['error' => 'User has no subscription'], 404);
        }

        // Calcular nova data de expiração
        $newExpiresAt = now()->addMonth();
        if ($request->extends_current && $subscription->expires_at > now()) {
            $newExpiresAt = Carbon::parse($subscription->expires_at)->addMonth();
        }

        $subscription->update([
            'plan_id' => $newPlan->id,
            'amount' => $newPlan->price,
            'expires_at' => $newExpiresAt,
            'status' => 'active'
        ]);

        // Log da ação
        \Log::info('Plan changed by admin', [
            'admin_user_id' => auth()->id(),
            'target_user_id' => $userId,
            'old_plan_id' => $subscription->getOriginal('plan_id'),
            'new_plan_id' => $newPlan->id,
            'extends_current' => $request->extends_current
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan changed successfully',
            'new_plan' => $newPlan->name,
            'expires_at' => $newExpiresAt->format('Y-m-d')
        ]);
    }

    /**
     * Estende a subscrição por um período
     */
    public function extendSubscription($userId, Request $request)
    {
        $request->validate([
            'period' => 'required|in:7,30,90,365',
            'period_type' => 'required|in:days,months'
        ]);

        $user = User::findOrFail($userId);
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json(['error' => 'User has no subscription'], 404);
        }

        $currentExpiry = $subscription->expires_at ?: now();

        if ($request->period_type === 'days') {
            $newExpiry = Carbon::parse($currentExpiry)->addDays($request->period);
        } else {
            $newExpiry = Carbon::parse($currentExpiry)->addMonths($request->period);
        }

        $subscription->update([
            'expires_at' => $newExpiry,
            'status' => 'active'
        ]);

        // Log da ação
        \Log::info('Subscription extended by admin', [
            'admin_user_id' => auth()->id(),
            'target_user_id' => $userId,
            'period' => $request->period,
            'period_type' => $request->period_type,
            'new_expiry' => $newExpiry
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription extended successfully',
            'new_expiry' => $newExpiry->format('Y-m-d H:i')
        ]);
    }

    /**
     * Cancela/Exclui um usuário
     */
    public function deleteUser($userId, Request $request)
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'You cannot delete your own account'], 403);
        }

        // Cancelar subscrição primeiro
        if ($user->subscription) {
            $user->subscription->update([
                'status' => 'cancelled',
                'blocked_at' => now()
            ]);
        }

        // Log antes de deletar
        \Log::info('User deleted by admin', [
            'admin_user_id' => auth()->id(),
            'deleted_user_id' => $userId,
            'deleted_user_email' => $user->email,
            'reason' => $request->input('reason', 'No reason provided')
        ]);

        // Deletar relacionamentos se necessário (ou usar cascade)
        $user->usageTracking()->delete();

        // Deletar usuário
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Obtém estatísticas das subscrições
     */
    private function getSubscriptionStats()
    {
        return [
            'total_users' => User::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'trial_subscriptions' => Subscription::where('status', 'trial')->count(),
            'blocked_subscriptions' => Subscription::where('status', 'blocked')->count(),
            'expired_subscriptions' => Subscription::where('expires_at', '<', now())
                                                  ->where('status', '!=', 'active')->count(),
            'total_revenue_month' => Subscription::where('status', 'active')
                                                ->sum('amount'),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)->count(),
        ];
    }

    /**
     * Exporta dados dos usuários para CSV
     */
    public function exportUsers(Request $request)
    {
        $users = User::with(['subscription.plan'])->get();

        $filename = 'users_subscriptions_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');

            // Headers do CSV
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Plan', 'Status', 'Started At',
                'Expires At', 'Amount', 'Created At'
            ]);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->subscription->plan->name ?? 'No Plan',
                    $user->subscription->status ?? 'No Subscription',
                    $user->subscription->started_at ?? '',
                    $user->subscription->expires_at ?? '',
                    $user->subscription->amount ?? 0,
                    $user->created_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
