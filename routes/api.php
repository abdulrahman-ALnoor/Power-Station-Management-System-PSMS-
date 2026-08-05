<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CompanyProfileController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MeterReadingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\UserController;



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);
    Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);

    Route::apiResource('/invoices', InvoiceController::class);
    Route::apiResource('/invoices',InvoiceController::class);
});



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

require __DIR__.'/auth.php';
