<?php

use App\Http\Controllers\Api\MeterReadingController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ConsumptionChargeController;
use App\Http\Controllers\Api\MeterController;
use App\Http\Controllers\Api\CompanyProfileController;
use App\Http\Controllers\Api\NotificationController;
//use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;

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
    Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);
    Route::get('/invoices/stats', [MeterReadingController::class, 'stats']);
    Route::get('/customers/{customerId}/invoices',[InvoiceController::class, 'customerInvoices']);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'exportPdf']);
    
   /// Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);
    Route::get('/invoices/monthly-revenue', [InvoiceController::class, 'monthlyRevenue']);

    Route::get('/invoices/latest-payments', [InvoiceController::class, 'latestPayments']);
    Route::get('/invoices/export-excel',[InvoiceController::class, 'exportExcel']);
    Route::get('/equipments/stats', [EquipmentController::class, 'stats']);

    // ===== إضافة صلاحيات الفواتير (Spatie) =====
    // index/show يحتاجون صلاحية invoices.view، store يحتاج invoices.create،
    // update يحتاج invoices.update، destroy يحتاج invoices.delete
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

    // ===== إضافة صلاحيات قراءات العدادات (Spatie) =====
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


    Route::get('/service-requests/my-latest', [ServiceRequestController::class, 'myLatestRequests']);
    Route::get('/equipment/my-stats', [EquipmentController::class, 'myStats']);

    // إضافة/تعديل وصف المعدة (notes) بس — للمهندس والقارئ على معداتهم فقط
    // (RU محدود حسب المستند، مختلف عن equipment.update الكامل)
    Route::patch('/equipment/{equipment}/describe', [EquipmentController::class, 'describe'])
        ->middleware('permission:equipment.describe');

    // ===== إضافة صلاحيات المعدات (Spatie) =====
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

    Route::get('/service-requests/my-performance', [ServiceRequestController::class, 'myMonthlyPerformance']);
    Route::get('/service-requests/my-dashboard', [ServiceRequestController::class, 'myDashboardStats']);
    Route::get('/service-requests/my-status', [ServiceRequestController::class, 'myRequestsStatus']);

    // ===== إضافة صلاحيات طلبات الصيانة (Spatie) =====
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

    // تغيير حالة الطلب: مخصصة للمهندس (طلبه المسند له بس، عبر ServiceRequestPolicy)
    Route::patch('/service-requests/{serviceRequest}/change-status', [ServiceRequestController::class, 'changeStatus'])
        ->middleware('permission:service-requests.change-status');

    // توجيه الطلب لمهندس معيّن: admin بس (يملك الصلاحية تلقائياً عبر Permission::all())
    Route::patch('/service-requests/{serviceRequest}/assign', [ServiceRequestController::class, 'assignEngineer'])
        ->middleware('permission:service-requests.assign');

});

/*
| User Routes
|--------------------------------------------------------------------------
*/
// تنبيه: هذي المجموعة (users, meters, consumption-charges, customers,
// company-profiles, notifications) كانت غير محمية أصلاً بـ auth:sanctum بالملف الأصلي.
// أضفتها هنا داخل auth:sanctum لأن permission middleware يحتاج مستخدم مسجل دخول
// ($request->user()) حتى يقدر يتحقق من صلاحياته - بدونه رح يعطي خطأ.

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/users/stats', [UserController::class, 'stats']);
Route::resource('users', UserController::class)->only(['index', 'show']);

    // Route::get('/users/role/{role}', [UserController::class, 'showByRole']);

    // ===== إضافة صلاحيات المستخدمين (Spatie) =====
    Route::resource('users', UserController::class)
        ->middleware('permission:users.view')
        ->only(['index', 'show']);

    Route::resource('users', UserController::class)
        ->middleware('permission:users.create')
        ->only(['store']);

    Route::resource('users', UserController::class)
        ->middleware('permission:users.update')
        ->only(['update']);
// =========================================================================
// مسارات الإحصائيات والدوال الخاصة ( لمنع التعارض)
// =========================================================================
Route::get('customers/stats', [CustomerController::class, 'stats']);
Route::get('company-profiles/stats', [CompanyProfileController::class, 'stats']);

    Route::resource('users', UserController::class)
        ->middleware('permission:users.delete')
        ->only(['destroy']);

    Route::get('meters/stats', [MeterController::class, 'stats']);

    // ===== إضافة صلاحيات العدادات (Spatie) =====
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

    // ===== إضافة صلاحيات رسوم الاستهلاك (Spatie) =====
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
Route::get('/invoices/stats', [InvoiceController::class, 'stats']);

    // مسار تفاصيل العميل المطابق للواجهة
    Route::get('/customers/{id}/details', [CustomerController::class, 'customerDetails']);

    // =========================================================================
    // 1. مسارات الإحصائيات والدوال الخاصة ( لمنع التعارض)
    // =========================================================================
    Route::get('customers/stats', [CustomerController::class, 'stats']);
    Route::get('company-profiles/stats', [CompanyProfileController::class, 'stats']);

    // ===== إضافة صلاحيات العملاء (Spatie) =====
    // مسارات العملاء للـ API (تنشئ get, post, show, update, delete تلقائياً)
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

    // ===== إضافة صلاحيات ملفات الشركات (Spatie) =====
    // مسارات ملفات الشركات للـ API
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


    Route::get('/invoices/stats', [InvoiceController::class, 'stats']);

    // Route::apiResource('roles' , RoleController::class);

    // ===== إضافة صلاحيات الإشعارات (Spatie) =====
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

    Route::get('/showByCustomer/{customerId}', [NotificationController::class, 'showByCustomer']);

});

require __DIR__.'/auth.php';
Route::middleware('auth:sanctum')->group(function () {
    // المسار الجديد لقائمة القراءات الخاصة بالقارئ
    Route::get('/reader/index', [MeterReadingController::class, 'readerIndex']);
});

//require __DIR__.'/auth.php';
