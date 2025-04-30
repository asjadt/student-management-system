<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCourse extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'session_id',
        'course_start_date',
        'course_title_id',
        'course_fee',
        'fee_paid',
        'letter_issue_date',
    ];
}
