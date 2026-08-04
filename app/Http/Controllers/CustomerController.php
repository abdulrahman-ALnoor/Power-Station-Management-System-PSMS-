<?php
namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * مسار الإحصائيات (customers/stats)
     */
    public function stats()
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'total_customers'   => Customer::count(),
                'residential_count' => Customer::where('customer_type', 'residential')->count(),
                'commercial_count'  => Customer::where('customer_type', 'commercial')->count(),
                'industrial_count'  => Customer::where('customer_type', 'industrial')->count(),
            ]
        ], 200);
    }

    public function index()
    {
        $customers = Customer::with(['creator'])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data'   => CustomerResource::collection($customers)
        ], 200);
    }

    public function show(int $id)
    {
        $customer = Customer::with(['creator', 'meters', 'invoices'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => new CustomerResource($customer)
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_number'     => 'nullable|string|max:50|unique:customers,customer_number',
            'full_name'           => 'required|string|max:150',
            'customer_type'       => 'required|in:residential,commercial,industrial',
            'phone'               => 'required|string|max:30',
            'alternative_phone'   => 'nullable|string|max:30',
            'address_description' => 'nullable|string',
            'notes'               => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id() ?? 1;

        $customer = Customer::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'تم إضافة العميل بنجاح',
            'data'    => new CustomerResource($customer)
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    public function update(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'customer_number'     => 'nullable|string|max:50|unique:customers,customer_number,' . $id,
            'full_name'           => 'sometimes|string|max:150',
            'customer_type'       => 'sometimes|in:residential,commercial,industrial',
            'phone'               => 'sometimes|string|max:30',
            'alternative_phone'   => 'nullable|string|max:30',
            'address_description' => 'nullable|string',
            'notes'               => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'تم تحديث بيانات العميل بنجاح',
            'data'    => new CustomerResource($customer)
        ], 200);
    }

    public function destroy(int $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'تم حذف العميل بنجاح'
        ], 200);
    }
}