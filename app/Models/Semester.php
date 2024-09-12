<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        "course_id",

        "business_id",
        "created_by"
    ];

    protected $casts = [];


    public function course() {
        return $this->belongsTo(CourseTitle::class,"course_id","id");
    }

    public function subjects() {
        return $this->belongsToMany(Subject::class,"semester_subjects","semester_id","subject_id");
    }
}
