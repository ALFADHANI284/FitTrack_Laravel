<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'measured_at',
        'weight_kg',
        'body_fat_percentage',
        'muscle_mass_kg',
        'notes',
    ];

    protected $casts = [
        'measured_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
