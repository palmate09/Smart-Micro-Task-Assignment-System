<?php

use App\Http\Controllers\authController; 
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::post('/register', [UserController::class, 'store']); 
Route::post('/login', [authController::class, 'login']);
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::put('/user/{id}', [UserController::class, 'update']); 
    Route::delete('/user/{id}', [UserController::class, 'destroy']);
    Route::get('/admin/index', [UserController::class, 'index']);
    Route::post('/logout', [authController::class, 'logout']);    
});
Route::post('/forgot_password_request', [authController::class, 'forgot_password_request'])->name('api.forgot_password_request');
Route::post('/forgot_password', [authController::class, 'forgot_password'])->name('api.forgot_password');  


