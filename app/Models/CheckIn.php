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
}
