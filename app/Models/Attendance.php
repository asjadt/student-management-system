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
        'session_id',
        'course_id',
        'business_id',
        'created_by',
    ];

    public function class_routine()
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

    public function scopeFilterAttendance($query)
    {
        // Chain field filters
        $filterable_fields = [
            'id',
            'class_routine_id',
            'attendance_date',
            'status',
            'student_id',
            'remarks',

            // 'day_of_week',
            // 'start_time',
            // 'end_time',
            // 'room_number'
            'subject_id',
            'teacher_id',
            'session_id',
            'course_id',
            'created_by'
        ];

        foreach ($filterable_fields as $field) {
            $query = $query->when(request()->filled($field), function ($query) use ($field) {
                return $query->where($field, request()->input($field));
            });
        }

        // Chain date range and search filters
        $query = $query
            ->where('business_id', auth()->user()->business_id)
            ->when(request()->filled("start_date"), fn($q) => $q->where('attendance_date', '>=', request()->input("start_date")))
            ->when(request()->filled("end_date"), fn($q) => $q->where('attendance_date', '<=', request()->input("end_date") . ' 23:59:59'))
            ->when(request()->filled("search_key"), function ($q) {
                $search = request()->input("search_key");
                $q->where(function ($sub) use ($search) {
                    $sub->where("room_number", "like", "%{$search}%")
                        ->orWhereHas("teacher", fn($q) => $q->where("name", "like", "%{$search}%"))
                        ->orWhereHas("subject", fn($q) => $q->where("name", "like", "%{$search}%"));
                });
            })
            ->when(
                request()->filled("order_by") && in_array(strtoupper(request()->input("order_by")), ['ASC', 'DESC']),
                fn($q) => $q->orderBy("id", request()->input("order_by")),
                fn($q) => $q->orderBy("id", "DESC") // default fallback
            );

        return $query;
    }
}
