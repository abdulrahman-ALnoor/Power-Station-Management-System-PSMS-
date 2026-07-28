<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with([
            'customer:id,full_name',
            'accountant:id,name',
            'consumptionCharge:id,meter_id,meter_reading_id,total_amount',
            'consumptionCharge.meter:id,meter_number',
            'consumptionCharge.meterReading:id,consumption',
    ])->paginate(10);

    return response()->json([
        'success' => true,
        'message' => 'Invoices retrieved successfully.',
        'data' => $invoices,
    ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = Invoice::with([
            'customer:id,full_name',
            'accountant:id,name',
            'consumptionCharge:id,meter_id,meter_reading_id,total_amount',
            'consumptionCharge.meter:id,meter_number',
            'consumptionCharge.meterReading:id,consumption',
        ])->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice retrieved successfully.',
            'data' => $invoice,
        ]);
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
        //
    }
}
