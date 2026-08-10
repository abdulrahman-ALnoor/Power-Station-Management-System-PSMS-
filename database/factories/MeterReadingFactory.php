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
        $price = fake()->randomFloat(2, 20, 100);
        $cost = $consumption * $price;

        return [
            'created_by' => \App\Models\User::inRandomOrder()->first()->id,

            'meter_id' => Meter::factory(),

            'previous_reading' => $previous,

            'current_reading' => $current,

            'consumption' => $consumption,

            'price_per_kwh' => $price,

            'reading_cost' => $cost,

            'reading_date' => fake()->date(),

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
        ];
    }
}
