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
        public function index()
        {
            try {

                $meters = Meter::with([
                    'customer',
                    'installer',
                    'creator',
                ])->latest()->get();

                return $this->success(
                    'تم جلب العدادات بنجاح.',
                    MeterResource::collection($meters)
                );

            } catch (\Exception $e) {

                return $this->error(
                    'حدث خطأ أثناء جلب العدادات: ' . $e->getMessage()
                );

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