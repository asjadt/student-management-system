<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
  use HasFactory, DefaultQueryScopesTrait;
  protected $fillable = [

    'name',
    'start_date',
    'end_date',
    'holiday_dates',
    "is_active",
    "business_id",
    "created_by"
  ];

  protected $casts = [
    'holiday_dates' => 'array',
  ];


  public function courses()
  {
    return   $this->belongsToMany(CourseTitle::class, "session_courses", "session_id", "course_id");
  }


  public function students()
  {
    return   $this->belongsToMany(Student::class, "student_sessions", "session_id", "student_id");
  }


  public function class_routines()
  {
    return $this->hasMany(ClassRoutine::class, "session_id", "id");
  }

  public function scopeFilter($query)
  {
    return $query->where('sessions.business_id', auth()->user()->business_id)
      ->when(request()->filled('student_id'), function ($query) {
        $query->whereHas('students', function ($q) {
          $q->where('students.id', request('student_id'));
        });
      })
      ->when(request()->filled('course_ids'), function ($query) {
        $course_ids = explode(',', request('course_ids'));
        $query->whereHas('courses', function ($q) use ($course_ids) {
          $q->whereIn('courses.id', $course_ids);
        });
      })
      ->when(request()->filled('start_start_date'), fn($q) =>
      $q->where('sessions.start_date', '>=', request('start_start_date')))
      ->when(request()->filled('end_start_date'), fn($q) =>
      $q->where('sessions.start_date', '<=', request('end_start_date') . ' 23:59:59'))
      ->when(request()->filled('start_end_date'), fn($q) =>
      $q->where('sessions.end_date', '>=', request('start_end_date')))
      ->when(request()->filled('end_end_date'), fn($q) =>
      $q->where('sessions.end_date', '<=', request('end_end_date') . ' 23:59:59'))
      ->when(request()->filled('search_key'), function ($query) {
        $term = request('search_key');
        $query->where('name', 'LIKE', "%{$term}%");
      })
      ->when(request()->filled('start_date'), fn($q) =>
      $q->where('sessions.created_at', '>=', request('start_date')))
      ->when(request()->filled('end_date'), fn($q) =>
      $q->where('sessions.created_at', '<=', request('end_date') . ' 23:59:59'));
  }
}
