<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MeterReading;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),

            'meter_reading_id' => MeterReading::factory(),

            'invoice_id' => Invoice::factory(),

            'notification_type' => fake()->randomElement([
                'reading',
                'payment',
                'service_request',
                'general',
            ]),

            'message' => fake()->sentence(),

            'status' => fake()->randomElement([
                'pending',
                'sent',
                'failed',
                'read',
            ]),

            'whatsapp_message_id' => fake()->optional()->uuid(),

            'sent_at' => fake()->optional()->dateTime(),

            'read_at' => fake()->optional()->dateTime(),
        ];
    }
}