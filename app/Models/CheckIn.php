<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'student_id',
        'first_name',
        'last_name',
        'phone',
        'comment',
        'check_in_at',
        'check_out_at',
        "business_id"
    ];

    protected $dates = [
        'check_in_at',
        'check_out_at',
    ];

    // Relationship (if needed)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // FILTER DATA
    public function scopeFilter($query)
    {
        if (request()->filled('type')) {
            $query->where('type', request('type'));
        }

        if (request()->filled('student_id')) {
            $query->where('student_id', request('student_id'));
        }

        if (request()->filled('phone')) {
            $query->where('phone', 'like', '%' . request('phone') . '%');
        }

        if (request()->filled('check_in_from')) {
            $query->whereDate('check_in_at', '>=', request('check_in_from'));
        }

        if (request()->filled('check_in_to')) {
            $query->whereDate('check_in_at', '<=', request('check_in_to'));
        }

        // STUDENT FILTER
        if (request()->filled('course_id') && request()->query('type') === 'student') {
            $query->whereHas('student', function ($q) {
                $q->where('course_title_id', request()->query('course_id'));
            });
        }
        if (request()->filled('search_key') && request()->query('type') === 'student') {
            $query->whereHas('student', function ($q) {
                $searchKey = request()->query('search_key');

                $q->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('student_id', 'like', "%$searchKey%")
                        ->orWhere('title', 'like', "%$searchKey%")
                        ->orWhere('first_name', 'like', "%$searchKey%")
                        ->orWhere('last_name', 'like', "%$searchKey%");
                });
            });
        }

        // VISITOR FILTER
        if (request()->filled('type') && request()->query('type') === 'customer') {
            $query->where(function ($q) {
                $searchKey = request()->query('search_key');
                $q->where('first_name', 'like', '%' . $searchKey . '%')
                    ->orWhere('last_name', 'like', '%' . $searchKey . '%');
            });
        }


        // 
        return $query;
    }
}
