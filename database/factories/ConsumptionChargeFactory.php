<?php

namespace Database\Factories;

use App\Models\ConsumptionCharge;
use App\Models\Customer;
use App\Models\Meter;
use App\Models\MeterReading;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsumptionChargeFactory extends Factory
{
    protected $model = ConsumptionCharge::class;

    public function definition(): array
    {
        // النطاق يعكس فاتورة واقعية بالريال اليمني: استهلاك (10-500 ك.و) × سعر (170-400 ريال)
        $total = fake()->randomFloat(2, 1500, 200000);

        // توزيع واقعي لحالة السداد بدل عشوائية بحتة:
        // 40% مدفوعة بالكامل، 45% جزئية، 15% لسا ما دفع فيها شي
        // (هذا يعكس واقع أي شركة خدمات: أغلب العملاء يسددوا، فئة تقسّط، وفئة متأخرة)
        $paymentType = fake()->randomElement([
            'paid', 'paid', 'paid', 'paid',              // 40%
            'partial', 'partial', 'partial', 'partial', 'partial', // 45% (تقريباً)
            'unpaid', 'unpaid',                            // 15% تقريباً
        ]);

        $paid = match ($paymentType) {
            'paid' => $total,
            'partial' => fake()->randomFloat(2, 1, $total - 1),
            'unpaid' => 0,
        };

        $status = match (true) {
            $paid >= $total => 'paid',
            $paid > 0 => 'partially_paid',
            default => 'pending',
        };

        $chargeDate = fake()->dateTimeBetween('-18 months', 'now');

        return [
            'customer_id' => Customer::factory(),

            'meter_id' => Meter::factory(),

            'meter_reading_id' => MeterReading::factory(),

            'total_amount' => $total,

            'paid_amount' => $paid,

            'remaining_amount' => $total - $paid,

            'status' => $status,

            'created_at' => $chargeDate,
            'updated_at' => $chargeDate,
        ];
    }
}
