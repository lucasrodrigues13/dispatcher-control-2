<?php

namespace App\Imports;

// use App\Models\Load;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;


// namespace App\Imports;

use App\Models\Load;
// use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LoadsImportOld implements ToModel, WithHeadingRow
{
    protected $carrierId;
    protected $dispatcherId;
    protected $employeeId;

    public function __construct($carrierId, $dispatcherId, $employeeId = null)
    {
        $this->carrierId = $carrierId;
        $this->dispatcherId = $dispatcherId;
        $this->employeeId = $employeeId;
    }

    public function model(array $row)
    {
        $parsedLoadId = $row['load_id'] ?? null;

        $data = [
            'internal_load_id'        => $row['internal_load_id'] ?? null,
            'creation_date'           => $this->formatDate($row['creation_date'] ?? null),
            'dispatcher'              => $row['dispatcher'] ?? null,
            'trip'                    => $row['trip'] ?? null,
            'year_make_model'         => $row['year_make_model'] ?? null,
            'vin'                     => $row['vin'] ?? null,
            'lot_number'              => $row['lot_number'] ?? null,
            'has_terminal'            => $this->parseInt($row['has_terminal'] ?? null, 0),
            'dispatched_to_carrier'   => $row['dispatched_to_carrier'] ?? null,
            'pickup_name'             => $row['pickup_name'] ?? null,
            'pickup_address'          => $row['pickup_address'] ?? null,
            'pickup_city'             => $row['pickup_city'] ?? null,
            'pickup_state'            => $row['pickup_state'] ?? null,
            'pickup_zip'              => $row['pickup_zip'] ?? null,
            'scheduled_pickup_date'   => $this->formatDate($row['scheduled_pickup_date'] ?? null),
            'pickup_phone'            => $row['pickup_phone'] ?? null,
            'pickup_mobile'           => $row['pickup_mobile'] ?? null,
            'actual_pickup_date'      => $this->formatDate($row['actual_pickup_date'] ?? null),
            'buyer_number'            => $this->parseInt($row['buyer_number'] ?? null, null),
            'pickup_notes'            => $row['pickup_notes'] ?? null,
            'delivery_name'           => $row['delivery_name'] ?? null,
            'delivery_address'        => $row['delivery_address'] ?? null,
            'delivery_city'           => $row['delivery_city'] ?? null,
            'delivery_state'          => $row['delivery_state'] ?? null,
            'delivery_zip'            => $row['delivery_zip'] ?? null,
            'scheduled_delivery_date' => $this->formatDate($row['scheduled_delivery_date'] ?? null),
            'actual_delivery_date'    => $this->formatDate($row['actual_delivery_date'] ?? null),
            'delivery_phone'          => $row['delivery_phone'] ?? null,
            'delivery_mobile'         => $row['delivery_mobile'] ?? null,
            'delivery_notes'          => $row['delivery_notes'] ?? null,
            'shipper_name'            => $row['shipper_name'] ?? null,
            'shipper_phone'           => $row['shipper_phone'] ?? null,
            'price'                   => $this->parseFloat($row['price'] ?? null, null),
            'expenses'                => $this->parseFloat($row['expenses'] ?? null, null),
            'broker_fee'              => $this->parseFloat($row['broker_fee'] ?? null, null),
            'driver_pay'              => $this->parseFloat($row['driver_pay'] ?? null, null),
            'payment_method'          => $row['payment_method'] ?? null,
            'paid_amount'             => $this->parseFloat($row['paid_amount'] ?? null, null),
            'paid_method'             => $row['paid_method'] ?? null,
            'reference_number'        => $row['reference_number'] ?? null,
            'receipt_date'            => $this->formatDate($row['receipt_date'] ?? null),
            'payment_terms'           => $row['payment_terms'] ?? null,
            'payment_notes'           => $row['payment_notes'] ?? null,
            'payment_status'          => $row['payment_status'] ?? null,
            'invoice_number'          => $row['invoice_number'] ?? null,
            'invoice_notes'           => $row['invoice_notes'] ?? null,
            'invoice_date'            => $this->formatDate($row['invoice_date'] ?? null),
            'driver'                  => $row['driver'] ?? null,

            // IDs recebidos do construtor
            'carrier_id'              => $this->carrierId,
            'dispatcher_id'           => $this->dispatcherId,
            'employee_id'             => $this->employeeId,
        ];

        $load = Load::firstOrNew(['load_id' => $parsedLoadId]);
        $load->fill($data);
        $load->save();

        return $load;
    }

    private function formatDate($date)
    {
        if (!$date || trim($date) === '') {
            return null;
        }

        // Tenta MM/DD/YYYY
        $timestamp = \DateTime::createFromFormat('m/d/Y', $date);
        if ($timestamp) {
            return $timestamp->format('Y-m-d');
        }

        // Tenta DD/MM/YYYY
        $timestamp = \DateTime::createFromFormat('d/m/Y', $date);
        return $timestamp ? $timestamp->format('Y-m-d') : null;
    }

    private function parseInt($value, $default = 0)
    {
        if ($value === null || trim($value) === '') {
            return $default;
        }
        return is_numeric($value) ? (int) $value : $default;
    }

    private function parseFloat($value, $default = null)
    {
        if ($value === null || trim($value) === '') {
            return $default;
        }
        return is_numeric($value) ? (float) $value : $default;
    }
}
