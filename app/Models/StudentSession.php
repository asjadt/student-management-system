<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSession extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'session_id'
    ];

    public function session() {
        return $this->hasOne(Session::class, "id", "session_id");
    }

    public function student_session_courses() {
        return $this->hasMany(StudentSessionCourse::class, "student_session_id", "id");
    }


     public function courses() {
        return $this->belongsToMany(CourseTitle::class,"student_session_courses","student_session_id","course_title_id");
    }


}
