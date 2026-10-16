<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/stats', [TaskController::class, 'allData']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks/{id}/progress', [TaskController::class, 'updateProgress']);
    Route::put('/profile/update', [UserController::class, 'updateProfile']);
    Route::get('/profile/me', [UserController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});