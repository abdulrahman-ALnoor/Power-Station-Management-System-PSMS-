<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MeterReadingController;

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



