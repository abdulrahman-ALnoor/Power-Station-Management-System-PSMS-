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
        $meterReading = MeterReading::create($request->all());

    return response()->json([
        'status' => 'success',
        'message' => 'تم إنشاء قراءة العداد بنجاح',
        'data' => $meterReading,
    ], 201);
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
        $meterReading = MeterReading::findOrFail($id);

    $meterReading->update($request->all());

    return response()->json([
        'status' => 'success',
        'message' => 'تم تحديث قراءة العداد بنجاح',
        'data' => $meterReading,
    ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
         $meterReading = MeterReading::findOrFail($id);

    $meterReading->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'تم حذف قراءة العداد بنجاح',
    ], 200);
    }
}
