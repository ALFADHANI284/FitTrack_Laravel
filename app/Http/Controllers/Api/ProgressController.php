<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgressEntry;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $entries = ProgressEntry::where('user_id', $request->user()->id)
            ->orderByDesc('measured_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'List progress',
            'data' => $entries,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'measured_at' => 'nullable|date',
            'weight_kg' => 'nullable|numeric',
            'body_fat_percentage' => 'nullable|numeric',
            'muscle_mass_kg' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $entry = ProgressEntry::create(array_merge($validated, [
            'user_id' => $request->user()->id,
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Progress berhasil ditambahkan',
            'data' => $entry,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $entry = ProgressEntry::find($id);

        if (!$entry || $entry->user_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'Progress tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'measured_at' => 'nullable|date',
            'weight_kg' => 'nullable|numeric',
            'body_fat_percentage' => 'nullable|numeric',
            'muscle_mass_kg' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $entry->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Progress berhasil diupdate',
            'data' => $entry,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $entry = ProgressEntry::find($id);

        if (!$entry || $entry->user_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'Progress tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $entry->delete();

        return response()->json([
            'status' => true,
            'message' => 'Progress berhasil dihapus',
            'data' => null,
        ], 200);
    }
}
