<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdditionalService;

class AdditionalServiceController extends Controller
{
    public function index()
    {
        $services = AdditionalService::with(['dispatcher.user', 'carrier.user'])->get();

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        // $actionType = $request->input('action_type'); // 'now' ou 'last'

        $data = $request->all();

        // Valor padrão para status
        if (empty($data['status'])) {
            $data['status'] = 'pending';
        }

        // Validação simples
        $request->validate([
            'describe'        => 'required|string|max:255',
            'quantity'        => 'required|numeric|min:0',
            'value'           => 'required|numeric|min:0',
            'total'           => 'required|numeric|min:0',
            // 'status'          => 'in:pending,approved,rejected',
            'carrier_id'      => 'required|string',
            // 'dispatcher_id'   => 'required|exists:dispatchers,id',
            'is_installment'  => 'boolean',
            'installment_type' => 'nullable|in:weeks,months',
            'installment_count' => 'nullable|integer|min:2|max:12',
        ]);

        // Validação condicional para parcelamento
        if ($request->is_installment) {
            $request->validate([
                'installment_type' => 'required|in:weeks,months',
                'installment_count' => 'required|integer|min:2|max:12',
            ]);
        }

        // Validação customizada para carrier_id
        if ($request->carrier_id !== 'all' && !\App\Models\Carrier::where('id', $request->carrier_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'The selected carrier id is invalid.',
                'errors' => [
                    'carrier_id' => ['The selected carrier id is invalid.']
                ]
            ], 422);
        }

        // Se carrier_id for 'all', criar um registro para cada carrier
        if ($request->carrier_id === 'all') {
            $carriers = \App\Models\Carrier::all();
            $createdServices = [];
            
            foreach ($carriers as $carrier) {
                $serviceData = $data;
                $serviceData['carrier_id'] = $carrier->id;
                $createdServices[] = AdditionalService::create($serviceData);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Additional Services saved for all carriers!',
                'data' => $createdServices,
                'count' => count($createdServices)
            ]);
        }

        // Criação do registro para um carrier específico
        $service = AdditionalService::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Additional Service saved!',
            'data'    => $service
        ]);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $service = AdditionalService::findOrFail($id);
            $service->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Additional Service deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting service: ' . $e->getMessage()
            ], 500);
        }
    }
}
