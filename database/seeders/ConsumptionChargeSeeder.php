<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsumptionCharge;
use App\Models\Customer;
use App\Models\Meter;
use App\Models\MeterReading;

class ConsumptionChargeSeeder extends Seeder
{
    public function run(): void
    {
        // recycle(): كل رسم استهلاك يُربط بعميل/عداد/قراءة موجودين فعلاً
        ConsumptionCharge::factory()
            ->recycle(Customer::all())
            ->recycle(Meter::all())
            ->recycle(MeterReading::all())
            ->count(60)
            ->create();
    }
}
