<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $reminders = Reminder::where('user_id', $request->user()->id)
            ->orderBy('remind_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'List reminders',
            'data' => $reminders,
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $reminder = Reminder::find($id);

        if (!$reminder || $reminder->user_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'Reminder tidak ditemukan',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail reminder',
            'data' => $reminder,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'remind_at' => 'required|date',
        ]);

        $reminder = Reminder::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'message' => $validated['message'] ?? null,
            'remind_at' => $validated['remind_at'],
            'is_sent' => false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Reminder berhasil dibuat',
            'data' => $reminder,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $reminder = Reminder::find($id);

        if (!$reminder || $reminder->user_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'Reminder tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'message' => 'nullable|string',
            'remind_at' => 'sometimes|date',
            'is_sent' => 'sometimes|boolean',
        ]);

        $reminder->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Reminder berhasil diupdate',
            'data' => $reminder,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $reminder = Reminder::find($id);

        if (!$reminder || $reminder->user_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'Reminder tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $reminder->delete();

        return response()->json([
            'status' => true,
            'message' => 'Reminder berhasil dihapus',
            'data' => null,
        ], 200);
    }
}
