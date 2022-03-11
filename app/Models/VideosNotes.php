<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideosNotes extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $table = 'videos_notes';
}
