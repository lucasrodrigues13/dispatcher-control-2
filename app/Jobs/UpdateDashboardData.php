<?php


namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\DashboardService;

class UpdateDashboardData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $period;

    public function __construct($userId, $period = 'last_30_days')
    {
        $this->userId = $userId;
        $this->period = $period;
    }

    public function handle(DashboardService $dashboardService)
    {
        // Update cached dashboard data in background
        $cacheKey = "dashboard_data_user_{$this->userId}_{$this->period}";

        $data = [
            'revenue_stats' => $dashboardService->getRevenueStats($this->period),
            'load_averages' => $dashboardService->getLoadAverages($this->period),
            'top_performers' => $dashboardService->getTopPerformers($this->period),
            'updated_at' => now()
        ];

        cache()->put($cacheKey, $data, now()->addHours(6));
    }
}
