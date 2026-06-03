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
use App\Http\Controllers\Api\TdeeController; 
use App\Http\Controllers\Api\StreakController;
use App\Http\Middleware\IsAdmin;


// ======================================================
// PUBLIC ROUTES
// ======================================================
Route::apiResource('/categories', CategoryController::class)->only(['index', 'show']); // (Udah)
Route::post('/register', [AuthController::class, 'register']); // (Udah)
Route::post('/login', [AuthController::class, 'login']); // (Udah)1

// ======================================================
// AUTH ROUTES (Udah)
// ======================================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']); // (Udah)
    Route::post('/login', [AuthController::class, 'login']); // (Udah)
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']); // (Udah)
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::put('/profile', [AuthController::class, 'updateProfile']); // (Udah)
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

    // Search Workouts (Udah)
    Route::get('/workouts/search', [WorkoutController::class, 'search']); // (Udah)
    // Workouts Core (Udah)
    Route::apiResource('/workouts', WorkoutController::class)->only(['index', 'show']); // (Udah)
    Route::get('/workout/{id}', [WorkoutController::class, 'show']); // (Udah)

    // Profile & Onboarding (Udah)
    Route::get('/profile', [ProfileController::class, 'show']); // (Udah)
    Route::post('/profile/onboarding', [ProfileController::class, 'saveOnboarding']); // (Udah)

    // USERS & NUTRIENT TARGETS (TDEE)
    Route::get('/users', [UserController::class, 'index'])->middleware(IsAdmin::class); // (Udah)
    Route::get('/users/{id}', [UserController::class, 'show']); // (Udah)
    Route::put('/users/{id}', [UserController::class, 'update']); // (Udah)
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // (Udah)
    Route::post('/users/upload-avatar', [UserController::class, 'uploadAvatar']); // (Udah)
    Route::post('/users/calculate-tdee', [TdeeController::class, 'calculateTdee']); // (Udah)

    // WORKOUT SCHEDULES
    Route::post('/schedules', [ScheduleController::class, 'store']); // (Udah)
    Route::get('/workout-schedules', [WorkoutScheduleController::class, 'index']); // (Udah)
    Route::get('/workout-schedules/{id}', [WorkoutScheduleController::class, 'show']); // (Udah)

    // WORKOUT HISTORY (Ke-2) (Udah)
    Route::get('/workout-history', [WorkoutHistoryController::class, 'index']); // (Udah)
    Route::get('/workout-history/{id}', [WorkoutHistoryController::class, 'show']); // (Udah)
    Route::post('/workout-history', [WorkoutHistoryController::class, 'store']); // (Udah)
    Route::post('/workout-history/{id}', [WorkoutHistoryController::class, 'storeFromWorkout']); // (Udah)
    Route::delete('/workout-history/{id}', [WorkoutHistoryController::class, 'destroy']); // (Udah)

    // REMINDERS (Ke-3)
    Route::get('/reminders', [ReminderController::class, 'index']); // (Udah)
    Route::get('/reminders/{id}', [ReminderController::class, 'show']); // (Udah)
    Route::post('/reminders', [ReminderController::class, 'store']); // (Udah)
    Route::put('/reminders/{id}', [ReminderController::class, 'update']); // (Udah)
    Route::delete('/reminders/{id}', [ReminderController::class, 'destroy']); // (Udah)

    // PROGRESS (Ke-4) (Udah)
    Route::get('/progress', [ProgressController::class, 'index']); // (Udah)
    Route::post('/progress', [ProgressController::class, 'store']); // (Udah)
    Route::put('/progress/{id}', [ProgressController::class, 'update']); // (Udah)
    Route::delete('/progress/{id}', [ProgressController::class, 'destroy']); // (Udah)

    // FAVORITES (Udah)
    Route::get('/favorites', [FavoriteController::class, 'index']); // (Udah)
    Route::post('/favorites/{workoutId}', [FavoriteController::class, 'store']); // (Udah)
    Route::delete('/favorites/{workoutId}', [FavoriteController::class, 'destroy']); // (Udah)

    // ACHIEVEMENTS & POINTS 
    Route::get('/achievements', [AchievementController::class, 'index']); // (Udah)
    Route::post('/achievements/claim/{id}', [AchievementController::class, 'claim']); // (Udah)
    Route::get('/achievements/points', [AchievementController::class, 'points']); // (Udah)
    Route::get('/achievements/tiers', [AchievementController::class, 'tiers']); // (Udah)
    Route::get('/points/history', [AchievementController::class, 'pointHistory']); // (Udah)

    // REFERRAL (Udah)
    Route::post('/referrals/redeem', [ReferralController::class, 'redeem']); // (Udah)

    // NOTIFICATIONS (Ke-6)
    Route::get('/notifications', [NotificationController::class, 'index']); // (Udah)
    Route::put('/notifications/{id}/read', [NotificationController::class, 'read']); // (Udah)

    // REVIEWS & RATINGS (Udah)
    Route::post('/user/reviews', [ReviewController::class, 'store']); // (Udah)
    Route::get('/user/reviews', [ReviewController::class, 'userReviews']); // (Udah)

    // ANALYTICS (Ke-5) (Udah)
    Route::get('/analytics/summary', [AnalyticsController::class, 'summary']); // (Udah)

    // AI (udah)
    Route::get('/ai/chat', [AiController::class, 'chatIndex']); // (Udah)
    Route::post('/ai/chat', [AiController::class, 'chatStore']); // (Udah)
    Route::get('/ai/personalization', [AiController::class, 'personalizationIndex']); // (Udah)
    Route::post('/ai/personalization', [AiController::class, 'personalizationStore']); // (Udah)
    Route::delete('/ai/personalization', [AiController::class, 'personalizationDestroy']); // (Udah)

    // Streak (Ke-7) (Udah)
    Route::get('/user/streak', [StreakController::class, 'getStreak']); // (Udah)
});

// ======================================================
// ADMIN ROUTES (udah)
// ======================================================
Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {
    // Workouts
    Route::post('/workouts', [WorkoutController::class, 'store']); // (Udah)
    Route::put('/workouts/{id}', [WorkoutController::class, 'update']); // (Udah)
    Route::delete('/workouts/{id}', [WorkoutController::class, 'destroy']); // (Udah)

    // Workout schedule
    Route::post('/workout-schedules', [WorkoutScheduleController::class, 'store']);
    Route::put('/workout-schedules/{id}', [WorkoutScheduleController::class, 'update']);
    Route::delete('/workout-schedules/{id}', [WorkoutScheduleController::class, 'destroy']);

    // Categories
    Route::post('/categories', [CategoryController::class, 'store']); // (Udah)
    Route::put('/categories/{id}', [CategoryController::class, 'update']); // (Udah)
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']); // (Udah)
});

// ======================================================
// ADMIN PREFIX
// ======================================================
Route::middleware(['auth:sanctum', IsAdmin::class])
    ->prefix('admin')
    ->group(function () {
        // Route::apiResource('workouts', WorkoutController::class);
    });