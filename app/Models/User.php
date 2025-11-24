<?php

namespace App\Models;

use App\Services\DashboardService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
        'email_verified_at',
        'owner_id',
        'is_owner',
        'is_subadmin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_owner' => 'boolean',
        'is_subadmin' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_users');
    }

    // Relacionamentos de billing
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function usageTracking()
    {
        return $this->hasMany(UsageTracking::class);
    }

    // Relacionamentos de entidades
    public function carriers()
    {
        return $this->hasMany(Carrier::class);
    }

    public function employees()
    {
        // Employees são relacionados através do dispatcher
        return $this->hasManyThrough(
            Employee::class,
            Dispatcher::class,
            'user_id',        // FK em dispatchers → user
            'dispatcher_id',  // FK em employees → dispatcher
            'id',             // PK em users
            'id'              // PK em dispatchers
        );
    }

    public function drivers()
    {
        // Drivers são relacionados através dos carriers
        return $this->hasManyThrough(
            Driver::class,
            Carrier::class,
            'user_id',        // FK em carriers → user
            'carrier_id',     // FK em drivers → carrier
            'id',             // PK em users
            'id'              // PK em carriers
        );
    }

    public function dispatchers()
    {
        return $this->hasOne(Dispatcher::class);
    }

    /**
     * Relacionamento com o owner (dispatcher principal)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Usuários vinculados a este owner
     */
    public function tenants()
    {
        return $this->hasMany(User::class, 'owner_id');
    }

    /**
     * Métodos de verificação de tenant
     */
    public function isOwner(): bool
    {
        return $this->is_owner === true;
    }

    public function isSubadmin(): bool
    {
        return $this->is_subadmin === true;
    }

    /**
     * Retorna o ID do tenant (owner_id se não for owner, ou id se for owner)
     */
    public function getTenantId(): ?int
    {
        if ($this->isOwner()) {
            return $this->id;
        }
        
        return $this->owner_id;
    }

    /**
     * Retorna o owner_id (sempre retorna o ID do owner, mesmo se for o próprio owner)
     */
    public function getOwnerId(): ?int
    {
        if ($this->isOwner()) {
            return $this->id;
        }
        
        return $this->owner_id;
    }

    /**
     * Verifica se pode realizar ação administrativa
     */
    public function canManageTenant(): bool
    {
        return $this->isOwner() || $this->isSubadmin();
    }

     public function loads()
    {
        return $this->hasManyThrough(
            Load::class,      // Modelo final
            Carrier::class,   // Modelo "atravessado"
            'user_id',        // FK em carriers → user
            'carrier_id',     // FK em loads → carrier
            'id',             // PK em users
            'id'              // PK em carriers
        );
    }

    // Métodos de verificação de roles
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function isCarrier(): bool
    {
        return $this->hasRole('Carrier');
    }

    public function isDispatcher(): bool
    {
        return $this->hasRole('Dispatcher');
    }

    // Métodos de verificação de assinatura
    public function hasActiveSubscription(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    public function isOnTrial(): bool
    {
        return $this->subscription && $this->subscription->isOnTrial();
    }

    public function hasUnlimitedAccess(): bool
    {
        // Carriers sempre têm acesso ilimitado
        if ($this->isCarrier()) {
            return true;
        }

        // Verificar se tem plano unlimited
        if ($this->subscription && $this->subscription->plan) {
            return $this->subscription->plan->slug === 'carrier-unlimited';
        }

        return false;
    }

    public function canAccessSystem(): bool
    {
        // Carriers sempre podem acessar
        if ($this->isCarrier()) {
            return true;
        }

        // Para outros usuários, verificar assinatura normal
        return $this->hasActiveSubscription() || $this->isOnTrial();
    }

    // Método para verificar se pode realizar uma ação específica
    public function canPerformAction(string $action): bool
    {
        // Carriers podem fazer tudo
        if ($this->isCarrier()) {
            return true;
        }

        // Para outros usuários, verificar limites do plano
        if (!$this->canAccessSystem()) {
            return false;
        }

        // Verificar limites específicos por ação
        $billingService = app(\App\Services\BillingService::class);
        // checkUsageLimits requer resourceType, usando 'load' como padrão
        $usageLimits = $billingService->checkUsageLimits($this, 'load');

        return $usageLimits['allowed'] ?? false;
    }

    // Método para obter informações de uso e limites
    public function getUsageInfo(): array
    {
        if ($this->isCarrier()) {
            return [
                'unlimited' => true,
                'message' => 'Acesso ilimitado como Carrier'
            ];
        }

        $billingService = app(\App\Services\BillingService::class);
        return $billingService->getUsageStats($this) ?? [];
    }

    public function canAccessDashboard(): bool
    {
        // Add your logic here
        return $this->hasRole('admin') || $this->hasRole('dispatcher') || $this->hasRole('manager');
    }

    public function getDashboardData()
    {
        $dashboardService = app(DashboardService::class);

        return $dashboardService->getCachedDashboardData(
            'dashboard_data_user_' . $this->id,
            function () use ($dashboardService) {
                return [
                    'user_loads_count' => $this->loads()->count(),
                    'user_revenue' => $this->getTotalRevenue(),
                    'recent_activities' => $this->getRecentActivities(),
                ];
            },
            30 // Cache for 30 minutes
        );
    }

    public function getTotalRevenue()
    {
        return TimeLineCharge::where('status_payment', 'paid')
            ->whereHas('carrier', function ($query) {
                $query->where('user_id', $this->id);
            })
            ->sum('price');
    }

    public function getRecentActivities($limit = 10)
    {
        return Load::where('carrier_id', $this->carriers()->first()?->id)
            ->latest()
            ->limit($limit)
            ->get();
    }



}
