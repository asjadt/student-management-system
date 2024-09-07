<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLetterEmailHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_letter_id',
        'sent_at',
        'recipient_email',
        'email_content',
        'status',
        'error_message',
    ];

    // Define the relationship with UserLetter
    public function student_letters()
    {
        return $this->belongsTo(StudentLetter::class, 'student_letter_id');
    }
}
