<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'nationality',
        'passport_number',
        'school_id',
        'date_of_birth',
        'course_start_date',
        'letter_issue_date',
        'student_status_id',
        'attachments',
        'is_active',
        'business_id',
        'created_by',
    ];

    protected $casts = [
        'attachments' => 'json',
    ];

    // Relationships
    public function student_status()
    {
        return $this->belongsTo(StudentStatus::class, 'student_status_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }







    // public function getDate_Of_BirthAttribute($value)
    // {
    //     if($value) {
    //         return (new Carbon($value))->format('d-m-Y');
    //     }
    //     return "";

    // }

    // public function getCourseStartDateAttribute($value)
    // {
    //     if($value) {
    //         return (new Carbon($value))->format('d-m-Y');
    //     }
    //     return "";

    // }
    // public function getLetterIssueDateAttribute($value)
    // {
    //     if($value) {
    //         return (new Carbon($value))->format('d-m-Y');
    //     }
    //     return "";
    // }


    // public function getCreatedAtAttribute($value)
    // {
    //     return (new Carbon($value))->format('d-m-Y');
    // }
    // public function getUpdatedAtAttribute($value)
    // {
    //     return (new Carbon($value))->format('d-m-Y');
    // }









}
