<?php

namespace App\Observers;

use App\Models\Meter;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class MeterObserver
{
    public function created(Meter $meter): void
    {
        // 1. تحديد اسم الملف والمسار
        $fileName = 'meter_' . $meter->id . '.svg';
        $path = 'qrcodes/' . $fileName;

        // 2. توليد الـ QR (هنا نضع الرابط الذي سيفتح عند مسح الكود)
        // يمكنك تغيير الرابط ليكون رابط صفحة العداد في التطبيق أو الموقع
        $qrContent = "Meter ID: " . $meter->id . " - Number: " . $meter->meter_number;
        
        $qrImage = QrCode::format('svg')->size(300)->generate($qrContent);

        // 3. حفظ الصورة في التخزين (storage/app/public/qrcodes)
        Storage::disk('public')->put($path, $qrImage);

        // 4. تحديث سجل العداد في قاعدة البيانات بمسار الصورة (بدون إطلاق Events لتجنب التكرار)
        $meter->timestamps = false; // عدم تحديث تاريخ التحديث
        $meter->qr_code = $path;
        $meter->save();
    }
}