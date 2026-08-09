<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


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


}
