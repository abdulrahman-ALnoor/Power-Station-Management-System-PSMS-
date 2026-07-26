<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $outstanding = fake()->randomFloat(2, 1000, 50000);
        $paid = fake()->randomFloat(2, 0, $outstanding);

        return [
            'invoice_number' => 'INV-' . fake()->unique()->numberBetween(1000, 999999),

            'customer_id' => Customer::factory(),

            'accountant_id' => User::factory(),

            'outstanding_before_payment' => $outstanding,

            'paid_amount' => $paid,

            'remaining_balance' => $outstanding - $paid,

            'status' => fake()->randomElement([
                'paid',
                'partially_paid',
            ]),

            'payment_notes' => fake()->optional()->sentence(),
        ];
    }
}