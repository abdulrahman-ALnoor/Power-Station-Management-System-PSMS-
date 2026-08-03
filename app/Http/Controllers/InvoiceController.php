<?php

namespace App\Http\Controllers;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
{
    $invoices = Invoice::with(['customer', 'accountant', 'notifications'])->get();

    return response()->json([
        'status' => 'success',
        'data' => $invoices
    ], 200);
}

public function show($id)
{
    $invoice = Invoice::with(['customer', 'accountant', 'notifications'])
        ->findOrFail($id);

    return response()->json([
        'status' => 'success',
        'data' => $invoice
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
