<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPlan extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
                    'name',
                    'student_id',
                    'course_id',
                    'number_of_installments',
                    'installment_amount',
                    'start_date',
                    'end_date',

                  "is_active",



        "business_id",
        "created_by"
    ];

    protected $casts = [

  ];





    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id','id');
    }




    public function course_title()
    {
        return $this->belongsTo(CourseTitle::class, 'course_id','id');
    }




















}

