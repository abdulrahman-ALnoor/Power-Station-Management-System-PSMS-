<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Owner',
                'System Manager',
                'Station Manager',
                'Accountant',
                'Customer Service',
                'Meter Reader',
                'Collector',
                'Technician',
            ]),

            'description' => fake()->sentence(),
        ];
    }
}