<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MeterReading;
use App\Models\Meter;

class MeterReadingSeeder extends Seeder
{
    public function run(): void
    {
        // recycle(): كل قراءة تُربط بعداد موجود فعلاً من MeterSeeder
        MeterReading::factory()
            ->recycle(Meter::all())
            ->count(60)
            ->create();
    }
}
