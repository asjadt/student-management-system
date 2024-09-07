<?php


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabledAwardingBody extends Model
{
    use HasFactory;
    protected $fillable = [
        'awarding_body_id',
        'business_id',
        'created_by',
    ];

}

