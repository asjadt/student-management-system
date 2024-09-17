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




















}

