<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Load;

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
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to populate kanban_status for existing loads...');
        
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
            $status = $this->determineLoadStatus($load);
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
    
    /**
     * Determine load status based on field values
     * Priority order: paid > billed > delivered > picked_up > assigned > new
     */
    private function determineLoadStatus($load)
    {
        // PAID: if has paid_amount or payment_status is 'paid'
        if (($load->paid_amount && $load->paid_amount > 0) || 
            (strtolower($load->payment_status ?? '') === 'paid')) {
            return 'paid';
        }

        // BILLED: if has invoice_date or invoice_number
        if ($load->invoice_date || $load->invoice_number) {
            return 'billed';
        }

        // DELIVERED: if has actual_delivery_date or scheduled_delivery_date
        if ($load->actual_delivery_date || $load->scheduled_delivery_date) {
            return 'delivered';
        }

        // PICKED UP: if has actual_pickup_date
        if ($load->actual_pickup_date) {
            return 'picked_up';
        }

        // ASSIGNED: if has scheduled_pickup_date
        if ($load->scheduled_pickup_date) {
            return 'assigned';
        }

        // NEW: default
        return 'new';
    }
}
