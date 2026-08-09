<?php


use App\Http\Controllers\Api\ConsumptionChargeController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MeterController;
use App\Http\Controllers\Api\MeterReadingController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ServiceRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('meters', MeterController::class);
Route::apiResource( 'consumption-charges', ConsumptionChargeController::class);

Route::get('/invoices/stats', [InvoiceController::class, 'stats']);
Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);


Route::apiResource('/invoices' , InvoiceController::class);
Route::apiResource('/meter-readings' , MeterReadingController::class);

Route::apiResource('/roles' , RoleController::class);

Route::apiResource('/notifications' , NotificationController::class);
Route::get('/showByCustomer/{customerId}', [NotificationController::class, 'showByCustomer']);


Route::apiResource('/service-requests' ,ServiceRequestController::class);


Route::apiResource('/equipment' ,EquipmentController::class);


