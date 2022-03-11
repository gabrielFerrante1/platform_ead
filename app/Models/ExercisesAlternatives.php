<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExercisesAlternatives extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $table = 'exercises_alternatives';
}
