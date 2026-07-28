<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Meter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'meter_id' => Meter::factory(),

            'customer_id' => Customer::factory(),

            'created_by' => User::factory(),

            'assigned_engineer_id' => User::factory(),

            'request_type' => fake()->randomElement([
                'new_connection',
                'maintenance',
                'disconnection',
            ]),

            'priority' => fake()->randomElement([
                'low',
                'medium',
                'high',
                'emergency',
            ]),

            'status' => fake()->randomElement([
                'pending',
                'assigned',
                'in_progress',
                'completed',
                'cancelled',
            ]),

            'description' => fake()->sentence(),

            'completed_at' => fake()->optional()->dateTime(),
        ];
    }
}