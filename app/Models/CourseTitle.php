<?php

namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTitle extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
        'name',
        'color',
        'description',
        "awarding_body_id",
        "is_active",
        "is_default",
        "business_id",
        "created_by"
    ];

    public function disabled()
    {
        return $this->hasMany(DisabledCourseTitle::class, 'course_title_id');
    }
    public function awarding_body()
    {
        return $this->belongsTo(AwardingBody ::class, 'awarding_body_id','id');
    }




}
