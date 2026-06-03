<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Workout;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'review' => 'required|string'
    ]);

    $review = Review::create([
        'user_id' => auth()->id(),
        // workout_id dihapus dari sini
        'rating' => $request->rating,
        'review' => $request->review
    ]);

    return response()->json([
        'success' => true,
        'message' => 'App Review added successfully',
        'data' => $review
    ]);
}

    public function userReviews()
{
    // Ambil 3 review terbaru dari user yang lagi login
    $reviews = \App\Models\Review::where('user_id', auth()->id())
        ->latest()
        ->take(3)
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'User reviews fetched successfully',
        'data' => $reviews
    ]);
}

}