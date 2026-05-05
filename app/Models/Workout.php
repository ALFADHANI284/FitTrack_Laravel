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
        'description'
    ];
}
