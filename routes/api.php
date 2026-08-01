<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CompanyProfileController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// مسارات العملاء للـ API (تنشئ get, post, show, update, delete تلقائياً)
Route::apiResource('customers', CustomerController::class);

// مسارات ملفات الشركات للـ API
Route::apiResource('company-profiles', CompanyProfileController::class);