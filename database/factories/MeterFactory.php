<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),

            'meter_number' => 'MTR-' . fake()->unique()->numberBetween(100000, 999999),

            'qr_code' => fake()->unique()->uuid(),

            'installation_date' => fake()->date(),

            'installation_location' => fake()->address(),

            'status' => fake()->randomElement([
                'active',
                'disconnected',
                'maintenance',
                'damaged',
            ]),

            'installed_by' => User::factory(),

            'created_by' => User::factory(),
        ];
    }
}