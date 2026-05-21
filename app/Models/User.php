<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 

#[Fillable(['name', 'email', 'password', 'role', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function workoutClasses()
    {
        return $this->belongsToMany(Workout::class, 'workout_class_memberships')
            ->withTimestamps();
    }

    public function workoutHistories()
    {
        return $this->hasMany(WorkoutHistory::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function progressEntries()
    {
        return $this->hasMany(ProgressEntry::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('claimed_at')
            ->withTimestamps();
    }

    public function aiChats()
    {
        return $this->hasMany(AiChat::class);
    }

    public function aiPersonalizations()
    {
        return $this->hasMany(AiPersonalization::class);
    }
}
