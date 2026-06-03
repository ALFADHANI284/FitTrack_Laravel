<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\WorkoutHistory;
use Illuminate\Support\Facades\DB; 
class StreakController extends Controller
{
    // GET /api/user/streak
    public function getStreak()
    {
        $user = auth()->user();

        // 1. Ambil tanggal unik dari riwayat workout user berdasarkan completed_at
        $workoutDates = WorkoutHistory::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->select(DB::raw('DATE(completed_at) as date')) 
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->pluck('date');

        $streak = 0;
        
        if ($workoutDates->isEmpty()) {
            return response()->json([
                'success' => true,
                'streak_days' => 0,
                'has_workout_today' => false
            ]);
        }

        $today = now()->startOfDay();
        
        // 2. Cek apakah user udah workout hari ini
        $hasWorkoutToday = $workoutDates->contains($today->toDateString());

        // 3. Mulai hitung mundur. Kalau hari ini belum, cek dari kemarin
        $dateToCheck = $hasWorkoutToday ? $today : now()->subDay()->startOfDay();

        foreach ($workoutDates as $dateString) {
            $parsedDate = Carbon::parse($dateString)->startOfDay();

            // Kalau cocok, tambah streak dan mundur 1 hari
            if ($parsedDate->equalTo($dateToCheck)) {
                $streak++;
                $dateToCheck->subDay(); 
            } else {
                break; // Bolong, streak putus
            }
        }

        return response()->json([
            'success' => true,
            'streak_days' => $streak,
            'has_workout_today' => $hasWorkoutToday
        ]);
    }
}