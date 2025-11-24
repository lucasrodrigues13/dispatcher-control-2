<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Employee;
use App\Models\Scopes\TenantScope;

class Load extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'loads';

    /**
     * Aplicar TenantScope para filtrar automaticamente por tenant
     */
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    protected $fillable = [
        // IDs e identificadores
        'load_id',
        'internal_load_id',
        'carrier_id',
        'dispatcher_id',
        'employee_id',

        // Dados básicos
        'creation_date',
        'dispatcher',
        'trip',

        // Informações do veículo
        'year_make_model',
        'vin',
        'lot_number',
        'has_terminal',
        'dispatched_to_carrier',

        // Pickup (Coleta)
        'pickup_name',
        'pickup_address',
        'pickup_city',
        'pickup_state',
        'pickup_zip',
        'scheduled_pickup_date',
        'pickup_phone',
        'pickup_mobile',
        'actual_pickup_date',
        'buyer_number',
        'pickup_notes',

        // Delivery (Entrega)
        'delivery_name',
        'delivery_address',
        'delivery_city',
        'delivery_state',
        'delivery_zip',
        'scheduled_delivery_date',
        'actual_delivery_date',
        'delivery_phone',
        'delivery_mobile',
        'delivery_notes',

        // Shipper (Remetente)
        'shipper_name',
        'shipper_phone',

        // Valores financeiros
        'price',
        'expenses',
        'broker_fee',
        'driver_pay',
        'invoiced_fee',

        // Informações de pagamento
        'payment_method',
        'paid_amount',
        'paid_method',
        'reference_number',
        'receipt_date',
        'payment_terms',
        'payment_notes',
        'payment_status',

        // Invoice (Fatura)
        'invoice_number',
        'invoice_notes',
        'invoice_date',

        // Driver e status
        'driver',
        'status_move',
    ];

    /**
     * Casts para tipos específicos
     */
    protected $casts = [
        'creation_date' => 'date',
        'scheduled_pickup_date' => 'date',
        'actual_pickup_date' => 'date',
        'scheduled_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'receipt_date' => 'date',
        'invoice_date' => 'date',
        'price' => 'decimal:2',
        'expenses' => 'decimal:2',
        'broker_fee' => 'decimal:2',
        'driver_pay' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'has_terminal' => 'boolean',
    ];

    /**
     * Valores padrão
     */
    protected $attributes = [
        'status_move' => 'no_moved',
    ];

    /**
     * Relacionamento com Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relacionamento com Carrier
     */
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * Relacionamento com Dispatcher
     */
    public function dispatcher()
    {
        return $this->belongsTo(Dispatcher::class);
    }
}
