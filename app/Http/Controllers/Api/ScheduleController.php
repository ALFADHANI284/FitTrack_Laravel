<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Reminder;
use App\Models\Notification;

class ScheduleController extends Controller
{
    /**
     * Endpoint untuk membuat jadwal baru
     */
    public function index(Request $request)
    {
        // 1. Ambil ID user yang lagi login pakai token
        $userId = $request->user()->id;

        // 2. Ambil data dari tabel schedules
        // with('workout') berfungsi untuk "nge-join" data dari tabel workouts
        // Jadi nama olahraga, kalori, dll otomatis kebawa ke Android
        $schedules = Schedule::with('workout')
            ->where('user_id', $userId)
            ->orderBy('schedule_time', 'asc') // Urutkan dari jadwal terdekat
            ->get();

        // 3. Kembalikan dalam format JSON yang pas dengan ScheduleListResponse lu di Android
        return response()->json([
            'status' => true,
            'message' => 'Berhasil mengambil daftar jadwal beserta relasi workout.',
            'data' => $schedules
        ], 200);
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'workout_id' => 'required|exists:workouts,id',
        'schedule_time' => 'required|date'
    ]);

    $schedule = Schedule::create([
        'user_id' => $request->user()->id,
        'workout_id' => $validated['workout_id'],
        'schedule_time' => $validated['schedule_time']
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Jadwal berhasil dibuat',
        'data' => $schedule
    ], 201);
}

    public function indexAdmin()
    {
        // Tarik semua jadwal, sekalian di-join sama tabel users
        $schedules = Schedule::with('user:id,name,email')
            ->orderBy('schedule_time', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Berhasil mengambil semua jadwal',
            'data' => $schedules
        ], 200);
    }
}