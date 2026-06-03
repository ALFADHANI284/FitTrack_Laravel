<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workout_id',
        'status',
        'duration_minutes',
        'calories_burned',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // Relasi balik ke tabel users
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi balik ke tabel workouts
    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }
}