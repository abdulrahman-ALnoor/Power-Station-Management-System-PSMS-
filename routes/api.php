<?php


use App\Http\Controllers\Api\ConsumptionChargeController;
use App\Http\Controllers\Api\MeterController;

use App\Http\Controllers\Api\CompanyProfileController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MeterReadingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ServiceRequestController;
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

    // ... سيتم إضافة مسارات لوحة التحكم وإدارة القراءات والمعدات هنا لاحقاً ...

});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);
   /// Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);

    Route::apiResource('/invoices', InvoiceController::class);
    Route::apiResource('/meter-readings', MeterReadingController::class);
    Route::get('/service-requests/my-latest', [ServiceRequestController::class, 'myLatestRequests']);
    Route::get('/equipment/my-stats', [EquipmentController::class, 'myStats']);
    Route::apiResource('equipment' , EquipmentController::class);

    Route::get('/service-requests/my-performance', [ServiceRequestController::class, 'myMonthlyPerformance']);
    Route::get('/service-requests/my-dashboard', [ServiceRequestController::class, 'myDashboardStats']);
    Route::get('/service-requests/my-status', [ServiceRequestController::class, 'myRequestsStatus']);
    Route::apiResource('service-requests' ,ServiceRequestController::class);


});



/*
| User Routes
|--------------------------------------------------------------------------
*/
 Route::get('/users/stats', [UserController::class, 'stats']);

Route::get('/users/role/{role}', [UserController::class, 'showByRole']);

Route::resource('users', UserController::class)
    ->only(['index', 'show']);

Route::get('meters/stats', [MeterController::class, 'stats']);
Route::apiResource('meters', MeterController::class);

Route::apiResource( 'consumption-charges', ConsumptionChargeController::class);

// مسار تفاصيل العميل المطابق للواجهة
Route::get('/customers/{id}/details', [CustomerController::class, 'customerDetails']);

// =========================================================================
// 1. مسارات الإحصائيات والدوال الخاصة ( لمنع التعارض)
// =========================================================================
Route::get('customers/stats', [CustomerController::class, 'stats']);
Route::get('company-profiles/stats', [CompanyProfileController::class, 'stats']);

// مسارات العملاء للـ API (تنشئ get, post, show, update, delete تلقائياً)
Route::apiResource('customers', CustomerController::class);

// مسارات ملفات الشركات للـ API
Route::apiResource('company-profiles', CompanyProfileController::class);


Route::get('/invoices/stats', [InvoiceController::class, 'stats']);
//Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);




Route::apiResource('roles' , RoleController::class);

Route::apiResource('notifications' , NotificationController::class);
 Route::get('/showByCustomer/{customerId}', [NotificationController::class, 'showByCustomer']);





require __DIR__.'/auth.php';

