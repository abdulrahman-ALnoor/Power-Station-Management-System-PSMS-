<?php

use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    
    return view('welcome');
});
Route::get('/invoices', [InvoiceController::class, 'index'])
    ->name('invoices.index');

Route::get('/invoices/{id}', [InvoiceController::class, 'show'])
    ->name('invoices.show');

Route::get('/meter-readings', [MeterReadingController::class, 'index'])
    ->name('meter-readings.index');

Route::get('/meter-readings/{id}', [MeterReadingController::class, 'show'])
    ->name('meter-readings.show');
//(Customers)
// مسار عرض جميع العملاء
Route::get('/customers', [CustomerController::class, 'index'])
    ->name('customers.index');

// مسار عرض عميل محدد عن طريق تمرير المتغير الديناميكي {id}
Route::get('/customers/{id}', [CustomerController::class, 'show'])
    ->name('customers.show');
// ==========================================
//(Company Profiles)
// مسار عرض جميع ملفات الشركات
Route::get('/company-profiles', [CompanyProfileController::class, 'index'])
    ->name('company-profiles.index');

// مسار عرض ملف شركة محدد عن طريق تمرير المتغير الديناميكي {id}
Route::get('/company-profiles/{id}', [CompanyProfileController::class, 'show'])
    ->name('company-profiles.show');




// API routes for user management for 7 functions: index, show, store, update, destroy, show_for_roles
Route::resource('users', UserController::class)
    ->only(['index', 'show']);

// Show user with roles 
Route::get('/users/role/{role}', [UserController::class, 'showByRole']);




Route::resource('roles', RoleController::class)->only(['index', 'show']);


Route::resource('/service-requests', ServiceRequestController::class)->only(['index', 'show']);
Route::get(
    '/service-requests/engineer/{engineerId}',
    [ServiceRequestController::class, 'showByEngineer']
    );
    

Route::resource('/equipment', EquipmentController::class)->only(['index', 'show']);
Route::get(
    '/equipment/user/{userId}',
    [EquipmentController::class, 'showByUser']
);





Route::resource('/notifications', NotificationController::class)->only(['index', 'show']);
Route::get(
    '/notifications/customer/{customerId}',
    [NotificationController::class, 'showByCustomer']
);



