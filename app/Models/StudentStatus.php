<?php

namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentStatus extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
        'name',
        'color',
        'description',
        "is_active",
        "is_default",
        "business_id",
        "created_by"
    ];


}
