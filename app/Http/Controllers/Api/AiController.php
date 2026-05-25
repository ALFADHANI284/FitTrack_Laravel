<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChat;
use App\Models\AiPersonalization;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function chatIndex(Request $request)
    {
        $chats = AiChat::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'AI chat history',
            'data' => $chats,
        ], 200);
    }

    public function chatStore(Request $request)
    {
        $validated = $request->validate([
            'role' => 'nullable|string|max:50',
            'message' => 'required|string',
            'meta' => 'nullable|array',
        ]);

        $mainPrompt = "";

        $chat = AiChat::create([
            'user_id' => $request->user()->id,
            'role' => $validated['role'] ?? 'user',
            'message' => $validated['message'],
            'meta' => $validated['meta'] ?? null,
        ]);

        $aiReplyText = 'halo dummy';

        $aiReply = AiChat::create([
            'user_id' => $request->user()->id,
            'role' => 'assistant',
            'message' => $aiReplyText,
            'meta' => [
                'source' => 'dummy',
                'request_chat_id' => $chat->id,
            ],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'AI chat berhasil disimpan',
            'data' => [
                'user_message' => $chat,
                'ai_reply' => $aiReply,
            ],
        ], 201);
    }

    public function personalizationIndex(Request $request)
    {
        $personalization = AiPersonalization::where('user_id', $request->user()->id)->first();

        return response()->json([
            'status' => true,
            'message' => 'AI personalization',
            'data' => $personalization,
        ], 200);
    }

    public function personalizationStore(Request $request)
    {
        $validated = $request->validate([
            'preferences' => 'nullable|array',
            'status' => 'nullable|string|max:50',
        ]);

        $personalization = AiPersonalization::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'preferences' => $validated['preferences'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'AI personalization berhasil disimpan',
            'data' => $personalization,
        ], 201);
    }

    public function personalizationDestroy(Request $request)
    {
        AiPersonalization::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'AI personalization berhasil dihapus',
            'data' => null,
        ], 200);
    }
}
