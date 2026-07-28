<?php

use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MeterReadingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::apiResource('/invoices' , InvoiceController::class);
Route::apiResource('/meter-readings' , MeterReadingController::class);