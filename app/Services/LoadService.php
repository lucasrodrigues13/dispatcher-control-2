<?php

namespace App\Services;

use App\Models\Load;
use Illuminate\Http\Request;

class LoadService
{
    /**
     * Build query with filters for loads
     * 
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildFilteredQuery(Request $request)
    {
        $query = Load::query();

        // ⭐ FILTER BY ROLE: Carriers and Drivers only see their own data
        $user = auth()->user();
        if (!$user->canViewAllTenantData()) {
            // If carrier, filter only their loads
            if ($user->isCarrier()) {
                $carrierId = $user->getCarrierId();
                if ($carrierId) {
                    $query->where('carrier_id', $carrierId);
                }
            }
            // If driver, filter only loads where they are the driver
            // (assuming there is a driver_email or driver_id field)
            // For now, leave only for carriers
        }

        // General search in all fields
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                // Main fields
                $q->where('load_id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('internal_load_id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('dispatcher', 'like', '%' . $searchTerm . '%')
                  ->orWhere('vin', 'like', '%' . $searchTerm . '%')
                  // Search by carrier name (through relationship)
                  ->orWhereHas('carrier', function($carrierQuery) use ($searchTerm) {
                      $carrierQuery->where('company_name', 'like', '%' . $searchTerm . '%')
                                   ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                                       $userQuery->where('name', 'like', '%' . $searchTerm . '%');
                                   });
                  })
                  ->orWhere('lot_number', 'like', '%' . $searchTerm . '%')
                  ->orWhere('year_make_model', 'like', '%' . $searchTerm . '%')
                  ->orWhere('trip', 'like', '%' . $searchTerm . '%')
                  // Pickup fields
                  ->orWhere('pickup_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pickup_address', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pickup_city', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pickup_state', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pickup_zip', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pickup_phone', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pickup_mobile', 'like', '%' . $searchTerm . '%')
                  ->orWhere('pickup_notes', 'like', '%' . $searchTerm . '%')
                  // Delivery fields
                  ->orWhere('delivery_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('delivery_address', 'like', '%' . $searchTerm . '%')
                  ->orWhere('delivery_city', 'like', '%' . $searchTerm . '%')
                  ->orWhere('delivery_state', 'like', '%' . $searchTerm . '%')
                  ->orWhere('delivery_zip', 'like', '%' . $searchTerm . '%')
                  ->orWhere('delivery_phone', 'like', '%' . $searchTerm . '%')
                  ->orWhere('delivery_mobile', 'like', '%' . $searchTerm . '%')
                  ->orWhere('delivery_notes', 'like', '%' . $searchTerm . '%')
                  // Financial fields
                  ->orWhere('price', 'like', '%' . $searchTerm . '%')
                  ->orWhere('expenses', 'like', '%' . $searchTerm . '%')
                  ->orWhere('broker_fee', 'like', '%' . $searchTerm . '%')
                  ->orWhere('driver_pay', 'like', '%' . $searchTerm . '%')
                  ->orWhere('paid_amount', 'like', '%' . $searchTerm . '%')
                  ->orWhere('payment_method', 'like', '%' . $searchTerm . '%')
                  ->orWhere('paid_method', 'like', '%' . $searchTerm . '%')
                  ->orWhere('reference_number', 'like', '%' . $searchTerm . '%')
                  // Other fields
                  ->orWhere('shipper_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('shipper_phone', 'like', '%' . $searchTerm . '%')
                  ->orWhere('driver', 'like', '%' . $searchTerm . '%')
                  ->orWhere('dispatched_to_carrier', 'like', '%' . $searchTerm . '%')
                  ->orWhere('has_terminal', 'like', '%' . $searchTerm . '%')
                  ->orWhere('buyer_number', 'like', '%' . $searchTerm . '%')
                  ->orWhere('payment_terms', 'like', '%' . $searchTerm . '%')
                  ->orWhere('payment_notes', 'like', '%' . $searchTerm . '%')
                  ->orWhere('payment_status', 'like', '%' . $searchTerm . '%')
                  ->orWhere('invoice_number', 'like', '%' . $searchTerm . '%')
                  ->orWhere('invoice_notes', 'like', '%' . $searchTerm . '%')
                  ->orWhere('invoiced_fee', 'like', '%' . $searchTerm . '%');
            });
        }

        // Specific filters
        if ($request->filled('load_id')) {
            $query->where('load_id', 'like', '%' . $request->load_id . '%');
        }

        if ($request->filled('internal_load_id')) {
            $query->where('internal_load_id', 'like', '%' . $request->internal_load_id . '%');
        }

        if ($request->filled('dispatcher')) {
            $query->where('dispatcher', 'like', '%' . $request->dispatcher . '%');
        }

        if ($request->filled('dispatcher_id')) {
            $query->where('dispatcher_id', 'like', '%' . $request->dispatcher_id . '%');
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', 'like', '%' . $request->carrier_id . '%');
        }

        if ($request->filled('carrier')) {
            $query->whereHas('carrier', function($carrierQuery) use ($request) {
                $carrierQuery->where('company_name', 'like', '%' . $request->carrier . '%')
                             ->orWhereHas('user', function($userQuery) use ($request) {
                                 $userQuery->where('name', 'like', '%' . $request->carrier . '%');
                             });
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', 'like', '%' . $request->employee_id . '%');
        }

        if ($request->filled('vin')) {
            $query->where('vin', 'like', '%' . $request->vin . '%');
        }

        if ($request->filled('pickup_city')) {
            $query->where('pickup_city', 'like', '%' . $request->pickup_city . '%');
        }

        if ($request->filled('delivery_city')) {
            $query->where('delivery_city', 'like', '%' . $request->delivery_city . '%');
        }

        if ($request->filled('scheduled_pickup_date')) {
            $query->whereDate('scheduled_pickup_date', $request->scheduled_pickup_date);
        }

        if ($request->filled('driver')) {
            $query->where('driver', 'like', '%' . $request->driver . '%');
        }

        return $query;
    }

    /**
     * Determine load status based on field priority logic
     * 
     * Regras de negócio (em ordem de prioridade):
     * 1. paid -> quando tem paid_amount ou payment_status indica pago
     * 2. billed -> quando tem invoice_number ou invoice_date
     * 3. delivered -> quando tem actual_delivery_date (apenas actual, não scheduled)
     * 4. picked_up -> quando tem actual_pickup_date
     * 5. assigned -> quando tem driver OU scheduled_pickup_date
     * 6. new -> padrão
     * 
     * @param Load $load
     * @return string
     */
    public function determineLoadStatus($load)
    {
        // 1. PAID - Apenas quando payment_status é exatamente 'paid' E paid_amount > 0
        $hasPaidAmount = !empty($load->paid_amount) && $load->paid_amount > 0;
        
        if ($hasPaidAmount && !empty($load->payment_status)) {
            $paymentStatus = strtolower(trim($load->payment_status));
            
            // Apenas considerar se payment_status for exatamente 'paid'
            if ($paymentStatus === 'paid') {
                return 'paid';
            }
        }
        
        // 2. BILLED - Se tem invoice (fatura)
        if (!empty($load->invoice_number) || !empty($load->invoice_date)) {
            return 'billed';
        }
        
        // 3. DELIVERED - Se tem data de entrega REAL (apenas actual_delivery_date)
        if (!empty($load->actual_delivery_date)) {
            return 'delivered';
        }
        
        // 4. PICKED_UP - Se tem data de coleta REAL
        if (!empty($load->actual_pickup_date)) {
            return 'picked_up';
        }
        
        // 5. ASSIGNED - Se tem driver OU data de coleta agendada
        if (!empty($load->driver) || !empty($load->scheduled_pickup_date)) {
            return 'assigned';
        }
        
        // 6. NEW - Status padrão
        return 'new';
    }
}

