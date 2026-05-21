<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Workout;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = Favorite::with('workout')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'List favorite workouts',
            'data' => $favorites,
        ], 200);
    }

    public function store(Request $request, $workoutId)
    {
        $workout = Workout::find($workoutId);

        if (!$workout) {
            return response()->json([
                'status' => false,
                'message' => 'Workout tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $favorite = Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'workout_id' => $workoutId,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Favorite berhasil ditambahkan',
            'data' => $favorite,
        ], 201);
    }

    public function destroy(Request $request, $workoutId)
    {
        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('workout_id', $workoutId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'status' => false,
                'message' => 'Favorite tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'status' => true,
            'message' => 'Favorite berhasil dihapus',
            'data' => null,
        ], 200);
    }
}
