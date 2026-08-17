<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\ConsumptionCharge;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        // بدل إنشاء 60 فاتورة "معلّقة بالهواء" (كل وحدة تخلق عميل ورسم استهلاك وهميين جدد)،
        // ننشئ فاتورة (سجل دفع) واحدة لكل رسم استهلاك موجود فعلاً من ConsumptionChargeSeeder.
        // كذا نضمن كل فاتورة مرتبطة بعميل حقيقي من قائمة CustomerSeeder الأساسية.
        ConsumptionCharge::all()->each(function (ConsumptionCharge $charge) {
            Invoice::factory()->forCharge($charge)->create();
        });
    }
}
