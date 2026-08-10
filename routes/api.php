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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);
    Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);

    Route::apiResource('/invoices', InvoiceController::class);
    Route::apiResource('/meter-readings', MeterReadingController::class);
});












/*
|--------------------------------------------------------------------------
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

Route::get('/invoices/stats', [InvoiceController::class, 'stats']);
Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);




Route::apiResource('roles' , RoleController::class);

Route::apiResource('notifications' , NotificationController::class);
Route::get('/showByCustomer/{customerId}', [NotificationController::class, 'showByCustomer']);


Route::apiResource('equipment' , EquipmentController::class);
Route::apiResource('service-requests' ,ServiceRequestController::class);





require __DIR__.'/auth.php';

