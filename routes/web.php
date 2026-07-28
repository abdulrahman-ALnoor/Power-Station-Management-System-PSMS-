<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CompanyProfileController;

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

