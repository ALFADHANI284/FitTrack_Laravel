<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'category_id', 'duration_minutes', 'calories_burned', 'description'])]
class Workout extends Model
{
    use HasFactory;
}
