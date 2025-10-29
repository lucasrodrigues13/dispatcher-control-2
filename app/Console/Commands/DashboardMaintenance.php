<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardService;

class DashboardMaintenance extends Command
{
    protected $signature = 'dashboard:maintenance {--clear-cache}';
    protected $description = 'Perform dashboard maintenance tasks';

    public function handle(DashboardService $dashboardService)
    {
        $this->info('Starting dashboard maintenance...');

        if ($this->option('clear-cache')) {
            cache()->tags(['dashboard'])->flush();
            $this->info('Dashboard cache cleared.');
        }

        // Update statistics
        $this->info('Updating dashboard statistics...');

        // Perform maintenance tasks
        $this->call('queue:work', ['--once' => true]);

        $this->info('Dashboard maintenance completed!');
    }
}
