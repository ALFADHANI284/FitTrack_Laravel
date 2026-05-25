<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TdeeController extends Controller
{
    // POST /api/users/calculate-tdee
    public function calculateTdee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gender' => 'required|in:male,female',
            'weight' => 'required|numeric|min:10', // dalam kg
            'height' => 'required|numeric|min:50',  // dalam cm
            'age' => 'required|integer|min:1',     // dalam tahun
            'activity_level' => 'required|in:sedentary,lightly_active,moderately_active,very_active',
            'goal' => 'required|in:lose_weight,maintain_weight,gain_weight'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // 1. Hitung BMR (Mifflin-St Jeor Equation)
        if ($request->gender === 'male') {
            $bmr = (10 * $request->weight) + (6.25 * $request->height) - (5 * $request->age) + 5;
        } else {
            $bmr = (10 * $request->weight) + (6.25 * $request->height) - (5 * $request->age) - 161;
        }

        // 2. Tentukan Pengali Aktivitas (TDEE)
        $activityMultipliers = [
            'sedentary' => 1.2,           // Jarang olahraga
            'lightly_active' => 1.375,     // Olahraga 1-3x seminggu
            'moderately_active' => 1.55,   // Olahraga 3-5x seminggu
            'very_active' => 1.725         // Olahraga berat 6-7x seminggu
        ];

        $tdee = $bmr * $activityMultipliers[$request->activity_level];

        // 3. Sesuaikan Kalori Berdasarkan Goal
        if ($request->goal === 'lose_weight') {
            $caloriesTarget = $tdee - 500; // Defisit kalori
        } elseif ($request->goal === 'gain_weight') {
            $caloriesTarget = $tdee + 500; // Surplus kalori
        } else {
            $caloriesTarget = $tdee;       // Maintain
        }

        $caloriesTarget = round($caloriesTarget);

        // 4. Hitung Pembagian Makronutrisi (Rasio Standar Sehat: 40% Carbs, 30% Protein, 30% Fat)
        // Protein: 1 gram = 4 kalori
        // Carbs: 1 gram = 4 kalori
        // Fat: 1 gram = 9 kalori
        $proteinTarget = round(($caloriesTarget * 0.30) / 4);
        $carbsTarget = round(($caloriesTarget * 0.40) / 4);
        $fatTarget = round(($caloriesTarget * 0.30) / 9);

        // 5. Simpan Hasil Perhitungan ke Database User
        $user->update([
            'daily_calories_target' => $caloriesTarget,
            'daily_protein_target' => $proteinTarget,
            'daily_carbs_target' => $carbsTarget,
            'daily_fat_target' => $fatTarget,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Target kalori dan nutrisi berhasil dihitung dan disimpan!',
            'data' => [
                'bmr' => round($bmr),
                'tdee_maintenance' => round($tdee),
                'goal' => $request->goal,
                'daily_targets' => [
                    'calories' => $caloriesTarget . ' kcal',
                    'protein' => $proteinTarget . ' g',
                    'carbs' => $carbsTarget . ' g',
                    'fat' => $fatTarget . ' g'
                ]
            ]
        ], 200);
    }
}