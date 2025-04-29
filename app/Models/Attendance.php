<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_routine_id',
        'student_id',
        'attendance_date',
        'status',
        'remarks',
        'day_of_week',
        'start_time',
        'end_time',
        'room_number',
        'subject_id',
        'teacher_id',
        'semester_id',
        'session_id',
        'course_id',
        'business_id',
        'created_by',
    ];

    public function classRoutine()
    {
        return $this->belongsTo(ClassRoutine::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function course()
    {
        return $this->belongsTo(CourseTitle::class, 'course_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
