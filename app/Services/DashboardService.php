<?

namespace App\Services;

use Carbon\Carbon;
use App\Models\Load;
use App\Models\TimeLineCharge;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Employeer;
use App\Models\Carrier;

class DashboardService
{
    /**
     * Get cached dashboard data to improve performance
     */
    public function getCachedDashboardData($cacheKey, $callback, $minutes = 60)
    {
        return cache()->remember($cacheKey, now()->addMinutes($minutes), $callback);
    }

    /**
     * Calculate revenue growth percentage
     */
    public function calculateGrowthPercentage($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Get top performing metrics
     */
    public function getTopPerformers($period = 'last_30_days')
    {
        $startDate = $this->getPeriodStartDate($period);

        return [
            'top_customers' => $this->getTopCustomersByRevenue($startDate),
            'top_employees' => $this->getTopEmployeesByCommission($startDate),
            'top_carriers' => $this->getTopCarriersByLoads($startDate),
        ];
    }

    private function getPeriodStartDate($period)
    {
        switch ($period) {
            case 'last_week':
                return Carbon::now()->subWeek();
            case 'last_30_days':
                return Carbon::now()->subDays(30);
            case 'last_90_days':
                return Carbon::now()->subDays(90);
            default:
                return Carbon::now()->subMonth();
        }
    }

    private function getTopCustomersByRevenue($startDate)
    {
        return TimeLineCharge::where('status_payment', 'paid')
            ->where('date_end', '>=', $startDate)
            ->selectRaw('costumer, SUM(price) as total_revenue')
            ->groupBy('costumer')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();
    }

    private function getTopEmployeesByCommission($startDate)
    {
        return Load::where('created_at', '>=', $startDate)
            ->whereNotNull('employee_id')
            ->selectRaw('employee_id, SUM(broker_fee) as total_commission')
            ->groupBy('employee_id')
            ->orderBy('total_commission', 'desc')
            ->limit(5)
            ->with('employee')
            ->get();
    }

    private function getTopCarriersByLoads($startDate)
    {
        return Load::where('created_at', '>=', $startDate)
            ->selectRaw('carrier_id, COUNT(*) as total_loads')
            ->groupBy('carrier_id')
            ->orderBy('total_loads', 'desc')
            ->limit(5)
            ->with('carrier')
            ->get();
    }
}

