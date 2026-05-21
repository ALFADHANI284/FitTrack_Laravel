<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkoutSchedule;
use Illuminate\Http\Request;

class WorkoutScheduleController extends Controller
{
    public function index()
    {
        $schedules = WorkoutSchedule::with('workout')
            ->orderBy('scheduled_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'List workout schedules',
            'data' => $schedules,
        ], 200);
    }

    public function show($id)
    {
        $schedule = WorkoutSchedule::with('workout')->find($id);

        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Schedule tidak ditemukan',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail schedule',
            'data' => $schedule,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'workout_id' => 'required|exists:workouts,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $schedule = WorkoutSchedule::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Schedule berhasil dibuat',
            'data' => $schedule,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = WorkoutSchedule::find($id);

        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Schedule tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'workout_id' => 'sometimes|exists:workouts,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'sometimes|date',
            'duration_minutes' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $schedule->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Schedule berhasil diupdate',
            'data' => $schedule,
        ], 200);
    }

    public function destroy($id)
    {
        $schedule = WorkoutSchedule::find($id);

        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Schedule tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $schedule->delete();

        return response()->json([
            'status' => true,
            'message' => 'Schedule berhasil dihapus',
            'data' => null,
        ], 200);
    }
}
