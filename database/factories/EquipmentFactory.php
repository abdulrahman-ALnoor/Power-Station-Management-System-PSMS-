<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'equipment_name' => fake()->randomElement([
                'Digital Meter',
                'Electric Pole',
                'Transformer',
                'Generator',
                'Power Cable',
                'Circuit Breaker',
            ]),

            'serial_number' => fake()->unique()->bothify('EQ-#####'),

            'status' => fake()->randomElement([
                'available',
                'maintenance',
                'damaged',
                'lost',
            ]),

            'notes' => fake()->optional()->sentence(),

            'created_by' => User::factory(),
        ];
    }
}