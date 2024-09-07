<?php



namespace App\Models;



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













                  public function disabled()
      {
          return $this->hasMany(DisabledAwardingBody::class, 'awarding_body_id', 'id');
      }










}

