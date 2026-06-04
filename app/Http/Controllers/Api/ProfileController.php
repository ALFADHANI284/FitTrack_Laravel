<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function saveOnboarding(Request $request)
    {
        // 1. Validasi persis seperti TdeeController ditambah kolom motivation
        $validator = Validator::make($request->all(), [
            'motivation'     => 'required|string|max:255',
            'goal'           => 'required|in:lose_weight,maintain_weight,gain_weight',
            'gender'         => 'required|in:male,female',
            'age'            => 'required|integer|min:1',
            'weight'         => 'required|numeric|min:10',
            'height'         => 'required|numeric|min:50',
            'activity_level' => 'required|in:sedentary,lightly_active,moderately_active,very_active',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // 2. RUMUS BMR (Mifflin-St Jeor) & TDEE 
        // Rumus BMR dibedakan berdasarkan gender
        if ($request->gender == 'male') {
            $bmr = (10 * $request->weight) + (6.25 * $request->height) - (5 * $request->age) + 5;
        } else {
            $bmr = (10 * $request->weight) + (6.25 * $request->height) - (5 * $request->age) - 161;
        }

        // Pengali berdasarkan Activity Level
        $activityMultipliers = [
            'sedentary'         => 1.2,
            'lightly_active'    => 1.375,
            'moderately_active' => 1.55,
            'very_active'       => 1.725,
        ];

        $tdee = $bmr * $activityMultipliers[$request->activity_level];

        // 3. Sesuaikan target kalori berdasarkan GOAL
        if ($request->goal == 'lose_weight') {
            $calorieTarget = $tdee - 500; // Defisit kalori
        } else if ($request->goal == 'gain_weight') {
            $calorieTarget = $tdee + 300; // Surplus kalori
        } else {
            $calorieTarget = $tdee; // Maintenance kalori
        }

        // 4. Simpan semua data ke database user yang sedang login
        $user->update([
            'motivation'           => $request->motivation,
            'goal'                 => $request->goal,
            'gender'               => $request->gender,
            'age'                  => $request->age,
            'weight'               => $request->weight,
            'height'               => $request->height,
            'activity_level'       => $request->activity_level,
            'daily_calories_target' => round($calorieTarget), 
        ]);

        return response()->json([
    'status'  => true,
    'message' => 'Data profil berhasil diambil!',
    'data'    => [
        'id'                    => $user->id,
        'name'                  => $user->name,
        'email'                 => $user->email, 
        'weight'                => (float) $user->weight,
        'height'                => (float) $user->height,
        'goal'                  => $user->goal,
        'daily_calories_target' => (int) $user->daily_calories_target,
        'points'                => $user->points ?? 0, 
        'tier'                  => $user->tier ?? 'Bronze'
    ]
], 200);
    }

    public function show(Request $request) {
    $user = $request->user(); // Ambil data user yang lagi login

    return response()->json([
        'status' => 'success',
        'message' => 'Data profil berhasil diambil',
        'data' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'weight' => (float) $user->weight,
            'height' => (float) $user->height,
            'goal' => $user->goal,
            'daily_calories_target' => (int) $user->daily_calories_target, 
            'points' => $user->points ?? 0,
            'tier' => $user->tier ?? 'Bronze'
        ]
    ]);
}
}