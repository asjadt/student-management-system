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
        'break_start_date',
        'break_end_date',

        "business_id",
        "created_by"
    ];

    protected $casts = [];




    public function courses() {
        return $this->belongsToMany(CourseTitle::class,"semester_courses","semester_id","course_id");
    }


}
