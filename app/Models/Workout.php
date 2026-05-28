<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'duration_minutes',
        'calories_burned',
        'description',
        'link_yt',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'workout_class_memberships')
            ->withTimestamps();
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function workoutSchedules()
    {
        return $this->hasMany(WorkoutSchedule::class);
    }
}
