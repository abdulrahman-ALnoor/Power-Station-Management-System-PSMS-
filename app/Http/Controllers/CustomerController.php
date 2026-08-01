<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{

    public function index()
    {
       // استخدام with لجلب العلاقات (مثل المستخدم الذي أنشأ العميل والفواتير) لمنع مشكلة N+1
        $customers = Customer::with(['creator', 'invoices'])->get();
        
        return response()->json([
            'status' => 'success', 
            'data'   => $customers
        ], 200);
    }

    // عرض عميل محدد برقم الـ ID بصيغة JSON
    public function show(int $id) 
    {
        // جلب العميل مع علاقاته، وإرجاع 404 تلقائياً إذا لم يوجد
        $customer = Customer::with(['creator', 'invoices', 'meters'])->findOrFail($id);
        
        return response()->json([
            'status' => 'success', 
            'data'   => $customer
        ], 200);
    }

    // إضافة عميل جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_number'     => 'required|string|max:50|unique:customers,customer_number',
            'full_name'           => 'required|string|max:255',
            'customer_type'       => 'required|string|max:50',
            'phone'               => 'required|string|max:20',
            'alternative_phone'   => 'nullable|string|max:20',
            'address_description' => 'nullable|string',
            'notes'               => 'nullable|string',
        ]);

        // للعرض فقط: إذا لم يكن هناك مستخدم مسجل الدخول، نضع قيمة افتراضية أو نجعلها nullable في قاعدة البيانات
        $validated['created_by'] = auth::id() ?? 1; 

        $customer = Customer::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة العميل بنجاح',
            'data'   => $customer
        ], 201, [], JSON_UNESCAPED_UNICODE); // 201 تعني Created
    }

    // تعديل بيانات عميل
    public function update(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'customer_number'     => 'required|string|max:50|unique:customers,customer_number,' . $id,
            'full_name'           => 'required|string|max:255',
            'customer_type'       => 'required|string|max:50',
            'phone'               => 'required|string|max:20',
            'alternative_phone'   => 'nullable|string|max:20',
            'address_description' => 'nullable|string',
            'notes'               => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بيانات العميل بنجاح',
            'data'   => $customer
        ], 200);
    }

    // حذف عميل
    public function destroy(int $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete(); // سيتم حذفه بشكل مرن (Soft Delete)

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف العميل بنجاح'
        ], 200);
    }



}
