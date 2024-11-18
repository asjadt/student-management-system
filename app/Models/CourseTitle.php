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
        'level',
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

   public function subjects() {
        return $this->belongsToMany(Subject::class,"course_subjects","course_id","subject_id");
    }


}
