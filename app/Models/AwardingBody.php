<?php


namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AwardingBody extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
                    'name',
                    'description',
                    'accreditation_start_date',
                    'accreditation_expiry_date',
                    'logo',

                  "is_active",

      "is_default",


        "business_id",
        "created_by"
    ];

    protected $casts = [

  ];



public function courses() {
    return $this->hasMany(CourseTitle::class,"awarding_body_id","id");
}









   public function disabled()
      {
          return $this->hasMany(DisabledAwardingBody::class, 'awarding_body_id', 'id');
      }










}

