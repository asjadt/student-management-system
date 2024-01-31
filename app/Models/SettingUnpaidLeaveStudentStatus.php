<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingUnpaidLeaveStudentStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'setting_leave_id', 'student_status_id'
    ];
    protected $table = "paid_leave_student_statuses";
    public function getCreatedAtAttribute($value)
    {
        return (new Carbon($value))->format('d-m-Y');
    }
    public function getUpdatedAtAttribute($value)
    {
        return (new Carbon($value))->format('d-m-Y');
    }
}
