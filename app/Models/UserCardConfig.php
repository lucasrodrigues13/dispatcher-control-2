<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCardConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'config_type',
        'config_data'
    ];

    protected $casts = [
        'config_data' => 'array'
    ];

    /**
     * Relacionamento com User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obter configuração de campos do card para um usuário
     */
    public static function getCardFieldsConfig($userId)
    {
        $config = self::where('user_id', $userId)
            ->where('config_type', 'kanban_card_fields')
            ->first();

        if ($config) {
            return $config->config_data;
        }

        // Configuração padrão
        return [
            // Basic Information
            'load_id' => true,
            'internal_load_id' => false,
            'dispatcher' => true,
            'trip' => false,
            'creation_date' => false,
            'employee_id' => false,
            'status_move' => false,
            
            // Vehicle Information
            'year_make_model' => false,
            'vin' => false,
            'lot_number' => false,
            'has_terminal' => false,
            'dispatched_to_carrier' => false,
            
            // Address Information
            'pickup_address' => false,
            'pickup_city' => true,
            'pickup_state' => false,
            'pickup_zip' => false,
            'pickup_phone' => false,
            'pickup_mobile' => false,
            'pickup_notes' => false,
            'delivery_address' => false,
            'delivery_city' => true,
            'delivery_state' => false,
            'delivery_zip' => false,
            'delivery_phone' => false,
            'delivery_mobile' => false,
            'delivery_notes' => false,
            'shipper_name' => false,
            'shipper_phone' => false,
            
            // Date Information
            'scheduled_pickup_date' => true,
            'actual_pickup_date' => false,
            'scheduled_delivery_date' => false,
            'actual_delivery_date' => false,
            'receipt_date' => false,
            'invoice_date' => false,
            
            // Financial Information
            'price' => false,
            'expenses' => false,
            'broker_fee' => false,
            'driver_pay' => false,
            'payment_method' => false,
            'paid_amount' => false,
            'paid_method' => false,
            'reference_number' => false,
            'payment_terms' => false,
            'payment_notes' => false,
            'payment_status' => false,
            'invoice_number' => false,
            'invoice_notes' => false,
            'invoiced_fee' => false,
            
            // Additional Information
            'driver' => false,
            'carrier_id' => false,
            'dispatcher_id' => false,
            'buyer_number' => false,
        ];
    }

    /**
     * Salvar configuração de campos do card para um usuário
     */
    public static function saveCardFieldsConfig($userId, $config)
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'config_type' => 'kanban_card_fields'
            ],
            [
                'config_data' => $config
            ]
        );
    }
}
