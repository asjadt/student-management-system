<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    use HasFactory;

    // Make the columns fillable
    protected $fillable = [
        'business_id',
        'online_student_status_id',
        'student_data_fields',
    ];

    // Cast these attributes to arrays
    protected $casts = [
        'student_data_fields' => 'array',


    ];

    // Optionally, you can define the relationship to StudentStatus if needed
    public function studentStatus()
    {
        return $this->belongsTo(StudentStatus::class, 'online_student_status_id');
    }
}
