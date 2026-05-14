<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\WorkoutController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Middleware\IsAdmin; 

// Public Routes
Route::apiResource('/categories', CategoryController::class)->only(['index', 'show']); 
Route::apiResource('/workouts', WorkoutController::class)->only(['index', 'show']);    // DIBATASI! Publik cuma bisa lihat data (GET)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected User Routes (harus ada Bearer Token di Header)
Route::middleware('auth:sanctum')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request){  // Endpoint untuk ambil data user yang sedang login
        return $request->user();
    });
    Route::get('/profile', [ProfileController::class, 'show']); // Endpoint GET /api/profile
    Route::post('/schedules', [ScheduleController::class, 'store']); // Endpoint POST /api/schedules
});

// Protected Admin Routes (harus ada Bearer Token milik Admin)
Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function () {
    Route::apiResource('workouts', WorkoutController::class); // Admin bebas akses (GET, POST, PUT, DELETE)
});