<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CompanyProfileSeeder::class,
            PermissionSeeder::class,
           // RoleSeeder::class,
            UserSeeder::class,
            //UserRoleSeeder::class,
            CustomerSeeder::class,
            MeterSeeder::class,
            MeterReadingSeeder::class,
            ConsumptionChargeSeeder::class,
            InvoiceSeeder::class,
            ServiceRequestSeeder::class,
            EquipmentSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
