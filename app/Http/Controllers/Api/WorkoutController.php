<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WorkoutController extends Controller
{
    // GET: Ambil Semua Data Latihan beserta Kategorinya
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();// Ambil data user dari token
        $workouts = Workout::with('category')->latest()->get();

        // Looping semua data buat nambahin status is_favorite
        $workouts->map(function ($workout) use ($user) {
            if ($user) {
                // Cek ke tabel favorites apakah user ini pernah nge-like workout ini
                $workout->is_favorite = \DB::table('favorites')
                    ->where('workout_id', $workout->id)
                    ->where('user_id', $user->id)
                    ->exists();
            } else {
                $workout->is_favorite = false;
            }
            return $workout;
        });

        return response()->json([
            'status' => true,
            'message' => 'List Data Latihan (Workout)',
            'data' => $workouts
        ], 200);
    }
    // POST: Tambah Latihan Baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id'      => 'required|exists:categories,id', 
            'name'             => 'required|string|max:255',
            'duration_minutes' => 'nullable|integer',
            'calories_burned'  => 'nullable|integer',
            'description'      => 'nullable|string',
            'link_yt'          => 'nullable|string|url|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $workout = Workout::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Latihan berhasil ditambahkan',
            'data' => $workout
        ], 201);
    }

    // GET: Detail 1 Latihan
    public function show(Request $request, $id)
    {
        $user = auth('sanctum')->user(); // Ambil data user dari token
        
        // Cari data beserta kategorinya
        $workout = Workout::with('category')->find($id);

        if (!$workout) {
            return response()->json([
                'status' => false,
                'message' => 'Latihan tidak ditemukan',
                'data' => null
            ], 404);
        }

        // Tambahin pengecekan is_favorite khusus untuk 1 workout ini
        if ($user) {
            $workout->is_favorite = \DB::table('favorites')
                ->where('workout_id', $workout->id)
                ->where('user_id', $user->id)
                ->exists();
        } else {
            $workout->is_favorite = false;
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail Latihan',
            'data' => $workout
        ], 200);
    }

    // PUT: Update Latihan
    public function update(Request $request, $id)
    {
        $workout = Workout::find($id);

        if (!$workout) {
            return response()->json([
                'status' => false,
                'message' => 'Latihan tidak ditemukan',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id'      => 'required|exists:categories,id',
            'name'             => 'required|string|max:255',
            'duration_minutes' => 'nullable|integer',
            'calories_burned'  => 'nullable|integer',
            'description'      => 'nullable|string',
            'link_yt'          => 'nullable|string|url|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update data
        $workout->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Latihan berhasil diupdate',
            'data' => $workout
        ], 200);
    }

    // DELETE: Hapus Latihan
    public function destroy($id)
    {
        $workout = Workout::find($id);

        if (!$workout) {
            return response()->json([
                'status' => false,
                'message' => 'Latihan tidak ditemukan',
                'data' => null
            ], 404);
        }

        $workout->delete();

        return response()->json([
            'status' => true,
            'message' => 'Latihan berhasil dihapus',
            'data' => null
        ], 200);
    }

    // POST: Join workout class
    public function join(Request $request, $id)
    {
        $workout = Workout::find($id);

        if (!$workout) {
            return response()->json([
                'status' => false,
                'message' => 'Latihan tidak ditemukan',
                'data' => null
            ], 404);
        }

        $request->user()->workoutClasses()->syncWithoutDetaching([$workout->id]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil join workout class',
            'data' => $workout
        ], 200);
    }
}