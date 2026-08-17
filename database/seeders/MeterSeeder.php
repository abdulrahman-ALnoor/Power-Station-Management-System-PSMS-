<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meter;
use App\Models\Customer;

class MeterSeeder extends Seeder
{
    public function run(): void
    {
        // recycle(): يخلي كل عداد يُربط بعميل موجود فعلاً من CustomerSeeder،
        // بدل ما ينشئ عميل وهمي جديد منفصل لكل عداد
        Meter::factory()
            ->recycle(Customer::all())
            ->count(30)
            ->create();
    }
}
