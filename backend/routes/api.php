<?php

use App\Http\Controllers\authController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\SkillsController;
use App\Http\Controllers\UserSkillController;  
use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::prefix('v1')->post('/register', [UserController::class, 'store']); 
Route::post('/login', [authController::class, 'login']);
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::put('/user/{id}', [UserController::class, 'update']); 
    Route::delete('/user/{id}', [UserController::class, 'destroy']);
    Route::get('/admin/index', [UserController::class, 'index']);
    Route::post('/logout', [authController::class, 'logout']);
    
    // Routes for skills added by admin only 
    Route::get('/skills', [SkillsController::class, 'index']); 
    Route::post('/skills', [SkillsController::class, 'store']); 
    Route::get('/skills/{id}', [SkillsController::class, 'show']); 
    Route::put('/skills/{id}', [SkillsController::class, 'update']); 
    Route::delete('/skills/{id}', [SkillsController::class, 'destroy']); 

    // Routes for user skills added by the worker
    Route::get('/user/skills', [UserSkillController::class, 'index']); 
    Route::post('/user/skills', [UserSkillController::class, 'store']); 
    Route::get('/user/skills/{id}', [UserSkillController::class, 'show']); 
    Route::put('/user/skills/{id}', [UserSkillController::class, 'update']); 
    Route::delete('/user/skills/{id}', [UserSkillController::class, 'destroy']); 
});
Route::post('/forgot_password_request', [authController::class, 'forgot_password_request'])->name('api.forgot_password_request');
Route::post('/forgot_password', [authController::class, 'forgot_password'])->name('api.forgot_password');  


