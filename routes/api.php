<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkoutController;
use App\Http\Controllers\Api\WorkoutHistoryController;
use App\Http\Controllers\Api\WorkoutScheduleController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\TdeeController; // <-- Import Controller Baru Kamu
use App\Http\Controllers\Api\StreakController;

use App\Http\Middleware\IsAdmin;


// ======================================================
// PUBLIC ROUTES
// ======================================================

Route::apiResource('/categories', CategoryController::class)
    ->only(['index', 'show']);

Route::apiResource('/workouts', WorkoutController::class)
    ->only(['index', 'show']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// ======================================================
// AUTH ROUTES
// ======================================================

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

    Route::middleware('auth:sanctum')->group(function () {

        // ... sisa route auth ...
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);

        
    });
});


// ======================================================
// PROTECTED USER ROUTES
// ======================================================

Route::middleware('auth:sanctum')->group(function () {

    // Current User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    // Goals
    Route::post('/profile/onboarding', [ProfileController::class, 'saveOnboarding']);
    // Schedules
    Route::post('/schedules', [ScheduleController::class, 'store']);

    // ======================================================
    // USERS & NUTRIENT TARGETS (TDEE)
    // ======================================================

    Route::get('/users', [UserController::class, 'index'])
        ->middleware(IsAdmin::class);

    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    Route::post('/users/upload-avatar', [UserController::class, 'uploadAvatar']);
    
    // Route Baru Kamu untuk Hitung Kalori & Nutrisi:
    Route::post('/users/calculate-tdee', [TdeeController::class, 'calculateTdee']);

    // ======================================================
    // WORKOUT
    // ======================================================

    // Route::get('/workout', [WorkoutController::class, 'index']);
    Route::get('/workout/{id}', [WorkoutController::class, 'show']);

    // ======================================================
    // WORKOUT SCHEDULES
    // ======================================================

    Route::get('/workout-schedules', [WorkoutScheduleController::class, 'index']);
    Route::get('/workout-schedules/{id}', [WorkoutScheduleController::class, 'show']);

    // ======================================================
    // WORKOUT HISTORY
    // ======================================================

    Route::get('/workout-history', [WorkoutHistoryController::class, 'index']);
    Route::get('/workout-history/{id}', [WorkoutHistoryController::class, 'show']);
    Route::post('/workout-history', [WorkoutHistoryController::class, 'store']);
    Route::post('/workout-history/{id}', [WorkoutHistoryController::class, 'storeFromWorkout']);
    Route::delete('/workout-history/{id}', [WorkoutHistoryController::class, 'destroy']);

    // ======================================================
    // REMINDERS
    // ======================================================

    Route::get('/reminders', [ReminderController::class, 'index']);
    Route::get('/reminders/{id}', [ReminderController::class, 'show']);
    Route::post('/reminders', [ReminderController::class, 'store']);
    Route::put('/reminders/{id}', [ReminderController::class, 'update']);
    Route::delete('/reminders/{id}', [ReminderController::class, 'destroy']);

    // ======================================================
    // PROGRESS
    // ======================================================

    Route::get('/progress', [ProgressController::class, 'index']);
    Route::post('/progress', [ProgressController::class, 'store']);
    Route::put('/progress/{id}', [ProgressController::class, 'update']);
    Route::delete('/progress/{id}', [ProgressController::class, 'destroy']);

    // ======================================================
    // FAVORITES
    // ======================================================

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{workoutId}', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{workoutId}', [FavoriteController::class, 'destroy']);

    // ======================================================
    // ACHIEVEMENTS & POINTS
    // ======================================================

    Route::get('/achievements', [AchievementController::class, 'index']);
    Route::post('/achievements/claim/{id}', [AchievementController::class, 'claim']);
    
    Route::get('/achievements/points', [AchievementController::class, 'points']);
    Route::get('/achievements/tiers', [AchievementController::class, 'tiers']);
    Route::get('/points/history', [AchievementController::class, 'pointHistory']);

    // ======================================================
    // REFERRAL
    // ======================================================
    
    Route::post('/referrals/redeem', [ReferralController::class, 'redeem']);

    // ======================================================
    // NOTIFICATIONS
    // ======================================================

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'read']);

    // ======================================================
    // REVIEWS & RATINGS
    // ======================================================

    Route::post('/workout-classes/{id}/reviews', [ReviewController::class, 'store']);

    // ======================================================
    // ANALYTICS
    // ======================================================

    Route::get('/analytics/summary', [AnalyticsController::class, 'summary']);

    // ======================================================
    // AI
    // ======================================================

    Route::get('/ai/chat', [AiController::class, 'chatIndex']);
    Route::post('/ai/chat', [AiController::class, 'chatStore']);
    Route::get('/ai/personalization', [AiController::class, 'personalizationIndex']);
    Route::post('/ai/personalization', [AiController::class, 'personalizationStore']);
    Route::delete('/ai/personalization', [AiController::class, 'personalizationDestroy']);

    // Streak
        Route::post('/user/check-in', [StreakController::class, 'checkIn']);
        Route::get('/user/streak', [StreakController::class, 'getStreak']);
});


// ======================================================
// ADMIN ROUTES
// ======================================================

Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {

    Route::post('/workouts', [WorkoutController::class, 'store']);
    Route::put('/workouts/{id}', [WorkoutController::class, 'update']);
    Route::delete('/workouts/{id}', [WorkoutController::class, 'destroy']);

    Route::post('/workout-schedules', [WorkoutScheduleController::class, 'store']);
    Route::put('/workout-schedules/{id}', [WorkoutScheduleController::class, 'update']);
    Route::delete('/workout-schedules/{id}', [WorkoutScheduleController::class, 'destroy']);

    // Categories
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
});


// ======================================================
// ADMIN PREFIX
// ======================================================

Route::middleware(['auth:sanctum', IsAdmin::class])
    ->prefix('admin')
    ->group(function () {

        // Route::apiResource('workouts', WorkoutController::class);
    });