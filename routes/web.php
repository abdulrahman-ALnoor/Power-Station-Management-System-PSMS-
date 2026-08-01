<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\UserController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Invoice Routes
|--------------------------------------------------------------------------
*/

Route::get('/invoices', [InvoiceController::class, 'index'])
    ->name('invoices.index');

Route::get('/invoices/{id}', [InvoiceController::class, 'show'])
    ->name('invoices.show');


/*
|--------------------------------------------------------------------------
| Meter Reading Routes
|--------------------------------------------------------------------------
*/

Route::get('/meter-readings', [MeterReadingController::class, 'index'])
    ->name('meter-readings.index');

Route::get('/meter-readings/{id}', [MeterReadingController::class, 'show'])
    ->name('meter-readings.show');


/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/

Route::get('/customers', [CustomerController::class, 'index'])
    ->name('customers.index');

Route::get('/customers/{id}', [CustomerController::class, 'show'])
    ->name('customers.show');


/*
|--------------------------------------------------------------------------
| Company Profile Routes
|--------------------------------------------------------------------------
*/

Route::get('/company-profiles', [CompanyProfileController::class, 'index'])
    ->name('company-profiles.index');

Route::get('/company-profiles/{id}', [CompanyProfileController::class, 'show'])
    ->name('company-profiles.show');

// ==========================================
// مسارات إضافة، تعديل، وحذف العملاء
// ==========================================
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

// ==========================================
// مسارات إضافة، تعديل، وحذف ملفات الشركات
// ==========================================
Route::post('/company-profiles', [CompanyProfileController::class, 'store'])->name('company-profiles.store');
Route::put('/company-profiles/{id}', [CompanyProfileController::class, 'update'])->name('company-profiles.update');
Route::delete('/company-profiles/{id}', [CompanyProfileController::class, 'destroy'])->name('company-profiles.destroy');

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/

Route::get('/users/role/{role}', [UserController::class, 'showByRole']);

Route::resource('users', UserController::class)
    ->only(['index', 'show']);


/*
|--------------------------------------------------------------------------
| Role Routes
|--------------------------------------------------------------------------
*/

Route::resource('roles', RoleController::class)
    ->only(['index', 'show']);


/*
|--------------------------------------------------------------------------
| Service Request Routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/service-requests/engineer/{engineerId}',
    [ServiceRequestController::class, 'showByEngineer']
);

Route::resource('service-requests', ServiceRequestController::class)
    ->only(['index', 'show']);


/*
|--------------------------------------------------------------------------
| Equipment Routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/equipment/user/{userId}',
    [EquipmentController::class, 'showByUser']
);

Route::resource('equipment', EquipmentController::class)
    ->only(['index', 'show']);


/*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/notifications/customer/{customerId}',
    [NotificationController::class, 'showByCustomer']
);

Route::resource('notifications', NotificationController::class)
    ->only(['index', 'show']);
