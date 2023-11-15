<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingPaidLeaveEmploymentStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'setting_leave_id', 'employment_status_id'
    ];
    protected $table = "paid_leave_employment_statuses";
}
