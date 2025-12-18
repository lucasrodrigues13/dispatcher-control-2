<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Load;
use App\Services\LoadService;

class PopulateKanbanStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loads:populate-kanban-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate kanban_status field for existing loads based on their data';

    /**
     * @var LoadService
     */
    protected $loadService;

    /**
     * Create a new command instance.
     */
    public function __construct(LoadService $loadService)
    {
        parent::__construct();
        $this->loadService = $loadService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to populate kanban_status for existing loads...');
        
        $loadService = app(LoadService::class);
        $loads = Load::all();
        $bar = $this->output->createProgressBar(count($loads));
        
        $stats = [
            'new' => 0,
            'assigned' => 0,
            'picked_up' => 0,
            'delivered' => 0,
            'billed' => 0,
            'paid' => 0
        ];
        
        foreach ($loads as $load) {
            $status = $this->loadService->determineLoadStatus($load);
            $load->kanban_status = $status;
            $load->save();
            
            $stats[$status]++;
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info('Kanban status populated successfully!');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['NEW', $stats['new']],
                ['ASSIGNED', $stats['assigned']],
                ['PICKED UP', $stats['picked_up']],
                ['DELIVERED', $stats['delivered']],
                ['BILLED', $stats['billed']],
                ['PAID', $stats['paid']],
                ['TOTAL', array_sum($stats)]
            ]
        );
        
        return 0;
    }
}