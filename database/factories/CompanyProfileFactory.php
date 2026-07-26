<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),

            'logo' => fake()->optional()->imageUrl(300, 300, 'logo'),

            'address' => fake()->address(),

            'whatsapp_number' => fake()->phoneNumber(),

            'support_number' => fake()->phoneNumber(),

            'currency' => fake()->randomElement([
                'YER',
                'USD',
                'SAR',
            ]),

            'price_per_kwh' => fake()->randomFloat(2, 50, 500),

            'reading_cycle_days' => fake()->randomElement([
                15,
                30,
            ]),
        ];
    }
}