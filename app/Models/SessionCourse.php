<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'course_id',
    ];
}
