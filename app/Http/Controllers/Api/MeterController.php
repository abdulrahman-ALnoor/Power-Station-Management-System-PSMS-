<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Meter\StoreMeterRequest;
use App\Http\Requests\Meter\UpdateMeterRequest;
use App\Http\Resources\MeterResource;
use App\Models\Meter;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
// المكتبات الجديدة المضافة للـ QR والـ Storage
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class MeterController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    // This function retrieves all meters with their related data.
    public function index(Request $request)
    {
        try {
            $query = Meter::with(['customer', 'installer', 'creator']);

            // بحث برقم العداد
            if ($request->filled('search')) {
                $query->where('meter_number', 'like', '%' . $request->search . '%');
            }

            // بحث بالمنطقة
            if ($request->filled('location')) {
                $query->where('installation_location', 'like', '%' . $request->location . '%');
            }

            // فلترة بتاريخ الإنشاء (من - إلى)
            if ($request->filled('date_from')) {
                $query->whereDate('installation_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('installation_date', '<=', $request->date_to);
            }

            // فلترة حسب الحالة
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $meters = $query->latest()->paginate(10);

            // ملاحظة: لا نستخدم MeterResource::collection($meters) مباشرة هنا، لأنها
            // بترجع AnonymousResourceCollection، وبما إنها ملفوفة جوا success() بدل
            // ما تترجع من الراوت مباشرة، Laravel ما بيضيف meta/links تلقائياً
            // (هاي الإضافة بتصير بس جوا toResponse()). النتيجة: data كانت بترجع
            // مصفوفة عدادات فلات بدون total/last_page/current_page، فيصير
            // الترقيم بالفرونت اند مستحيل. الحل: نحافظ على كائن الـ paginator
            // نفسه (فيه كل الـ meta) ونحوّل بس العناصر جواه عبر MeterResource،
            // بنفس الأسلوب المتبع بالضبط بـ UserController/EquipmentController.
            $meters->getCollection()->transform(
                fn ($meter) => (new MeterResource($meter))->resolve()
            );

            return $this->success('تم جلب العدادات بنجاح.', $meters);

        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء جلب العدادات: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    // This function is used to create a new meter and generate a QR Code for it.
    public function store(StoreMeterRequest $request)
    {
        try {
            // 1. قاعدة البيانات تتطلب قيمة qr_code عند الإنشاء، لذلك نضع قيمة مؤقتة فريدة.
            // سيتم استبدالها بمسار ملف QR الحقيقي بعد توفر رقم العداد id.
            $meterData = $request->validated();
            $meterData['qr_code'] = 'pending-' . Str::uuid()->toString();
            $meter = Meter::create($meterData);

            // 2. الرابط الخاص بقراءة هذا العداد والذي سيتم توجيه القارئ إليه عند مسح الـ QR
            $scanUrl = url("/api/reader/meters/{$meter->id}/record-reading");

            // 3. توليد الـ QR Code بصيغة SVG
            $qrCode = QrCode::format('svg')->size(300)->generate($scanUrl);

            // 4. تحديد مسار واسم الملف وحفظه في مجلد التخزين
            $qrFileName = 'qrcodes/meter_' . $meter->id . '.svg';
            Storage::disk('public')->put($qrFileName, $qrCode);

            // 5. تحديث العداد لحفظ مسار الـ QR Code في قاعدة البيانات
            $meter->update(['qr_code' => $qrFileName]);

            return $this->success(
                'تم إنشاء العداد وتوليد الـ QR بنجاح.',
                new MeterResource(
                    $meter->load([
                        'customer',
                        'installer',
                        'creator',
                    ])
                ),
                201
            );

        } catch (\Exception $e) {
            return $this->error(
                'حدث خطأ أثناء إنشاء العداد: ' . $e->getMessage()
            );
        }
    }

    /**
     * Display the specified resource.
     */
    // This function retrieves a specific meter with its related data.
    public function show(Meter $meter)
    {
        try {
            $meter->load([
                'customer',
                'installer',
                'creator',
            ]);

            return $this->success(
                'تم جلب بيانات العداد بنجاح.',
                new MeterResource($meter)
            );

        } catch (\Exception $e) {
            return $this->error(
                'حدث خطأ أثناء جلب بيانات العداد: ' . $e->getMessage()
            );
        }
    }

    public function stats()
    {
        try {
            $total = Meter::count();

            $byStatus = Meter::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return $this->success('تم جلب إحصائيات العدادات بنجاح.', [
                'total_meters' => $total,
                'active'       => $byStatus['active'] ?? 0,
                'disconnected' => $byStatus['disconnected'] ?? 0,
                'maintenance'  => $byStatus['maintenance'] ?? 0,
                'damaged'      => $byStatus['damaged'] ?? 0,
            ]);
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء جلب الإحصائيات: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    // This method is not used in API.
    public function create()
    {
        return $this->success(
            'هذه الدالة غير مستخدمة في واجهات API.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    // This function is used to update a specific meter.
    public function update(UpdateMeterRequest $request, Meter $meter)
    {
        try {
            $meter->update($request->validated());

            return $this->success(
                'تم تحديث العداد بنجاح.',
                new MeterResource(
                    $meter->load([
                        'customer',
                        'installer',
                        'creator',
                    ])
                )
            );

        } catch (\Exception $e) {
            return $this->error(
                'حدث خطأ أثناء تحديث العداد: ' . $e->getMessage()
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    // This function is used to delete a specific meter.
    public function destroy(Meter $meter)
    {
        try {
            // حذف الـ QR Code من مجلد التخزين (اختياري، لتوفير المساحة)
            if ($meter->qr_code && Storage::disk('public')->exists($meter->qr_code)) {
                Storage::disk('public')->delete($meter->qr_code);
            }

            $meter->delete();

            return $this->success(
                'تم حذف العداد بنجاح.'
            );

        } catch (\Exception $e) {
            return $this->error(
                'حدث خطأ أثناء حذف العداد: ' . $e->getMessage()
            );
        }
    }
}

