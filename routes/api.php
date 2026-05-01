<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\WorkoutController;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public Routes
Route::apiResource('/categories', CategoryController::class);
Route::apiResource('/workouts', WorkoutController::class);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (harus ada Bearer Token di Header)
Route::middleware('auth:sanctum')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout']);

    // Contoh endpoint untuk ambil data user yang sedang login
    Route::get('/user', function (Request $request){
        return $request->user();
    });
});

