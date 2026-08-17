<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use App\Models\ConsumptionCharge;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        // ننشئ رسم الاستهلاك أول، عشان نربط مبلغ الفاتورة بمبلغه الفعلي
        // (قبل كذا كانا يتولدوا بشكل عشوائي منفصل تماماً عن بعض، وهذا غير منطقي)
        $charge = ConsumptionCharge::factory()->create();

        $outstanding = $charge->total_amount;
        $paid = $charge->paid_amount;

        // تاريخ الفاتورة: بعد أو يوم إنشاء رسم الاستهلاك (منطقي: الدفع يصير بعد الفاتورة، مو قبلها)
        $invoiceDate = fake()->dateTimeBetween($charge->created_at, 'now');

        return [
            'invoice_number' => 'INV-' . fake()->unique()->numberBetween(1000, 999999),

            'customer_id' => $charge->customer_id,

            'accountant_id' => \App\Models\User::inRandomOrder()->first()->id,

            'consumption_charge_id' => $charge->id,

            'outstanding_before_payment' => $outstanding,

            'paid_amount' => $paid,

            'remaining_balance' => $outstanding - $paid,

            'status' => $paid >= $outstanding ? 'paid' : 'partially_paid',

            'payment_notes' => fake()->optional()->sentence(),

            'created_at' => $invoiceDate,
            'updated_at' => $invoiceDate,
        ];
    }

    /**
     * تربط الفاتورة برسم استهلاك موجود فعلاً بدل إنشاء وهمي جديد.
     * تُستخدم من InvoiceSeeder عشان الفواتير ترتبط بعملاء حقيقيين من CustomerSeeder.
     */
    public function forCharge(ConsumptionCharge $charge): static
    {
        return $this->state(function () use ($charge) {
            $outstanding = $charge->total_amount;
            $paid = $charge->paid_amount;
            $invoiceDate = fake()->dateTimeBetween($charge->created_at, 'now');

            return [
                'customer_id' => $charge->customer_id,
                'consumption_charge_id' => $charge->id,
                'outstanding_before_payment' => $outstanding,
                'paid_amount' => $paid,
                'remaining_balance' => $outstanding - $paid,
                'status' => $paid >= $outstanding ? 'paid' : 'partially_paid',
                'created_at' => $invoiceDate,
                'updated_at' => $invoiceDate,
            ];
        });
    }
}
