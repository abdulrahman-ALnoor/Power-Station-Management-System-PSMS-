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
         $invoice = Invoice::create($request->all());

    return response()->json([
        'status' => 'success',
        'message' => 'تم إنشاء الفاتورة بنجاح',
        'data' => $invoice
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
          $invoice = Invoice::findOrFail($id);

    $invoice->update($request->all());

    return response()->json([
        'status' => 'success',
        'message' => 'تم تحديث الفاتورة بنجاح',
        'data' => $invoice
    ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $invoice = Invoice::findOrFail($id);

    $invoice->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'تم حذف الفاتورة بنجاح'
    ], 200);
        //
    }
}
