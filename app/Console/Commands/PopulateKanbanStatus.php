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
     * 
     * Regras de negócio:
     * 1. paid -> quando tem paid_amount ou payment_status indica pago
     * 2. billed -> quando tem invoice_number ou invoice_date
     * 3. delivered -> quando tem actual_delivery_date (apenas actual, não scheduled)
     * 4. picked_up -> quando tem actual_pickup_date
     * 5. assigned -> quando tem driver OU scheduled_pickup_date
     * 6. new -> padrão
     */
    private function determineLoadStatus($load)
    {
        // 1. PAID - Se tem valor pago ou status indica pago
        if (!empty($load->paid_amount) && $load->paid_amount > 0) {
            return 'paid';
        }
        
        if (!empty($load->payment_status)) {
            $paymentStatus = strtolower(trim($load->payment_status));
            $paidStatuses = ['paid', 'pago', 'completed', 'concluído', 'concluido', 'received', 'recebido'];
            foreach ($paidStatuses as $status) {
                if (strpos($paymentStatus, $status) !== false) {
                    return 'paid';
                }
            }
        }

        // 2. BILLED - Se tem invoice (fatura)
        if ($load->invoice_number || $load->invoice_date) {
            return 'billed';
        }

        // 3. DELIVERED - Se tem data de entrega REAL (apenas actual_delivery_date)
        if ($load->actual_delivery_date) {
            return 'delivered';
        }

        // 4. PICKED_UP - Se tem data de coleta REAL
        if ($load->actual_pickup_date) {
            return 'picked_up';
        }

        // 5. ASSIGNED - Se tem driver OU data de coleta agendada
        if ($load->driver || $load->scheduled_pickup_date) {
            return 'assigned';
        }

        // 6. NEW - Status padrão
        return 'new';
    }
}

