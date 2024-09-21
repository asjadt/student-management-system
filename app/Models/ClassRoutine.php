<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoutine extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
                    'day_of_week',
                    'start_time',
                    'end_time',
                    'room_number',
                    'subject_id',
                    'teacher_id',
                    'session_id',


                    "is_active",

        "business_id",
        "created_by"
    ];

    protected $casts = [

  ];

  public function teacher()
  {
      return $this->belongsTo(User::class, 'teacher_id','id');
  }



  public function subject()
  {
      return $this->belongsTo(Subject::class, 'subject_id','id');
  }




  public function session()
  {
      return $this->belongsTo(Session::class, 'session_id','id');
  }


















}

