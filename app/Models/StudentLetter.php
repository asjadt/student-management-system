<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLetter extends Model
{
    use HasFactory;
    protected $fillable = [
        'issue_date',
        'status',
        'letter_content',
        'sign_required',
        'attachments',
        "letter_view_required",
        // "letter_viewed",
        'student_id',
        "email_sent",
        "business_id",
        "created_by"
    ];







    protected $casts = [
        'attachments' => 'array',

    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'user_id', 'id');
    }
}

