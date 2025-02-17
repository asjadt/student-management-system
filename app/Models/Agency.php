<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;
    protected $fillable = [
        'agency_name',
        'contact_person',
        'email',
        'phone_number',
        'address',
        'commission_rate',
        "is_active",
        'business_id',
        "owner_id"
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id','id');
    }



}
