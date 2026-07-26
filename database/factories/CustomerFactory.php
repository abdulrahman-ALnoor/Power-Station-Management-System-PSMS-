<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_number' => 'CUS-' . fake()->unique()->numberBetween(1000, 999999),

            'full_name' => fake()->name(),

            'customer_type' => fake()->randomElement([
                'residential',
                'commercial',
                'industrial',
            ]),

            'phone' => fake()->phoneNumber(),

            'alternative_phone' => fake()->optional()->phoneNumber(),

            'address_description' => fake()->address(),

            'notes' => fake()->optional()->sentence(),

            'created_by' => User::factory(),
        ];
    }
}