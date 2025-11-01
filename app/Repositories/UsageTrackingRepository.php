<?php

namespace App\Repositories;

use App\Models\UsageTracking;
use App\Models\User;
use App\Models\Plan;

class UsageTrackingRepository
{
    /**
     * Retorna o uso atual do usuário
     */
    public function getCurrentUsage(User $user)
    {
        return UsageTracking::where('user_id', $user->id)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->where('week', now()->weekOfYear)
            ->first();
    }

    /**
     * Compara o uso atual com os limites do plano
     */
    public function checkLimits(User $user, string $resourceType)
    {
        $usage = $this->getCurrentUsage($user);
        $plan = $user->subscription && $user->subscription->plan ? $user->subscription->plan : null;

        if (!$plan) {
            return [
                'allowed' => false,
                'message' => 'User does not have an active subscription plan.',
                'suggest_upgrade' => true,
            ];
        }

        // Unlimited plan, always allowed
        if ($plan->slug === 'carrier-unlimited') {
            return ['allowed' => true];
        }

        $carriersUsed  = $usage ? $usage->carriers_count  : 0;
        $employeesUsed = $usage ? $usage->employees_count : 0;
        $driversUsed   = $usage ? $usage->drivers_count   : 0;

        $maxCarriers  = $plan->max_carriers;
        $maxEmployees = $plan->max_employees;
        $maxDrivers   = $plan->max_drivers;

        // Check only the resource being added
        if ($resourceType === 'carrier') {
            if ($maxCarriers === 0) {
                return [
                    'allowed' => true,
                    'suggest_upgrade' => true,
                    'message' => 'Your current plan does not allow adding carriers. Please consider upgrading.',
                ];
            }
            if ($carriersUsed >= $maxCarriers) {
                return [
                    'allowed' => true,
                    'suggest_upgrade' => true,
                    'message' => 'You have reached the carrier limit for your plan. Please upgrade to add more.',
                ];
            }
        }

        if ($resourceType === 'employee') {
            if ($maxEmployees === 0) {
                return [
                    'allowed' => true,
                    'suggest_upgrade' => true,
                    'message' => 'Your current plan does not allow adding employees. Please consider upgrading.',
                ];
            }
            if ($employeesUsed >= $maxEmployees) {
                return [
                    'allowed' => true,
                    'suggest_upgrade' => true,
                    'message' => 'You have reached the employee limit for your plan. Please upgrade to add more.',
                ];
            }
        }

        if ($resourceType === 'driver') {
            if ($maxDrivers === 0) {
                return [
                    'allowed' => true,
                    'suggest_upgrade' => true,
                    'message' => 'Your current plan does not allow adding drivers. Please consider upgrading.',
                ];
            }
            if ($driversUsed >= $maxDrivers) {
                return [
                    'allowed' => true,
                    'suggest_upgrade' => true,
                    'message' => 'You have reached the driver limit for your plan. Please upgrade to add more.',
                ];
            }
        }

        // Within the limits
        return ['allowed' => true];
    }

    /**
     * Incrementa o uso de um recurso
     */
    public function incrementUsage(User $user, string $resourceType, int $quantity = 1)
    {
        $usage = $this->getCurrentUsage($user);

        if (!$usage) {
            $usage = UsageTracking::create([
                'user_id' => $user->id,
                'year' => now()->year,
                'month' => now()->month,
                'week' => now()->weekOfYear,
                'loads_count' => 0,
                'carriers_count' => 0,
                'employees_count' => 0,
                'drivers_count' => 0,
            ]);
        }

        switch ($resourceType) {
            case 'carrier':
                $usage->carriers_count += $quantity;
                break;
            case 'employee':
                $usage->employees_count += $quantity;
                break;
            case 'driver':
                $usage->drivers_count += $quantity;
                break;
            case 'load':
                $usage->loads_count += $quantity;
                break;
        }

        $usage->save();
        return $usage;
    }
}
