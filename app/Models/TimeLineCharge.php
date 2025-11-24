<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class TimeLineCharge extends Model
{
    use HasFactory;

    // Nome da tabela, caso o plural não corresponda
    protected $table = 'time_line_charges';

    /**
     * Aplicar TenantScope para filtrar automaticamente por tenant
     */
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Atributos que podem ser atribuídos em massa.
     */
    protected $fillable = [
        'invoice_id',
        'costumer',
        'price',
        'status_payment',
        'carrier_id',
        'dispatcher_id',
        'date_start',
        'date_end',
        'amount_type',
        'array_type_dates',
        'load_ids',
        'load_details',       // ⭐ NOVO CAMPO
        'due_date',
        'payment_terms',
        'invoice_notes',
    ];

    /**
     * Casts para tipos específicos
     */
    protected $casts = [
        'due_date' => 'date',
        'date_start' => 'date',
        'date_end' => 'date',
        'array_type_dates' => 'array',
        'load_ids' => 'array',
        'load_details' => 'array',  // ⭐ NOVO CAST
    ];

    /**
     * Relacionamento com o Carrier.
     */
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * Relacionamento com o Dispatcher.
     */
    public function dispatcher()
    {
        return $this->belongsTo(Dispatcher::class);
    }

    /**
     * Accessor para formatar payment_terms de forma legível
     */
    public function getPaymentTermsFormattedAttribute()
    {
        return match($this->payment_terms) {
            'net_15' => 'Net 15 days',
            'net_30' => 'Net 30 days',
            'net_45' => 'Net 45 days',
            'net_60' => 'Net 60 days',
            'due_on_receipt' => 'Due on Receipt',
            'custom' => 'Custom Terms',
            default => $this->payment_terms ?? 'Not specified'
        };
    }

    /**
     * Scope para invoices vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status_payment', '!=', 'paid');
    }

    /**
     * Scope para invoices que vencem em breve (próximos 7 dias)
     */
    public function scopeDueSoon($query)
    {
        return $query->whereBetween('due_date', [now(), now()->addDays(7)])
                    ->where('status_payment', '!=', 'paid');
    }

    /**
     * Verifica se a invoice está vencida
     */
    public function isOverdue()
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               $this->status_payment !== 'paid';
    }

    /**
     * Calcula dias até o vencimento
     */
    public function daysUntilDue()
    {
        if (!$this->due_date) {
            return null;
        }

        $days = now()->diffInDays($this->due_date, false);
        return $days;
    }

    // ⭐ MÉTODOS EXISTENTES MANTIDOS
    public function getLoadIdsArrayAttribute()
    {
        $loadIds = $this->load_ids;

        if (is_string($loadIds)) {
            return json_decode($loadIds, true) ?? [];
        }

        if (is_array($loadIds)) {
            return $loadIds;
        }

        return [];
    }

    public function getFiltersArrayAttribute()
    {
        $filters = $this->array_type_dates;

        if (is_string($filters)) {
            return json_decode($filters, true) ?? [];
        }

        if (is_array($filters)) {
            return $filters;
        }

        return [];
    }

    // ⭐ NOVOS MÉTODOS PARA HISTÓRICO
    /**
     * Método para obter loads com fallback para histórico
     */
    public function getLoadsWithHistory()
    {
        $loadIds = $this->getLoadIdsArrayAttribute();

        if (empty($loadIds)) {
            return collect();
        }

        // Primeiro, tenta buscar loads que ainda existem na tabela
        $existingLoads = \App\Models\Load::whereIn('load_id', $loadIds)->get();
        $existingLoadIds = $existingLoads->pluck('load_id')->toArray();

        // Identifica loads que foram deletados
        $deletedLoadIds = array_diff($loadIds, $existingLoadIds);

        // Se tem dados salvos no histórico, usa eles para loads deletados
        $historicalLoads = collect();
        if (!empty($deletedLoadIds) && !empty($this->load_details)) {
            $historicalData = $this->load_details;

            foreach ($deletedLoadIds as $deletedId) {
                $historicalLoad = collect($historicalData)->firstWhere('load_id', $deletedId);
                if ($historicalLoad) {
                    // Adiciona flag para identificar como histórico
                    $historicalLoad['is_historical'] = true;
                    $historicalLoad['deleted_at'] = $this->created_at; // aproximação
                    $historicalLoads->push((object) $historicalLoad);
                }
            }
        }

        // Marca loads existentes como não-históricos
        $existingLoads->each(function($load) {
            $load->is_historical = false;
        });

        // Combina loads existentes + históricos
        return $existingLoads->concat($historicalLoads);
    }

    /**
     * Salva snapshot dos loads no momento da criação da invoice
     */
    public function saveLoadSnapshot()
    {
        $loadIds = $this->getLoadIdsArrayAttribute();

        if (empty($loadIds)) {
            return;
        }

        $loads = \App\Models\Load::whereIn('load_id', $loadIds)
            ->select([
                'id',
                'load_id',
                'year_make_model',
                'dispatcher',
                'broker_fee',
                'driver_pay',
                'driver',
                'lot_number',
                'paid_amount',
                'paid_method',
                'payment_notes',
                'payment_status',
                'payment_terms',
                'payment_method',
                'invoiced_fee',
                'price',
                'carrier_id',
                'dispatcher_id',
                'vin',
                'pickup_city',
                'delivery_city',
                'scheduled_pickup_date',
                'actual_pickup_date',
                'scheduled_delivery_date',
                'actual_delivery_date',
                'created_at'
            ])
            ->get()
            ->toArray();

        $this->update(['load_details' => $loads]);
    }



    public function getTotalLoadsAmount()
    {
        $loadIds = $this->getLoadIdsArrayAttribute();

        if (empty($loadIds)) {
            return 0;
        }

        // Determina qual campo usar para o cálculo (padrão: price)
        $amountField = $this->amount_type ?? 'price';

        // Valida se o campo é válido
        if (!in_array($amountField, ['price', 'paid_amount'])) {
            $amountField = 'price'; // fallback
        }

        // Primeiro, tenta buscar loads que ainda existem
        $existingLoads = \App\Models\Load::whereIn('load_id', $loadIds)->get();
        $totalFromExisting = $existingLoads->sum(function($load) use ($amountField) {
            return (float) ($load->{$amountField} ?? 0);
        });

        // Se todos os loads ainda existem, retorna o total
        $existingLoadIds = $existingLoads->pluck('load_id')->toArray();
        $deletedLoadIds = array_diff($loadIds, $existingLoadIds);

        if (empty($deletedLoadIds)) {
            return $totalFromExisting;
        }

        // Para loads deletados, usa os dados do histórico se disponível
        $totalFromHistory = 0;
        if (!empty($this->load_details)) {
            $historicalData = $this->load_details;

            foreach ($deletedLoadIds as $deletedId) {
                $historicalLoad = collect($historicalData)->firstWhere('load_id', $deletedId);
                if ($historicalLoad && isset($historicalLoad[$amountField])) {
                    $totalFromHistory += (float) $historicalLoad[$amountField];
                }
            }
        }

        return $totalFromExisting + $totalFromHistory;
    }

    /**
     * Accessor para obter o total formatado
     */
    public function getTotalLoadsAmountFormattedAttribute()
    {
        return '$' . number_format($this->getTotalLoadsAmount(), 2);
    }

    /**
     * Scope para incluir o total calculado na consulta (otimização)
     */
    public function scopeWithLoadsTotal($query)
    {
        return $query->selectRaw('
            time_line_charges.*,
            CASE
                WHEN time_line_charges.amount_type = "paid_amount" THEN "paid_amount"
                ELSE "price"
            END as calc_amount_type
        ');
    }





}
