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
        'description',
        "awarding_body_id",
        "is_active",
        "is_default",
        "business_id",
        "created_by"
    ];


    public function sessions()
    {
        return   $this->belongsToMany(Session::class, "session_courses", "course_id", "session_id");
    }


    public function awarding_body()
    {
        return $this->belongsTo(AwardingBody::class, 'awarding_body_id', 'id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, "course_subjects", "course_id", "subject_id");
    }

    public function class_routines()
    {
        return $this->hasMany(ClassRoutine::class, 'course_id', 'id');
    }

    public function teachers()
    {
        return $this->belongsToMany(
            User::class,           // Related model
            'class_routines',      // Pivot table
            'course_id',           // Foreign key on pivot referencing course_titles
            'teacher_id'           // Foreign key on pivot referencing users
        );
    }


    // FILTER
    public function scopeFilter($query)
    {
        return $query->where([
            "business_id" => auth()->user()->business_id
        ])

            // FILTER BY STUDENT ID
            ->when(filled(request()->query('student_id')), function ($query) {
                $query->whereHas('sessions.students', function ($q) {
                    $q->where('students.id', request()->query('student_id'));
                });
            })

            // If the user has a business_id, apply additional business-specific filtering
            ->when(!empty(auth()->user()->business_id), function ($query) {
                // Use a custom scope 'forBusiness' to apply business-specific logic to the query
                $query->forBusiness('course_titles');
            })

            // If a search key is provided in the request, filter the query based on the search key
            ->when(!empty(request()->search_key), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->search_key;
                    // Search for the term in the name and description columns of course titles
                    $query->where("course_titles.name", "like", "%" . $term . "%")
                        ->orWhere("course_titles.description", "like", "%" . $term . "%");
                });
            })

            // If an awarding_body_id is provided in the request, filter the query by it
            ->when(!empty(request()->awarding_body_id), function ($query) {
                return $query->where('course_titles.awarding_body_id', request()->awarding_body_id);
            })

            ->when(!empty(request()->session_ids), function ($query) {
                return $query->whereHas('sessions', function ($query) {
                    $session_ids = explode(',', request()->session_ids);
                    $query->whereIn("sessions.id", $session_ids);
                });
            })
            // If a start date is provided in the request, filter the query for records created after it
            ->when(!empty(request()->start_date), function ($query) {
                return $query->where('course_titles.created_at', ">=", request()->start_date);
            })

            // If an end date is provided in the request, filter the query for records created before it
            ->when(!empty(request()->end_date), function ($query) {
                return $query->where('course_titles.created_at', "<=", (request()->end_date . ' 23:59:59'));
            });
    }
}
