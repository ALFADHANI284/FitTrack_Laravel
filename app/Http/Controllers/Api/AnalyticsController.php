<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Workout;
use App\Models\Favorite;
use App\Models\Review;
use App\Http\Controllers\Controller;

class AnalyticsController extends Controller
{
    public function summary()
    {
        return response()->json([
            'success' => true,

            'data' => [

                'total_users' => User::count(),

                'total_workouts' => Workout::count(),

                'total_favorites' => Favorite::count(),

                'total_reviews' => Review::count(),

                'average_rating' => round(
                    Review::avg('rating'),
                    1
                )
            ]
        ]);
    }
}