<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CompanyProfileController;

Route::get('/', function () {
    return view('welcome');
});
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