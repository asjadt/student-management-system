<?php



namespace App\Models;


namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
                    'installment_plan_id',
                    'amount_paid',
                    'payment_date',
                    'status',
                    'student_id',

                  "is_active",



        "business_id",
        "created_by"
    ];

    protected $casts = [
                  
  ];





    public function installment_plan()
    {
        return $this->belongsTo(InstallmentPlan::class, 'installment_plan_id','id');
    }




    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id','id');
    }




}

