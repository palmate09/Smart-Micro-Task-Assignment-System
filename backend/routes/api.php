<?php

use App\Http\Controllers\authController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\SkillsController;
use App\Http\Controllers\UserSkillController;  
use App\Http\Controllers\TaskController; 
use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\notificationController;
use App\Http\Controllers\WorkerController;
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
    Route::get('/workers/top-performers', [WorkerController::class, 'topPerformers']);

    // Routes for skills added by admin only 
    Route::get('/skills', [SkillsController::class, 'index']); 
    Route::post('/skills', [SkillsController::class, 'store']); 
    Route::get('/skills/{id}', [SkillsController::class, 'show']); 
    Route::put('/skills/{id}', [SkillsController::class, 'update']); 
    Route::delete('/skills/{id}', [SkillsController::class, 'destroy']); 

    // Routes for user skills added by the worker
    Route::post('/user/skills', [UserSkillController::class, 'store']); 
    Route::get('/user/skills/{id}', [UserSkillController::class, 'show']); 
    Route::put('/user/skills/{id}', [UserSkillController::class, 'update']); 
    Route::delete('/user/skills/{id}', [UserSkillController::class, 'destroy']);
    
    //Routes for the task created by the company
    Route::get('/tasks', [TaskController::class, 'index']);  
    Route::get('/tasks/search', [TaskController::class, 'search']);
    Route::post('/tasks', [TaskController::class, 'store']); 
    Route::get('/tasks/{id}', [TaskController::class, 'show']); 
    Route::put('/tasks/{id}', [TaskController::class, 'update']); 
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::post('/tasks/auto-assign', [TaskController::class, 'autoAssign']);
    
    //Routes for assigning the task to the worker 
    Route::patch('/tasks/{task}/assign', [TaskAssignmentController::class, 'assignTaskAutomatically']);
    Route::post('/tasks/{task}/consent', [TaskAssignmentController::class, 'respondToTask']); 
    Route::patch('/tasks/{id}/status', [TaskAssignmentController::class, 'updateStatusTask']);
    Route::post('/tasks/{id}/feedback', [TaskAssignmentController::class, 'submitFeedback']);
    Route::patch('/tasks/{id}/reassign', [TaskAssignmentController::class, 'reassign']);
    Route::get('/tasks/{id}/logs', [TaskAssignmentController::class, 'logs']);

    //Routes for notifications
    Route::get('/notifications', [notificationController::class, 'show']);
    Route::patch('/notifications/{id}/read', [notificationController::class, 'markAsRead']);
    Route::get('/notifications/unread-count', [notificationController::class, 'unreadCount']);
    
});
Route::post('/forgot_password_request', [authController::class, 'forgot_password_request'])->name('api.forgot_password_request');
Route::post('/forgot_password', [authController::class, 'forgot_password'])->name('api.forgot_password');  


