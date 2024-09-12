<?php


namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
                    'first_name',
                    'middle_name',
                    'last_name',
                    'email',
                    'phone',
                    'qualification',
                    'hire_date',

                  "is_active",



        "business_id",
        "created_by"
    ];

    protected $casts = [
















  ];
























}

