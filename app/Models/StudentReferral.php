<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        "student_id",
        'agency_id',
        'agency_commission',
    ];


}
