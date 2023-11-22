<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'punch_in_time_tolerance',
        'work_availability_definition',
        'punch_in_out_alert',
        'punch_in_out_interval',
        'alert_area',
        'auto_approval',
        'special_users',
        'special_roles',

        "business_id",
        "is_active",
        "is_default",
        "created_by"
    ];

    public function special_users() {
        return $this->belongsToMany(User::class, 'setting_attendance_special_users', 'setting_attendance_id', 'user_id');
    }
    public function special_roles() {
        return $this->belongsToMany(Role::class, 'setting_attendance_special_roles', 'setting_attendance_id', 'role_id');
    }


}