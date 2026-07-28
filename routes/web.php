<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});






// API routes for user management for 7 functions: index, show, store, update, destroy, show_for_roles
Route::resource('users', UserController::class)
    ->only(['index', 'show']);

// Show user with roles 
Route::get('/users/role/{role}', [UserController::class, 'showByRole']);  





