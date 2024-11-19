<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
                    'name',
                    'description',

                  "is_active",



        "business_id",
        "created_by"
    ];

    protected $casts = [

  ];


  public function courses() {
    return $this->belongsToMany(CourseTitle::class, "course_subjects", "subject_id", "course_id");
}

  public function teachers () {
          return    $this->belongsToMany(User::class,"subject_teachers","subject_id","teacher_id");
  }














}

