<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles,HasPermissions,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $connection = 'mysql';



    protected $guard_name = "api";
    protected $fillable = [
        'first_Name',
        'last_Name',
        'middle_Name',
        "color_theme_name",
        'emergency_contact_details',
        'gender',
        'is_in_employee',
        'designation_id',
        'student_status_id',
        "course_title_id",
        'joining_date',
        'salary_per_annum',
        'weekly_contractual_hours',
        'minimum_working_days_per_week',
        'overtime_rate',
        'phone',
        'image',
        'address_line_1',
        'address_line_2',
        'country',
        'city',
        'postcode',
        "lat",
        "long",
        'email',
        'password',
        'is_sponsorship_offered',
        "immigration_status",

        'work_location_id',
        "is_active_visa_details",


        'bank_id',
        'sort_code',
        'account_number',
        'account_name',





        'business_id',
        'user_id',
        "created_by",
         'is_active'
    ];





    public function business() {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }


    public function all_users() {
        return $this->hasMany(User::class, 'business_id', 'business_id');
    }




    public function student_status() {
        return $this->belongsTo(StudentStatus::class, 'student_status_id', 'id');
    }

    public function course_title() {
        return $this->belongsTo(CourseTitle::class, 'course_title_id', 'id');
    }


    

    public function scopeWhereHasRecursiveHolidays($query, $today,$depth = 5)
    {
        $query->whereHas('departments', function ($subQuery) use ($today,$depth) {
            $subQuery->whereHasRecursiveHolidays($today,$depth);
        });
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        "site_redirect_token",

        "email_verify_token",
        "email_verify_token_expires",
        "resetPasswordToken",
        "resetPasswordExpires"
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'emergency_contact_details' => 'array',



    ];







    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            // Cascade soft delete to related children


        });

        static::restoring(function ($user) {
            // Cascade restore to related children
            $user->leaves()->withTrashed()->restore();
            $user->attendances()->withTrashed()->restore();
        });

    }


















    public function getCreatedAtAttribute($value)
    {
        return (new Carbon($value))->format('d-m-Y');
    }
    public function getUpdatedAtAttribute($value)
    {
        return (new Carbon($value))->format('d-m-Y');
    }

    public function getJoiningDateAttribute($value)
    {
        return (new Carbon($value))->format('d-m-Y');
    }


}
