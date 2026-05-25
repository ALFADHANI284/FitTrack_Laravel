<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StreakController extends Controller
{
    // POST /api/user/check-in
    public function checkIn(Request $request)
    {
        $user = auth()->user();

        $today = Carbon::today()->toDateString();

        // Cek apakah user sudah check-in hari ini
        $alreadyCheckIn = CheckIn::where('user_id', $user->id)
            ->where('check_in_date', $today)
            ->exists();

        if ($alreadyCheckIn) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah check-in hari ini'
            ], 400);
        }

        // Simpan check-in
        CheckIn::create([
            'user_id' => $user->id,
            'check_in_date' => $today
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil'
        ]);
    }

    // GET /api/user/streak
    public function getStreak()
{
    $user = auth()->user();

    $checkIns = CheckIn::where('user_id', $user->id)
        ->orderBy('check_in_date', 'desc')
        ->pluck('check_in_date');

    $streak = 0;

    $today = now()->startOfDay();

    foreach ($checkIns as $index => $date) {

        $expectedDate = $today->copy()->subDays($index);

        if (\Carbon\Carbon::parse($date)->equalTo($expectedDate)) {
            $streak++;
        } else {
            break;
        }
    }

    return response()->json([
        'success' => true,
        'streak_days' => $streak
    ]);
}
}