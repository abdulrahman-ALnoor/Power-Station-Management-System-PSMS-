<?php

namespace Database\Factories;

use App\Models\Meter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeterReadingFactory extends Factory
{
    public function definition(): array
    {
        $previous = fake()->randomFloat(2, 0, 10000);
        $consumption = fake()->randomFloat(2, 10, 500);
        $current = $previous + $consumption;
        // سعر الكيلوواط بالريال اليمني (نفس نطاق CompanyProfileFactory عشان يتوافقوا منطقياً)
        $price = fake()->randomFloat(2, 170, 400);
        $cost = $consumption * $price;

        // تاريخ القراءة: موزّع على آخر 18 شهر بس (مو من 1970!)
        // هذا يخلي البيانات تبدو واقعية لنظام حديث، ويسمح بتقارير شهرية منطقية
        $readingDate = fake()->dateTimeBetween('-18 months', 'now');

        return [
            'created_by' => \App\Models\User::inRandomOrder()->first()->id,

            'meter_id' => Meter::factory(),

            'previous_reading' => $previous,

            'current_reading' => $current,

            'consumption' => $consumption,

            'price_per_kwh' => $price,

            'reading_cost' => $cost,

            'reading_date' => $readingDate,

            'reading_method' => fake()->randomElement([
                'manual',
                'qr_scan',
            ]),

            'status' => fake()->randomElement([
                'pending',
                'approved',
                'rejected',
            ]),

            'notes' => fake()->optional()->sentence(),

            // نجعل created_at قريب من تاريخ القراءة نفسه (منطقي: القراءة تُسجَّل وقتها تقريباً)
            'created_at' => $readingDate,
            'updated_at' => $readingDate,
        ];
    }
}
