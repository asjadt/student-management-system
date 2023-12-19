<?php

namespace App\Models;

use Carbon\Carbon;
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
        "created_by"
    ];

    public function parent(){
        return $this->belongsTo(Department::class,'parent_id', 'id');
    }
    public function parentRecursive()
    {
        return $this->belongsTo(Department::class, 'parent_id', 'id')->with('parentRecursive');
    }

    // public function getAllParentIdsAttribute()
    // {
    //     $parentIds = [$this->id]; // Start with the current department's ID

    //     $department = $this;

    //     while ($department->parentRecursive) {
    //         $parentIds[] = $department->parentRecursive->id;
    //         $department = $department->parentRecursive;
    //     }

    //     return array_reverse($parentIds); // Reverse the array to have the top-level parent (father) first
    // }
    // public function getAllParentDataAttribute()
    // {
    //     $parentData = [$this]; // Start with the current department's data

    //     $department = $this;

    //     while ($department->parentRecursive) {
    //         $parentData[] = $department->parentRecursive;
    //         $department = $department->parentRecursive;
    //     }

    //     return array_reverse($parentData); // Reverse the array to have the top-level parent (father) first
    // }
    public function children_recursive()
    {
        return $this->hasMany(Department::class, 'parent_id', 'id')->with(
            [
                "children_recursive" => function ($query) {
                    $query->select('departments.id', 'departments.name'); // Specify the fields for the creator relationship
                },
                "manager" => function ($query) {
                    $query->select('users.id', 'users.first_Name','users.middle_Name',
                    'users.last_Name');
                }

            ]


        )
        ->addSelect([
            'total_users_count' => DepartmentUser::selectRaw('COUNT(*)')
                ->whereColumn('departments.id', 'department_id')
        ]);
      ;
    }
    // public function getAllChildrenDataAttribute()
    // {
    //     $childrenData = collect(); // Start with an empty collection

    //     $this->load('children_recursive');

    //     foreach ($this->children_recursive as $child) {
    //         $childrenData->push($child);

    //         if ($child->children_recursive->isNotEmpty()) {
    //             // If the child has children, recursively get their data
    //             $childrenData = $childrenData->merge($child->getAllChildrenDataAttribute());
    //         }
    //     }

    //     return $childrenData;
    // }


    public function manager(){
        return $this->belongsTo(User::class,'manager_id', 'id');
    }
    public function holidays() {
        return $this->belongsToMany(Holiday::class, 'department_holidays', 'department_id', 'holiday_id');
    }
    public function users() {
        return $this->belongsToMany(User::class, 'department_users', 'department_id', 'user_id');
    }
    public function getTotalUsersCountAttribute()
    {
        return $this->users()->count();
    }

    public function announcements() {
        return $this->belongsToMany(Announcement::class, 'department_announcements', 'department_id', 'announcement_id');
    }
    public function work_shifts() {
        return $this->belongsToMany(WorkShift::class, 'department_work_shifts', 'department_id', 'work_shift_id');
    }
    public function getCreatedAtAttribute($value)
    {
        return (new Carbon($value))->format('d/m/Y');
    }
    public function getUpdatedAtAttribute($value)
    {
        return (new Carbon($value))->format('d/m/Y');
    }
}
