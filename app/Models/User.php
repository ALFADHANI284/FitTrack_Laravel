<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Support\Str; // Tambahan untuk generate referral code

#[Fillable(['name', 'email', 'password', 'role', 'avatar_path', 'points', 'tier', 'referral_code'])]
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

    // Auto-generate referral_code acak saat registrasi akun baru
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            $user->referral_code = strtoupper(Str::random(8));
        });
    }

    // Relasi Baru ke Histori Poin
    public function pointHistories()
    {
        return $this->hasMany(PointHistory::class);
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