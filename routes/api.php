<?php

use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MeterReadingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\EquipmentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// مسار تفاصيل العميل المطابق للواجهة
Route::get('/customers/{id}/details', [CustomerController::class, 'customerDetails']);

// =========================================================================
// 1. مسارات الإحصائيات والدوال الخاصة ( لمنع التعارض)
// =========================================================================
Route::get('customers/stats', [CustomerController::class, 'stats']);
Route::get('company-profiles/stats', [CompanyProfileController::class, 'stats']);

// مسارات العملاء للـ API (تنشئ get, post, show, update, delete تلقائياً)
Route::apiResource('customers', CustomerController::class);

// مسارات ملفات الشركات للـ API
Route::apiResource('company-profiles', CompanyProfileController::class);


Route::get('/invoices/stats', [InvoiceController::class, 'stats']);
Route::get('/meter-readings/stats', [MeterReadingController::class, 'stats']);


Route::apiResource('/invoices' , InvoiceController::class);
Route::apiResource('/meter-readings' , MeterReadingController::class);


// مسارات المعدات
Route::get('/equipment/stats', [EquipmentController::class, 'stats']);
Route::get('/equipment', [EquipmentController::class, 'index']); // إذا لم يكن معرفاً مسبقاً