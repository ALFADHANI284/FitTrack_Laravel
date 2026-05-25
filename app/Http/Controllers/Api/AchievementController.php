<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\PointHistory; // Import model baru
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

    // GET /api/achievements/points
    public function points(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status' => true,
            'message' => 'Berhasil mengambil data poin',
            'data' => [
                'total_points' => $user->points,
                'referral_code' => $user->referral_code
            ]
        ], 200);
    }

    // GET /api/achievements/tiers
    public function tiers(Request $request)
    {
        $user = $request->user();
        
        $availableTiers = [
            ['name' => 'Bronze', 'min_points' => 0, 'max_points' => 499],
            ['name' => 'Silver', 'min_points' => 500, 'max_points' => 999],
            ['name' => 'Gold', 'min_points' => 1000, 'max_points' => 99999],
        ];

        return response()->json([
            'status' => true,
            'message' => 'Berhasil mengambil data tier',
            'data' => [
                'current_tier' => $user->tier,
                'current_points' => $user->points,
                'tier_list' => $availableTiers
            ]
        ], 200);
    }

    // GET /api/points/history
    public function pointHistory(Request $request)
    {
        $history = PointHistory::where('user_id', $request->user()->id)->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Berhasil mengambil histori poin',
            'data' => $history
        ], 200);
    }
}