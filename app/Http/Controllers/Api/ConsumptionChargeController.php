<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConsumptionCharge\StoreConsumptionChargeRequest;
use App\Http\Requests\ConsumptionCharge\UpdateConsumptionChargeRequest;
use App\Http\Resources\ConsumptionChargeResource;
use App\Models\ConsumptionCharge;
use App\Traits\ApiResponse;

class ConsumptionChargeController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    // This function retrieves all consumption charges with their related data.
    public function index()
    {
        try {

            $charges = ConsumptionCharge::with([
                'customer',
                'meter',
                'meterReading',
                'invoices',
            ])->latest()->get();

            return $this->success(
                'تم جلب رسوم الاستهلاك بنجاح.',
                ConsumptionChargeResource::collection($charges)
            );

        } catch (\Exception $e) {

            return $this->error(
                'حدث خطأ أثناء جلب رسوم الاستهلاك: ' . $e->getMessage()
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
     * Store a newly created resource in storage.
     */
    // This function is used to create a new consumption charge.
    public function store(StoreConsumptionChargeRequest $request)
    {
        try {

            $consumptionCharge = ConsumptionCharge::create(
                $request->validated()
            );

            return $this->success(
                'تم إنشاء رسوم الاستهلاك بنجاح.',
                new ConsumptionChargeResource(
                    $consumptionCharge->load([
                        'customer',
                        'meter',
                        'meterReading',
                        'invoices',
                    ])
                ),
                201
            );

        } catch (\Exception $e) {

            return $this->error(
                'حدث خطأ أثناء إنشاء رسوم الاستهلاك: ' . $e->getMessage()
            );

        }
    }

            /**
         * Display the specified resource.
         */
        // This function retrieves a specific consumption charge with its related data.
        public function show(ConsumptionCharge $consumptionCharge)
        {
            try {

                $consumptionCharge->load([
                    'customer',
                    'meter',
                    'meterReading',
                    'invoices',
                ]);

                return $this->success(
                    'تم جلب رسوم الاستهلاك بنجاح.',
                    new ConsumptionChargeResource($consumptionCharge)
                );

            } catch (\Exception $e) {

                return $this->error(
                    'حدث خطأ أثناء جلب رسوم الاستهلاك: ' . $e->getMessage()
                );

            }
        }



            /**
         * Show the form for editing the specified resource.
         */
        // This method is not used in API.
        public function edit(string $id)
        {
            return $this->success(
                'هذه الدالة غير مستخدمة في واجهات API.'
            );
        }



            /**
         * Update the specified resource in storage.
         */
        // This function is used to update a specific consumption charge.
        public function update(UpdateConsumptionChargeRequest $request, ConsumptionCharge $consumptionCharge)
        {
            try {

                $consumptionCharge->update(
                    $request->validated()
                );

                return $this->success(
                    'تم تحديث رسوم الاستهلاك بنجاح.',
                    new ConsumptionChargeResource(
                        $consumptionCharge->load([
                            'customer',
                            'meter',
                            'meterReading',
                            'invoices',
                        ])
                    )
                );

            } catch (\Exception $e) {

                return $this->error(
                    'حدث خطأ أثناء تحديث رسوم الاستهلاك: ' . $e->getMessage()
                );

            }
        }
        /**
         * Remove the specified resource from storage.
         */
        // This function is used to delete a specific consumption charge.
        public function destroy(ConsumptionCharge $consumptionCharge)
        {
            try {

                $consumptionCharge->delete();

                return $this->success(
                    'تم حذف رسوم الاستهلاك بنجاح.'
                );

            } catch (\Exception $e) {

                return $this->error(
                    'حدث خطأ أثناء حذف رسوم الاستهلاك: ' . $e->getMessage()
                );

            }
        }
}
