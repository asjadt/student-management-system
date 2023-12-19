<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabledDesignation extends Model
{
    use HasFactory;
    protected $fillable = [
        'designation_id',
        'business_id',
        'created_by',
        // Add other fillable columns if needed
    ];
    public function getCreatedAtAttribute($value)
    {
        return (new Carbon($value))->format('d/m/Y');
    }
    public function getUpdatedAtAttribute($value)
    {
        return (new Carbon($value))->format('d/m/Y');
    }
}
