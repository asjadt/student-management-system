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
        'online_form_fields',
        'online_verification_data',
    ];

    // Cast these attributes to arrays
    protected $casts = [
        'online_form_fields' => 'array',
        'online_verification_data' => 'array',
    ];

    // Optionally, you can define the relationship to StudentStatus if needed
    public function studentStatus()
    {
        return $this->belongsTo(StudentStatus::class, 'online_student_status_id');
    }
}
