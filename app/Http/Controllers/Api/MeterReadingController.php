<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use Illuminate\Http\Request;

class MeterReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $meterReadings = MeterReading::with([
            'creator',
            'meter',
            'consumptionCharge',
        ])->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Meter readings retrieved successfully.',
            'data' => $meterReadings,
        ], 200);
        
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
        $meterReading = MeterReading::with([
            'creator',
            'meter',
            'consumptionCharge',
        ])->find($id);

        if (!$meterReading) {
            return response()->json([
                'success' => false,
                'message' => 'Meter reading not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Meter reading retrieved successfully.',
            'data' => $meterReading,
        ], 200);
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
