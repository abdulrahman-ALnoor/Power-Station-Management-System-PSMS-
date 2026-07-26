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
        $total = fake()->randomFloat(2, 1000, 50000);
        $paid = fake()->randomFloat(2, 0, $total);

        return [
            'customer_id' => Customer::factory(),

            'meter_id' => Meter::factory(),

            'meter_reading_id' => MeterReading::factory(),

            'total_amount' => $total,

            'paid_amount' => $paid,

            'remaining_amount' => $total - $paid,

            'status' => fake()->randomElement([
                'pending',
                'partially_paid',
                'paid',
            ]),
        ];
    }
}