<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Workout;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    public function store(Request $request, $id)
    {
        $workout = Workout::find($id);

        if (!$workout) {
            return response()->json([
                'success' => false,
                'message' => 'Workout not found'
            ], 404);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string'
        ]);

        $review = Review::create([
            'user_id' => auth()->id(),
            'workout_id' => $id,
            'rating' => $request->rating,
            'review' => $request->review
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review added',
            'data' => $review
        ]);
    }
}