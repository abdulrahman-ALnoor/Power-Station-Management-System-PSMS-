<?php

use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MeterReadingController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/invoices/stats', [InvoiceController::class, 'stats']);
Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);


Route::apiResource('/invoices' , InvoiceController::class);
Route::apiResource('/meter-readings' , MeterReadingController::class);

Route::apiResource('/roles' , RoleController::class);