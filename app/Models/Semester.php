<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
        'name',
        'start_date',
        'end_date',




        "business_id",
        "created_by"
    ];

    protected $casts = [];
}
