<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $claimedIds = $user->achievements()->pluck('achievements.id')->toArray();

        $achievements = Achievement::all()->map(function ($achievement) use ($claimedIds) {
            $achievement->is_claimed = in_array($achievement->id, $claimedIds, true);
            return $achievement;
        });

        return response()->json([
            'status' => true,
            'message' => 'List achievements',
            'data' => $achievements,
        ], 200);
    }

    public function claim(Request $request, $id)
    {
        $achievement = Achievement::find($id);

        if (!$achievement) {
            return response()->json([
                'status' => false,
                'message' => 'Achievement tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $user = $request->user();
        $alreadyClaimed = $user->achievements()->where('achievement_id', $id)->exists();

        if ($alreadyClaimed) {
            return response()->json([
                'status' => false,
                'message' => 'Achievement sudah diklaim',
                'data' => null,
            ], 409);
        }

        $user->achievements()->attach($id, ['claimed_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'Achievement berhasil diklaim',
            'data' => $achievement,
        ], 200);
    }
}
