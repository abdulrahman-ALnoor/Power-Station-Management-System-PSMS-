<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Meter\StoreMeterRequest;
use App\Http\Requests\Meter\UpdateMeterRequest;
use App\Http\Resources\MeterResource;
use App\Models\Meter;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class MeterController extends Controller
{
    use ApiResponse;

            /**
         * Display a listing of the resource.
         */
        // This function retrieves all meters with their related data.
            public function index(Request $request)
    {
        try {
            $query = Meter::with(['customer', 'installer', 'creator']);

            // بحث برقم العداد
            if ($request->filled('search')) {
                $query->where('meter_number', 'like', '%' . $request->search . '%');
            }

            // بحث بالمنطقة
            if ($request->filled('location')) {
                $query->where('installation_location', 'like', '%' . $request->location . '%');
            }

            // فلترة بتاريخ الإنشاء (من - إلى)
            // بدّل السطرين دول
                if ($request->filled('date_from')) {
                    $query->whereDate('installation_date', '>=', $request->date_from);
                }

                if ($request->filled('date_to')) {
                    $query->whereDate('installation_date', '<=', $request->date_to);
                }

            // فلترة حسب الحالة
            if ($request->filled('status')) {
                $query->where('status', $request->status);

            }

            $meters = $query->latest()->paginate(10);

            return $this->success('تم جلب العدادات بنجاح.', MeterResource::collection($meters));

        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء جلب العدادات: ' . $e->getMessage());
        }
    }


        /**
         * Store a newly created resource in storage.
         */
        // This function is used to create a new meter.
        public function store(StoreMeterRequest $request)
        {
            try {

                $meter = Meter::create($request->validated());

                return $this->success(
                    'تم إنشاء العداد بنجاح.',
                    new MeterResource(
                        $meter->load([
                            'customer',
                            'installer',
                            'creator',
                        ])
                    ),
                    201
                );

            } catch (\Exception $e) {

                return $this->error(
                    'حدث خطأ أثناء إنشاء العداد: ' . $e->getMessage()
                );

            }
        }

    /**
     * Display the specified resource.
     */
    // This function retrieves a specific meter with its related data.
    public function show(Meter $meter)
    {
        try {

            $meter->load([
                'customer',
                'installer',
                'creator',
            ]);

            return $this->success(
                'تم جلب بيانات العداد بنجاح.',
                new MeterResource($meter)
            );

        } catch (\Exception $e) {

            return $this->error(
                'حدث خطأ أثناء جلب بيانات العداد: ' . $e->getMessage()
            );

        }
    }

            public function stats()
    {
        try {
            $total = Meter::count();

            $byStatus = Meter::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return $this->success('تم جلب إحصائيات العدادات بنجاح.', [
                'total_meters' => $total,
                'active'       => $byStatus['active'] ?? 0,
                'disconnected' => $byStatus['disconnected'] ?? 0,
                'maintenance'  => $byStatus['maintenance'] ?? 0,
                'damaged'      => $byStatus['damaged'] ?? 0,
            ]);
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء جلب الإحصائيات: ' . $e->getMessage());
        }
    }

            /**
         * Show the form for creating a new resource.
         */
        // This method is not used in API.
        public function create()
        {
            return $this->success(
                'هذه الدالة غير مستخدمة في واجهات API.'
            );
        }

            /**
         * Update the specified resource in storage.
         */
        // This function is used to update a specific meter.
        public function update(UpdateMeterRequest $request, Meter $meter)
        {
            try {

                $meter->update($request->validated());

                return $this->success(
                    'تم تحديث العداد بنجاح.',
                    new MeterResource(
                        $meter->load([
                            'customer',
                            'installer',
                            'creator',
                        ])
                    )
                );

            } catch (\Exception $e) {

                return $this->error(
                    'حدث خطأ أثناء تحديث العداد: ' . $e->getMessage()
                );

            }
        }

            /**
         * Remove the specified resource from storage.
         */
        // This function is used to delete a specific meter.
        public function destroy(Meter $meter)
        {
            try {

                $meter->delete();

                return $this->success(
                    'تم حذف العداد بنجاح.'
                );

            } catch (\Exception $e) {

                return $this->error(
                    'حدث خطأ أثناء حذف العداد: ' . $e->getMessage()
                );

            }
        }
}
