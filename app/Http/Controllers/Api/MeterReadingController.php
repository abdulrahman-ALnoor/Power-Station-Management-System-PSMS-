<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use App\Models\CompanyProfile;
use App\Models\ConsumptionCharge;
use App\Models\Invoice;
use App\Models\Meter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use App\Http\Resources\MeterReadingResource;
use App\Http\Requests\StoreMeterReadingRequest;
use App\Http\Requests\UpdateMeterReadingRequest;

class MeterReadingController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', MeterReading::class);

        $query = MeterReading::query();

        // القارئ يشوف بس القراءات اللي أنشأها هو (Gate::before يتجاوز هذا للأدمن)
        if ($request->user()->hasRole('reader')) {
            $query->where('created_by', $request->user()->id);
        }

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
    public function store(StoreMeterReadingRequest $request)
    {
        try{
            $reading = DB::transaction(function() use ($request){
                $validated = $request->validated();
                $companyProfile = CompanyProfile::first();

                if(!$companyProfile){
                    throw new \Exception('لم يتم إعداد بيانات الشركة بعد. يرجى إعدادها قبل إضافة قراءة العداد.');
                }

                $lastReading = MeterReading::where('meter_id', $validated['meter_id'])
                    ->latest('reading_date')
                    ->latest('id')
                    ->first();
                $previousReading = $lastReading?->current_reading ?? 0;

                if ($validated['current_reading'] < $previousReading) {
                    throw new \Exception('Current reading cannot be less than previous reading.');
                }

                $pricePerKwh = $companyProfile->price_per_kwh;
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
                    'created_by' => Auth::id() ?? 1,
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
    public function show(MeterReading $meterReading)
    {
        $this->authorize('view', $meterReading);

        $meterReading->load(['meter.customer', 'creator', 'consumptionCharge']);
        return $this->success('تم جلب قراءة العداد بنجاح.', new MeterReadingResource($meterReading));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMeterReadingRequest $request, MeterReading $meterReading)
    {
        $this->authorize('update', $meterReading);

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

            $hasInvoice = $meterReading->consumptionCharge()->whereHas('invoice')->exists();

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

            return $this->success('تم تحديث القراءة بنجاح.', new MeterReadingResource($meterReading->refresh()));

        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء تحديث قراءة العداد: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MeterReading $meterReading)
    {
        $this->authorize('delete', $meterReading);

        try {
            $meterReading->delete();
            return $this->success('تم حذف قراءة العداد بنجاح.');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء حذف قراءة العداد: ' . $e->getMessage());
        }
    }

    // ====================================================================
    // ==================== دوال واجهة القارئ الجديدة =====================
    // ====================================================================

    public function readerDashboardStats(Request $request)
    {
        $userId = $request->user()->id;

        $totalReadings = MeterReading::where('created_by', $userId)->count();
        $currentMonthReadings = MeterReading::where('created_by', $userId)
            ->whereMonth('reading_date', now()->month)
            ->whereYear('reading_date', now()->year)
            ->count();

        return $this->success('تم جلب إحصائيات القارئ بنجاح.', [
            'total_readings' => $totalReadings,
            'current_month_readings' => $currentMonthReadings,
        ]);
    }

    public function readerReadingsProgress(Request $request)
    {
        $userId = $request->user()->id;
        
        // تم التعديل هنا لاستخدام installed_by بدلاً من user_id
        $assignedMetersCount = Meter::where('installed_by', $userId)->count();
        
        $readMetersCount = MeterReading::where('created_by', $userId)
            ->whereMonth('reading_date', now()->month)
            ->distinct('meter_id')
            ->count('meter_id');

        $percentage = $assignedMetersCount > 0
            ? round(($readMetersCount / $assignedMetersCount) * 100, 2)
            : 0;

        return $this->success('تم جلب نسبة تقدم القراءات بنجاح.', [
            'assigned_meters' => $assignedMetersCount,
            'completed_readings' => $readMetersCount,
            'progress_percentage' => $percentage,
        ]);
    }

    public function readerConsumptionStats(Request $request)
    {
        $userId = $request->user()->id;

        $totalConsumption = MeterReading::where('created_by', $userId)->sum('consumption');
        $avgConsumption = MeterReading::where('created_by', $userId)->avg('consumption');

        return $this->success('تم جلب بيانات الاستهلاك بنجاح.', [
            'total_consumption' => round($totalConsumption, 2),
            'average_consumption' => round($avgConsumption, 2),
        ]);
    }

    public function readerLatestReadings(Request $request)
    {
        $userId = $request->user()->id;

        $latestReadings = MeterReading::with(['meter.customer', 'consumptionCharge'])
            ->where('created_by', $userId)
            ->orderByDesc('reading_date')
            ->take(5)
            ->get();

        return $this->success('تم جلب أحدث القراءات بنجاح.', MeterReadingResource::collection($latestReadings));
    }

    public function readerIndex(Request $request)
    {
        $userId = $request->user()->id;

        $query = MeterReading::with(['meter.customer', 'consumptionCharge'])
            ->where('created_by', $userId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('meter', function ($meter) use ($search) {
                $meter->where('meter_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('month')) {
            $query->whereMonth('reading_date', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $readings = $query->orderByDesc('reading_date')
                          ->orderByDesc('id')
                          ->paginate($request->get('per_page', 15));

        return $this->success('تم جلب قائمة القراءات بنجاح.', MeterReadingResource::collection($readings));
    }

    public function readerReadingsStats(Request $request)
    {
        $userId = $request->user()->id;
        return $this->success('تم جلب إحصائيات قراءات القارئ بنجاح.', [
            'total_readings' => MeterReading::where('created_by', $userId)->count(),
            'approved_readings' => MeterReading::where('created_by', $userId)->where('status', 'approved')->count(),
            'pending_readings' => MeterReading::where('created_by', $userId)->where('status', 'pending')->count(),
            'rejected_readings' => MeterReading::where('created_by', $userId)->where('status', 'rejected')->count(),
        ]);
    }
}
