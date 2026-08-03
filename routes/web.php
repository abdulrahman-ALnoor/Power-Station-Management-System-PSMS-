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


// مسارات العملاء والشركات (راوت واحد يغطي كافة الدوال الـ 7 أو الـ 5 تلقائياً)
Route::resource('customers', CustomerController::class);
Route::resource('company-profiles', CompanyProfileController::class);


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
