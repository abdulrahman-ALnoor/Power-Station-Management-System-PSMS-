<?php

namespace App\Http\Controllers;
use App\Models\MeterReading;
use Illuminate\Http\Request;

class MeterReadingController extends Controller
{
public function index()
{
    $meterReadings = MeterReading::with([
        'meter',
        'creator',
        'consumptionCharge',
        'notifications'
    ])->get();

    return response()->json([
        'status' => 'success',
        'data' => $meterReadings
    ], 200);
}

public function show($id)
{
    $meterReading = MeterReading::with([
        'meter',
        'creator',
        'consumptionCharge',
        'notifications'
    ])->findOrFail($id);

    return response()->json([
        'status' => 'success',
        'data' => $meterReading
    ], 200);
}    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
        //
    }
}
