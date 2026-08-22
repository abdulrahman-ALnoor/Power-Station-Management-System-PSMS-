<?php
//use App\Http\Controllers\Auth\AuthController;

use App\Http\Controllers\Api\MeterReadingController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ConsumptionChargeController;
use App\Http\Controllers\Api\MeterController;
use App\Http\Controllers\Api\CompanyProfileController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AuthController;

use Illuminate\Http\Request;

// مسار لتسجيل قراءة العداد عبر الـ QR Code
Route::post('/reader/meters/{meter}/record-reading', [MeterReadingController::class, 'storeReadingByQr'])->middleware('auth:sanctum');

// مسار تسجيل الدخول (غير محمي)
Route::post('/login', [AuthController::class, 'login']);

// جميع المسارات داخل هذه المجموعة تتطلب توكن (محمية بـ Middleware)
Route::middleware('auth:sanctum')->group(function () {
    // مسار تجريبي للتأكد من عمل التوكن
    Route::get('/reader/profile', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });

    // بيانات المستخدم الحالي (دور + صلاحيات) — تُستخدم من الفرونت عند تحديث الصفحة
    Route::get('/me', [AuthController::class, 'me']);

    // ==========================================
    // 1. مسارات الواجهة الأولى (لوحة القارئ الرئيسية)
    // ==========================================
    Route::get('/reader/dashboard/stats', [MeterReadingController::class, 'readerDashboardStats']);
    Route::get('/reader/dashboard/progress', [MeterReadingController::class, 'readerReadingsProgress']);
    Route::get('/reader/dashboard/consumption', [MeterReadingController::class, 'readerConsumptionStats']);
    Route::get('/reader/dashboard/latest-readings', [MeterReadingController::class, 'readerLatestReadings']);

    // ==========================================
    // 2. مسارات الواجهة الثانية (قراءات القارئ والفلترة)
    // ==========================================
    Route::get('/reader/readings', [MeterReadingController::class, 'readerIndex']);
    Route::get('/reader/readings/stats', [MeterReadingController::class, 'readerReadingsStats']);
    // ملاحظة: كان فيه route ثانٍ بنفس الوظيفة (/reader/index) بآخر الملف — دمجناه هنا
    Route::get('/reader/index', [MeterReadingController::class, 'readerIndex']);

    // ==========================================
    // 3. مسارات الواجهة الثالثة (معدات القارئ وطلبات الصيانة)
    // ==========================================
    Route::get('/reader/equipment/my-stats', [EquipmentController::class, 'myEquipmentStats']);
    Route::get('/reader/equipment', [EquipmentController::class, 'myEquipmentList']);
    Route::post('/reader/service-requests', [ServiceRequestController::class, 'storeByReader']);

    // ==========================================
    // 4. مسارات الدفع وإصدار الفواتير (صلاحيات القارئ)
    // ==========================================
    Route::get('/reader/invoices', [InvoiceController::class, 'readerIndex']); // عرض الفواتير
    Route::post('/reader/invoices', [InvoiceController::class, 'store']); // إصدار فواتير الدفع

});

Route::middleware(['auth:sanctum'])->group(function () {

    // =========================================================================
    // قاعدة مهمة: أي رابط ثابت (stats, export, monthly-revenue...) لازم يُسجَّل
    // قبل أي apiResource/Route لنفس المورد فيه {id}، وإلا {id} يلتقط الكلمة
    // الثابتة على إنها معرّف قبل ما توصل لسطرها الصريح (يسبب 404 خاطئ).
    // =========================================================================

    // ----- روابط ثابتة لقراءات العدادات (قبل apiResource) -----
    Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);

    // ----- روابط ثابتة للفواتير (قبل apiResource) -----
    // كل هالروابط تكشف بيانات مالية حساسة (إيرادات، فواتير، تصدير) —
    // لازم صلاحية invoices.view، مو بس تسجيل دخول.
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/customers/{customerId}/invoices', [InvoiceController::class, 'customerInvoices']);
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'exportPdf']);
    Route::get('/reports/revenue', [InvoiceController::class, 'revenueReport']);
    Route::get('/reports/account-statement', [InvoiceController::class, 'accountStatement']);
        Route::get('/invoices/monthly-revenue', [InvoiceController::class, 'monthlyRevenue']);
        Route::get('/invoices/status-distribution', [InvoiceController::class, 'statusDistribution']);
        Route::get('/invoices/overdue', [InvoiceController::class, 'overdueInvoices']);
        Route::get( '/reports/collections',[InvoiceController::class, 'collectionsReport']);
        Route::get('/invoices/latest-payments', [InvoiceController::class, 'latestPayments']);
        Route::get('/invoices/export-excel', [InvoiceController::class, 'exportExcel']);
        Route::get('/invoices/stats', [InvoiceController::class, 'stats']); // ← نُقل لهنا (كان السبب بمشكلة الـ 404)
    });

    // ----- رابط ثابت للمعدات (قبل apiResource) -----
    Route::get('/equipments/stats', [EquipmentController::class, 'stats']);

    // ===== صلاحيات الفواتير (Spatie) =====
    Route::apiResource('/invoices', InvoiceController::class)
        ->middleware('permission:invoices.view')
        ->only(['index', 'show']);
    Route::apiResource('/invoices', InvoiceController::class)
        ->middleware('permission:invoices.create')
        ->only(['store']);
    Route::apiResource('/invoices', InvoiceController::class)
        ->middleware('permission:invoices.update')
        ->only(['update']);
    Route::apiResource('/invoices', InvoiceController::class)
        ->middleware('permission:invoices.delete')
        ->only(['destroy']);

    // ===== صلاحيات قراءات العدادات (Spatie) =====
    Route::apiResource('/meter-readings', MeterReadingController::class)
        ->middleware('permission:meter-readings.view')
        ->only(['index', 'show']);
    Route::apiResource('/meter-readings', MeterReadingController::class)
        ->middleware('permission:meter-readings.create')
        ->only(['store']);
    Route::apiResource('/meter-readings', MeterReadingController::class)
        ->middleware('permission:meter-readings.update')
        ->only(['update']);
    Route::apiResource('/meter-readings', MeterReadingController::class)
        ->middleware('permission:meter-readings.delete')
        ->only(['destroy']);

    // ----- روابط ثابتة لطلبات الصيانة (قبل apiResource) -----
    Route::get('/service-requests/my-latest', [ServiceRequestController::class, 'myLatestRequests']);
    Route::get('/service-requests/my-performance', [ServiceRequestController::class, 'myMonthlyPerformance']);
    Route::get('/service-requests/my-dashboard', [ServiceRequestController::class, 'myDashboardStats']);
    Route::get('/service-requests/my-status', [ServiceRequestController::class, 'myRequestsStatus']);

    // ----- رابط ثابت للمعدات (قبل apiResource) -----
    Route::get('/equipment/my-stats', [EquipmentController::class, 'myStats']);

    // إضافة/تعديل وصف المعدة (notes) بس — للمهندس والقارئ على معداتهم فقط
    // (3 أجزاء بالمسار، فما يتعارض مع /equipment/{equipment} بغض النظر عن الترتيب)
    Route::patch('/equipment/{equipment}/describe', [EquipmentController::class, 'describe'])
        ->middleware('permission:equipment.describe');

    // ===== صلاحيات المعدات (Spatie) =====
    Route::apiResource('equipment', EquipmentController::class)
        ->middleware('permission:equipment.view')
        ->only(['index', 'show']);
    Route::apiResource('equipment', EquipmentController::class)
        ->middleware('permission:equipment.create')
        ->only(['store']);
    Route::apiResource('equipment', EquipmentController::class)
        ->middleware('permission:equipment.update')
        ->only(['update']);
    Route::apiResource('equipment', EquipmentController::class)
        ->middleware('permission:equipment.delete')
        ->only(['destroy']);

    // ===== صلاحيات طلبات الصيانة (Spatie) =====
    Route::apiResource('service-requests', ServiceRequestController::class)
        ->middleware('permission:service-requests.view')
        ->only(['index', 'show']);
    Route::apiResource('service-requests', ServiceRequestController::class)
        ->middleware('permission:service-requests.create')
        ->only(['store']);
    Route::apiResource('service-requests', ServiceRequestController::class)
        ->middleware('permission:service-requests.update')
        ->only(['update']);
    Route::apiResource('service-requests', ServiceRequestController::class)
        ->middleware('permission:service-requests.delete')
        ->only(['destroy']);

    // تغيير حالة الطلب (3 أجزاء بالمسار، ما يتعارض بغض النظر عن الترتيب)
    Route::patch('/service-requests/{serviceRequest}/change-status', [ServiceRequestController::class, 'changeStatus'])
        ->middleware('permission:service-requests.change-status');

    // توجيه الطلب لمهندس معيّن: admin بس
    Route::patch('/service-requests/{serviceRequest}/assign', [ServiceRequestController::class, 'assignEngineer'])
        ->middleware('permission:service-requests.assign');

});

/*
| User Routes
|--------------------------------------------------------------------------
*/
// تنبيه: هذي المجموعة كانت غير محمية أصلاً بـ auth:sanctum بالملف الأصلي.
// أضفناها هنا داخل auth:sanctum لأن permission middleware يحتاج مستخدم مسجل دخول.

Route::middleware(['auth:sanctum'])->group(function () {

    // ----- رابط ثابت للمستخدمين (قبل resource) -----
    Route::get('/users/stats', [UserController::class, 'stats']);
    Route::get('/users/by-role/{role}', [UserController::class, 'showByRole'])
        ->middleware('permission:users.view');

    // ===== صلاحيات المستخدمين (Spatie) =====
    Route::resource('users', UserController::class)
        ->middleware('permission:users.view')
        ->only(['index', 'show']);
    Route::resource('users', UserController::class)
        ->middleware('permission:users.create')
        ->only(['store']);
    Route::resource('users', UserController::class)
        ->middleware('permission:users.update')
        ->only(['update']);
    Route::resource('users', UserController::class)
        ->middleware('permission:users.delete')
        ->only(['destroy']);

    // ----- رابط ثابت للعدادات (قبل apiResource) -----
    Route::get('meters/stats', [MeterController::class, 'stats']);

    // ===== صلاحيات العدادات (Spatie) =====
    Route::apiResource('meters', MeterController::class)
        ->middleware('permission:meters.view')
        ->only(['index', 'show']);
    Route::apiResource('meters', MeterController::class)
        ->middleware('permission:meters.create')
        ->only(['store']);
    Route::apiResource('meters', MeterController::class)
        ->middleware('permission:meters.update')
        ->only(['update']);
    Route::apiResource('meters', MeterController::class)
        ->middleware('permission:meters.delete')
        ->only(['destroy']);

    // ===== صلاحيات رسوم الاستهلاك (Spatie) =====
    Route::apiResource('consumption-charges', ConsumptionChargeController::class)
        ->middleware('permission:consumption-charges.view')
        ->only(['index', 'show']);
    Route::apiResource('consumption-charges', ConsumptionChargeController::class)
        ->middleware('permission:consumption-charges.create')
        ->only(['store']);
    Route::apiResource('consumption-charges', ConsumptionChargeController::class)
        ->middleware('permission:consumption-charges.update')
        ->only(['update']);
    Route::apiResource('consumption-charges', ConsumptionChargeController::class)
        ->middleware('permission:consumption-charges.delete')
        ->only(['destroy']);

    // مسار تفاصيل العميل (3 أجزاء، ما يتعارض)
    Route::get('/customers/{id}/details', [CustomerController::class, 'customerDetails']);

    // ----- روابط ثابتة (قبل apiResource) -----
    Route::get('customers/stats', [CustomerController::class, 'stats']);
    Route::get('company-profiles/stats', [CompanyProfileController::class, 'stats']);

    // ===== صلاحيات العملاء (Spatie) =====
    Route::apiResource('customers', CustomerController::class)
        ->middleware('permission:customers.view')
        ->only(['index', 'show']);
    Route::apiResource('customers', CustomerController::class)
        ->middleware('permission:customers.create')
        ->only(['store']);
    Route::apiResource('customers', CustomerController::class)
        ->middleware('permission:customers.update')
        ->only(['update']);
    Route::apiResource('customers', CustomerController::class)
        ->middleware('permission:customers.delete')
        ->only(['destroy']);

    // ===== صلاحيات ملفات الشركات (Spatie) =====
    Route::apiResource('company-profiles', CompanyProfileController::class)
        ->middleware('permission:company-profiles.view')
        ->only(['index', 'show']);
    Route::apiResource('company-profiles', CompanyProfileController::class)
        ->middleware('permission:company-profiles.create')
        ->only(['store']);
    Route::apiResource('company-profiles', CompanyProfileController::class)
        ->middleware('permission:company-profiles.update')
        ->only(['update']);
    Route::apiResource('company-profiles', CompanyProfileController::class)
        ->middleware('permission:company-profiles.delete')
        ->only(['destroy']);

    // ===== صلاحيات الإشعارات (Spatie) =====
    Route::apiResource('notifications', NotificationController::class)
        ->middleware('permission:notifications.view')
        ->only(['index', 'show']);
    Route::apiResource('notifications', NotificationController::class)
        ->middleware('permission:notifications.create')
        ->only(['store']);
    Route::apiResource('notifications', NotificationController::class)
        ->middleware('permission:notifications.update')
        ->only(['update']);
    Route::apiResource('notifications', NotificationController::class)
        ->middleware('permission:notifications.delete')
        ->only(['destroy']);

    Route::get('/showByCustomer/{customerId}', [NotificationController::class, 'showByCustomer'])
        ->middleware('permission:notifications.view');

    Route::prefix('dashboard')->group(function () {

        Route::get('/', [DashboardController::class, 'index']);

        Route::get('/statistics', [DashboardController::class, 'statistics']);
        Route::get('/monthly-revenue-chart', [DashboardController::class, 'monthlyRevenueChart']);

        Route::get('/electricity-consumption-chart', [DashboardController::class, 'electricityConsumptionChart']);

        Route::get('/equipment-status', [DashboardController::class, 'equipmentStatus']);

        Route::get('/latest-readings', [DashboardController::class, 'latestReadings']);
        Route::get('/latest-service-requests', [DashboardController::class, 'latestServiceRequests']);
        Route::get('/latest-invoices', [DashboardController::class, 'latestInvoices']);

    });

Route::prefix('dashboard')->group(function () {

    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/statistics', [DashboardController::class, 'getStatistics']);
    Route::get('/monthly-revenue-chart', [DashboardController::class, 'monthlyRevenueChart']);

    Route::get('/electricity-consumption-chart', [DashboardController::class, 'electricityConsumptionChart']);

    Route::get('/equipment-status', [DashboardController::class, 'equipmentStatus']);

    Route::get('/latest-readings', [DashboardController::class, 'latestReadings']);
    Route::get('/latest-service-requests', [DashboardController::class, 'latestServiceRequests']);
    Route::get('/latest-invoices', [DashboardController::class, 'latestInvoices']);


});
require __DIR__.'/auth.php';
