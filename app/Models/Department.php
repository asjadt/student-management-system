<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "location",
        "description",
        "is_active",
        "manager_id",
        "parent_id",
        "business_id",
    ];
    public function holidays() {
        return $this->belongsToMany(Holiday::class, 'department_tenants', 'department_id', 'holiday_id');
    }

}
