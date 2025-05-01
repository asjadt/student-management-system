<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSessionCourse extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_session_id',
        'course_title_id',
        'course_start_date',
        'course_end_date',
        'course_fee',
        'fee_paid',
        'course_duration',
        'course_detail',
        "level"

    ];

    public function course() {
        return $this->hasOne(CourseTitle::class, "id", "course_title_id");
    }

    public function student_session_course_subjects() {
        return $this->hasMany(StudentCourseSubject::class, "student_session_course_id", "id");
    }

    public function subjects() {
        return $this->belongsToMany(Subject::class,"student_course_subjects","student_session_course_id","subject_id");
    }

}
