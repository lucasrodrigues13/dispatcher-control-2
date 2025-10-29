<?php   
namespace App\Traits;

use Carbon\Carbon;

trait AppliesFilters
{
    public function applyPeriod($query, $period, $start = null, $end = null)
    {
        switch ($period) {
            case 'last_week':
                $query->whereBetween('created_at', [Carbon::now()->subWeek(), Carbon::now()]);
                break;
            case 'past_15_days':
                $query->whereBetween('created_at', [Carbon::now()->subDays(15), Carbon::now()]);
                break;
            // ... outros cases ...
            case 'custom':
                $query->whereBetween('created_at', [Carbon::parse($start), Carbon::parse($end)]);
                break;
        }
        return $query;
    }

    public function applyEntity($query, $entityType, $entityId)
    {
        if ($entityType && $entityId) {
            $column = match($entityType) {
                'customer' => 'customer_id',
                'driver'   => 'driver_id',
                'employee' => 'employee_id',
                default     => null
            };
            if ($column) {
                $query->where($column, $entityId);
            }
        }
        return $query;
    }

    public function applyValueType($query, $valueType)
    {
        // Para cargas price/paid
        return $query;
    }
}
