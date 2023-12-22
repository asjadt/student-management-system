<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserJobHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'company_name',
        'job_title',
        'employment_start_date',
        'employment_end_date',
        'responsibilities',
        'supervisor_name',
        'contact_information',
        'salary',
        'work_location',
        'achievements',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by','id');
    }
    public function getCreatedAtAttribute($value)
    {
        return (new Carbon($value))->format('d-m-Y');
    }
    public function getUpdatedAtAttribute($value)
    {
        return (new Carbon($value))->format('d-m-Y');
    }

    public function getEmploymentStartDateAttribute($value)
    {
        return (new Carbon($value))->format('d-m-Y');
    }
    public function getEmploymentEndDateAttribute($value)
    {
        return (new Carbon($value))->format('d-m-Y');
    }





    public function setEmploymentStartDateAttribute($value)
    {
        $this->attributes['employment_start_date'] = (new Carbon($value))->format('Y-m-d');
    }
    public function setEmploymentEndDateAttribute($value)
    {
        $this->attributes['employment_end_date'] = (new Carbon($value))->format('Y-m-d');
    }
}