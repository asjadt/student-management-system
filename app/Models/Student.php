<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        'first_name',
        'middle_name',
        'last_name',
        "student_id",
        'nationality',
        "course_fee",
        "fee_paid",
        'passport_number',
        'date_of_birth',

        'course_start_date',
        'course_end_date',
        'level',

        'letter_issue_date',
        'student_status_id',
        "course_title_id",
        'attachments',
        'course_duration',
        'course_detail',

        'email',
        'contact_number',
        'sex',
        'address',
        'country',
        'city',
        'postcode',
        'lat',
        'long',

        'emergency_contact_details',
        'previous_education_history',
        'passport_issue_date',
        'passport_expiry_date',
        'place_of_issue',



        'is_active',
        'business_id',
        'created_by',

    ];

    protected $casts = [
        'attachments' => 'json',
        'emergency_contact_details' => 'json',
        'previous_education_history' => 'json',
    ];


    public function course_title() {
        return $this->belongsTo(CourseTitle::class, 'course_title_id', 'id');
    }

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
