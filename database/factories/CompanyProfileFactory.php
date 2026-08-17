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

            // ثابتة على الريال اليمني (كان عشوائي بين 3 عملات، يسبب أرقام غير متناسقة)
            'currency' => 'YER',

            // سعر الكيلوواط الحقيقي باليمن يتراوح：
            // 170-230 ريال (تعرفة حكومية مدعومة) إلى 500-1400 ريال (مولدات خاصة/تجارية)
            // نستخدم متوسط واقعي يغطي الحالتين
            'price_per_kwh' => fake()->randomFloat(2, 170, 400),

            'reading_cycle_days' => fake()->randomElement([
                15,
                30,
            ]),
        ];
    }
}
