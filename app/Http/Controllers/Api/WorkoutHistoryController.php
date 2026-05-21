<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkoutHistory;
use App\Models\Workout;
use Illuminate\Http\Request;

class WorkoutHistoryController extends Controller
{
    public function index(Request $request)
    {
        $histories = WorkoutHistory::with('workout')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'List workout history',
            'data' => $histories,
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $history = WorkoutHistory::with('workout')->find($id);

        if (!$history || $history->user_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'History tidak ditemukan',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail history',
            'data' => $history,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'workout_id' => 'required|exists:workouts,id',
            'status' => 'nullable|string|max:50',
            'duration_minutes' => 'nullable|integer|min:1',
            'calories_burned' => 'nullable|integer|min:1',
            'completed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $history = WorkoutHistory::create([
            'user_id' => $request->user()->id,
            'workout_id' => $validated['workout_id'],
            'status' => $validated['status'] ?? 'completed',
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'calories_burned' => $validated['calories_burned'] ?? null,
            'completed_at' => $validated['completed_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Workout history berhasil disimpan',
            'data' => $history,
        ], 201);
    }

    public function storeFromWorkout(Request $request, $workoutId)
    {
        $workout = Workout::find($workoutId);

        if (!$workout) {
            return response()->json([
                'status' => false,
                'message' => 'Workout tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'nullable|string|max:50',
            'duration_minutes' => 'nullable|integer|min:1',
            'calories_burned' => 'nullable|integer|min:1',
            'completed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $history = WorkoutHistory::create([
            'user_id' => $request->user()->id,
            'workout_id' => $workoutId,
            'status' => $validated['status'] ?? 'completed',
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'calories_burned' => $validated['calories_burned'] ?? null,
            'completed_at' => $validated['completed_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Workout history berhasil disimpan',
            'data' => $history,
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $history = WorkoutHistory::find($id);

        if (!$history || $history->user_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'History tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $history->delete();

        return response()->json([
            'status' => true,
            'message' => 'History berhasil dihapus',
            'data' => null,
        ], 200);
    }
}
