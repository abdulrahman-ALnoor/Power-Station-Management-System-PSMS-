<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    use ApiResponse;

    public function stats()
    {
        $data = [
            'total_customers'   => Customer::count(),
            'residential_count' => Customer::where('customer_type', 'residential')->count(),
            'commercial_count'  => Customer::where('customer_type', 'commercial')->count(),
            'industrial_count'  => Customer::where('customer_type', 'industrial')->count(),
        ];

        return $this->success('تم جلب الإحصائيات بنجاح', $data, 200);
    }

    public function index()
    {
        $customers = Customer::with(['creator'])->latest()->get();

        return $this->success('تم جلب العملاء بنجاح', CustomerResource::collection($customers), 200);
    }

    public function show(int $id)
    {
        $customer = Customer::with(['creator', 'meters', 'invoices'])->findOrFail($id);

        return $this->success('تم جلب بيانات العميل بنجاح', new CustomerResource($customer), 200);
    }

    public function store(StoreCustomerRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id() ?? 1;

        $customer = Customer::create($validated);

        return $this->success('تم إضافة العميل بنجاح', new CustomerResource($customer), 201);
    }

    public function update(UpdateCustomerRequest $request, int $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($request->validated());

        return $this->success('تم تحديث بيانات العميل بنجاح', new CustomerResource($customer), 200);
    }

    public function destroy(int $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return $this->success('تم حذف العميل بنجاح', null, 200);
    }
}