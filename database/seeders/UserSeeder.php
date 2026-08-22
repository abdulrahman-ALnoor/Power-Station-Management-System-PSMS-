<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * إنشاء مستخدمين تجريبيين وإنشاء حساب مدير واضح للاستخدام المحلي.
     */
    public function run(): void
    {
        User::factory()->count(20)->create();

        $admin = User::updateOrCreate(
            ['email' => env('PSMS_ADMIN_EMAIL', 'admin@psms.test')],
            [
                'name' => env('PSMS_ADMIN_NAME', 'System Admin'),
                'password' => Hash::make(env('PSMS_ADMIN_PASSWORD', 'ChangeThisPassword123!')),
                'phone' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');
    }
}
