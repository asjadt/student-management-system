<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCourseSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_session_course_id',
        'subject_id'
    ];

    public function subject() {
        return $this->hasOne(Subject::class, "id", "subject_id");
    }

}
