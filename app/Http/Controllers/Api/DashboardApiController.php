<?


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Load;
use App\Models\TimeLineCharge;
use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardApiController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function getRealtimeData(Request $request)
    {
        $data = [
            'total_loads_today' => Load::whereDate('created_at', today())->count(),
            'revenue_today' => TimeLineCharge::where('status_payment', 'paid')
                                           ->whereDate('date_end', today())
                                           ->sum('price'),
            'pending_invoices' => TimeLineCharge::where('status_payment', '!=', 'paid')->count(),
            'overdue_count' => TimeLineCharge::where('due_date', '<', now())
                                           ->where('status_payment', '!=', 'paid')
                                           ->count(),
        ];

        return response()->json($data);
    }

    public function getChartDataAjax(Request $request)
    {
        $chartType = $request->get('type');
        $period = $request->get('period', 'last_30_days');

        switch ($chartType) {
            case 'revenue_trend':
                return response()->json($this->getRevenueTrendData($period));
            case 'load_distribution':
                return response()->json($this->getLoadDistributionData($period));
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    private function getRevenueTrendData($period)
    {
        // Implementation similar to controller
        return ['labels' => [], 'data' => []];
    }

    private function getLoadDistributionData($period)
    {
        // Implementation for load distribution chart
        return ['labels' => [], 'data' => []];
    }
}
