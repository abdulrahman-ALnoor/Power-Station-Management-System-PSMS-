<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\ConsumptionCharge;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;



class CustomerController extends Controller
{
    use ApiResponse;

    // دوالك السابقة كما هي
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

    // --------------------------------------------------------
    // الدالة الجديدة المطابقة للواجهة 4 (نافذة تفاصيل العميل)
    // --------------------------------------------------------
    public function customerDetails(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);

        $metersQuery = $customer->meters();

        if ($request->filled('meter_status')) {
            $metersQuery->where('status', $request->meter_status);
        }

        if ($request->filled('search')) {
            $metersQuery->where('meter_number', 'like', '%' . $request->search . '%');
        }

        $meters = $metersQuery->get();

        $meterIds = $customer->meters()->pluck('id');
        
        $totalConsumption = $meterIds->isNotEmpty() 
            ? MeterReading::whereIn('meter_id', $meterIds)->sum('consumption') 
            : 0;

        $outstandingBalance = ConsumptionCharge::where('customer_id', $customer->id)
            ->whereIn('status', ['pending', 'partially_paid'])
            ->sum('remaining_amount');

        $lastPayment = Invoice::where('customer_id', $customer->id)
            ->latest()
            ->first();

        $latestServiceRequests = ServiceRequest::where('customer_id', $customer->id)
            ->latest()
            ->take(5)
            ->get();

        $data = [
            'customer_info'     => $customer,
            'statistics'        => [
                'meters_count'       => $customer->meters()->count(),
                'total_consumption'  => $totalConsumption,
                'outstanding_balance'=> $outstandingBalance,
            ],
            'meters'            => $meters,
            'financial_summary' => [
                'last_payment_amount' => $lastPayment?->paid_amount ?? 0,
                'last_payment_date'   => $lastPayment?->created_at?->format('Y-m-d'),
            ],
            'latest_service_requests' => $latestServiceRequests,
        ];

        return $this->success('تم جلب تفاصيل وإحصائيات العميل بنجاح', $data, 200);
    }
}