<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursesModules extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $table = 'courses_modules';
}
