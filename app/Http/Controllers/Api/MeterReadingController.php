<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use App\Models\CompanyProfile;
use App\Models\ConsumptionCharge;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use App\Http\Resources\MeterReadingResource;
use App\Http\Requests\StoreMeterReadingRequest;
use App\Http\Requests\UpdateMeterReadingRequest;

// protected function success(string $message, $data = null, int $status = 200)
//     {
//         return response()->json([
//             'success' => true,
//             'message' => $message,
//             'data' => $data,
//         ], $status);
//     }

//     /**
//      * Return an error JSON response.
//      *
//      * @param string $message
//      * @param int $status
//      * @param mixed $data
//      * @return \Illuminate\Http\JsonResponse
//      **/
//     protected function error(string $message, int $status = 400, $data = null)
//     {
//         return response()->json([

//             'success' => false,
//             'message' => $message,
//             'data'  => $data,
//         ], $status);
//     }
class MeterReadingController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    // this function is used to get the list of meter readings with filters and pagination
    // the filters are: search by meter number or customer name, filter by status, filter by year, month and day
    public function index(Request $request)
    {
        $query = MeterReading::query();

        // 1. البحث (رقم العداد أو اسم العميل)
    if ($request->filled('search')) {

        $search = $request->search;
        $query->whereHas('meter', function ($meter) use ($search) {
            $meter->where('meter_number', 'like', "%{$search}%")
                ->orWhereHas('customer', function ($customer) use ($search) {
                    $customer->where('full_name', 'like', "%{$search}%");
                });
        });
    }

        if($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. الفلترة بالسنة والشهر واليوم
        $query
            ->when($request->year, function ($q) use ($request) {
                $q->whereYear('reading_date', $request->year);
            })
            ->when($request->month, function ($q) use ($request) {
                $q->whereMonth('reading_date', $request->month);
            })
            ->when($request->day, function ($q) use ($request) {
                $q->whereDay('reading_date', $request->day);
            });

        // جلب البيانات
        $readings = $query
            ->with([
                'meter.customer',
                'creator',
                'consumptionCharge',
            ])
            ->orderByDesc('reading_date')
            ->orderByDesc('id')
            ->paginate($request->get('per_page', 10));

        return $this->success(
        
            'تم جلب قراءات العدادات بنجاح.',
            MeterReadingResource::collection($readings)
        );
        
    }
    // this function is used to get the statistics of meter readings
    // the statistics are: total readings, total consumption, expected revenue, approved readings, pending readings, rejected readings, this month consumption, this month revenue
    public function stats(Request $request)
{
    // 1. إجمالي عدد القراءات
    $totalReadings = MeterReading::count();

    // 2. إجمالي الاستهلاك بالكيلو واط
    $totalConsumption = MeterReading::sum('consumption');

    // 3. إجمالي الإيرادات المتوقعة
    // مجموع تكلفة الاستهلاك لكل القراءات
    $expectedRevenue = MeterReading::sum('reading_cost');

    // 4. عدد القراءات المعتمدة
    $approvedReadings = MeterReading::where('status', 'approved')
        ->count();

    // 5. عدد القراءات المعلقة
    $pendingReadings = MeterReading::where('status', 'pending')
        ->count();

    // 6. عدد القراءات المرفوضة
    $rejectedReadings = MeterReading::where('status', 'rejected')
        ->count();

    // 7. استهلاك هذا الشهر
    $thisMonthConsumption = MeterReading::whereMonth(
            'reading_date',
            now()->month
        )
        ->whereYear(
            'reading_date',
            now()->year
        )
        ->sum('consumption');

    // 8. إيرادات هذا الشهر المتوقعة
    $thisMonthRevenue = MeterReading::whereMonth(
            'reading_date',
            now()->month
        )
        ->whereYear(
            'reading_date',
            now()->year
        )
        ->sum('reading_cost');

    return $this->success(
        'تم جلب إحصائيات قراءات العدادات بنجاح.',
        [
            'total_readings' => $totalReadings,
            'total_consumption' => $totalConsumption,
            'expected_revenue' => $expectedRevenue,
            'approved_readings' => $approvedReadings,
            'pending_readings' => $pendingReadings,
            'rejected_readings' => $rejectedReadings,
            'this_month_consumption' => $thisMonthConsumption,
            'this_month_revenue' => $thisMonthRevenue,
        ]
    );
}

    /**
     * Store a newly created resource in storage.
     */
    // this function is used to store a new meter reading and create a corresponding consumption charge
    // it uses a transaction to ensure that both the meter reading and consumption charge are created successfully
    public function store(StoreMeterReadingRequest $request)
    {
        try{
            $reading = DB::transaction(function() use ($request){
                $validated = $request->validated();
                // جلب بيانات الشركة من اجل الprice_per_kwh
                $companyProfile = CompanyProfile::first();
                if(!$companyProfile){
                    throw new \Exception('لم يتم إعداد بيانات الشركة بعد. يرجى إعدادها قبل إضافة قراءة العداد.');
                }
                // جلب آخر قراءة للعداد المحدد
                $lastReading = MeterReading::where('meter_id', $validated['meter_id'])
                ->latest('reading_date')
                ->latest('id')
                ->first();
                $previousReading = $lastReading?->current_reading ?? 0;
                
                if ($validated['current_reading'] < $previousReading) {
                    throw new \Exception(
                        'Current reading cannot be less than previous reading.'
                    );
                }

                // سعر الكيلو واط لكل ساعة
                $pricePerKwh = $companyProfile->price_per_kwh;
                // حساب الاستهلاك والتكلفة
                $consumption = $validated['current_reading'] - $previousReading;
                $readingCost = $consumption * $pricePerKwh;
                
                

                $reading = MeterReading::create([
                    'meter_id' => $validated['meter_id'],
                    'previous_reading' => $previousReading,
                    'current_reading' => $validated['current_reading'],
                    'consumption' => $consumption,
                    'price_per_kwh' => $pricePerKwh,
                    'reading_cost' => $readingCost,
                    'reading_date' => $validated['reading_date'],
                    'reading_method' => $validated['reading_method'] ?? null,
                    'status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => Auth::id() ?? 1, // مؤقتاً، سيتم استبداله بـ Auth::id() بعد إضافة نظام تسجيل الدخول
                ]);

                $meter = $reading->meter;

                ConsumptionCharge::create([
                    'customer_id' => $meter->customer_id,
                    'meter_id' => $meter->id,
                    'meter_reading_id' => $reading->id,
                    'total_amount' => $readingCost,
                    'paid_amount' => 0,
                    'remaining_amount' => $readingCost,
                    'status' => 'pending',
                ]);
                return $reading;
                
            });
            
            return $this->success(
                'تم تسجيل قراءة العداد بنجاح.',
                new MeterReadingResource(
                $reading->load([
                    'meter.customer',
                    'creator',
                    'consumptionCharge',
                ])
            ),
            
            
            201
        );
        }catch(\Exception $e){
            return $this->error('حدث خطأ أثناء حفظ قراءة العداد: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    // this function is used to show a specific meter reading with its related data
    // it loads the meter, customer, creator, and consumption charge relationships for the specified meter reading
    public function show(MeterReading $meterReading)
    {
        $meterReading->load([ 'meter.customer', 'creator', 'consumptionCharge', ]);
        return $this->success('تم جلب قراءة العداد بنجاح.',new MeterReadingResource($meterReading) );
    }

    /**
     * Update the specified resource in storage.
     */

    // this function is used to update a specific meter reading and its corresponding consumption charge
    // it checks if the meter reading is the latest one for the meter before allowing the update
    // it also checks if there are any invoices associated with the consumption charge before allowing the update
    // it also recalculates the consumption and reading cost based on the updated current reading
    // it uses a transaction to ensure that both the meter reading and consumption charge are updated successfully
    public function update(UpdateMeterReadingRequest $request, MeterReading $meterReading)
    {
        try {
            $validated = $request->validated();
            $companyProfile = CompanyProfile::first();
            if(!$companyProfile){
                throw new \Exception('لم يتم إعداد بيانات الشركة بعد. يرجى إعدادها قبل إضافة قراءة العداد.');
            }
            $lastReading = MeterReading::where('meter_id', $meterReading->meter_id)
            ->latest('reading_date')
            ->latest('id')
            ->first();
            if ($meterReading->id !== $lastReading->id) {
                return $this->error(
                    'لا يمكن تعديل هذه القراءة لأنها ليست آخر قراءة لهذا العداد.',
                    422
                );
            }
            $hasInvoice = $meterReading->consumptionCharge
                ->invoice()
                ->exists();

            if ($hasInvoice) {
                return $this->error(
                    'لا يمكن تعديل القراءة بعد تسجيل عملية دفع لها.',
                    422
                );
            }

            $previousReading = MeterReading::where('meter_id', $meterReading->meter_id)
            ->where('id', '!=', $meterReading->id)
            ->latest('reading_date')
            ->latest('id')
            ->value('current_reading') ?? 0;

            $consumption = $validated['current_reading'] - $previousReading;
            $readingCost = $consumption * $companyProfile->price_per_kwh;


            $meterReading->update([
                'previous_reading' => $previousReading,
                'current_reading' => $validated['current_reading'],
                'consumption' => $consumption,
                'price_per_kwh' => $companyProfile->price_per_kwh,
                'reading_cost' => $readingCost,
                'reading_date' => $validated['reading_date'],
                'reading_method' => $validated['reading_method'] ?? null,
                'status' => $validated['status'] ?? $meterReading->status,
                'notes' => $validated['notes'] ?? $meterReading->notes,
            ]);
            
            

            

            ConsumptionCharge::where('meter_reading_id', $meterReading->id)->update([
                'total_amount' => $readingCost,
                'remaining_amount' => $readingCost - ConsumptionCharge::where('meter_reading_id', $meterReading->id)->value('paid_amount'),
            ]);

        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء تحديث قراءة العداد: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    // this function is used to delete a specific meter reading
    // it uses a try-catch block to handle any exceptions that may occur during the deletion
    public function destroy(MeterReading $meterReading)
    {
        try {
            $meterReading->delete();
            return $this->success('تم حذف قراءة العداد بنجاح.');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء حذف قراءة العداد: ' . $e->getMessage());
        }
    }
}
