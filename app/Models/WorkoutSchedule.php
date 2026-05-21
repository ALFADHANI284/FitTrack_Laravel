<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'workout_id',
        'title',
        'description',
        'scheduled_at',
        'duration_minutes',
        'location',
        'capacity',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }
}
