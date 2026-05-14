<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    /**
     * Endpoint untuk membuat jadwal baru
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'workout_id' => 'required|exists:workouts,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'schedule_time' => 'required|date_format:Y-m-d H:i:s|after:now',
        ]);

        // Simpan jadwal
        $schedule = Schedule::create([
            'user_id' => $request->user()->id,
            'workout_id' => $request->workout_id, // Simpan ID workout
            'title' => $request->title,
            'description' => $request->description,
            'schedule_time' => $request->schedule_time,
            'is_notified' => false,
        ]);

        $schedule->load('workout'); 

        return response()->json([
            'success' => true,
            'message' => 'Jadwal latihan berhasil dibuat!',
            'data' => $schedule
        ], 201);
    }
}