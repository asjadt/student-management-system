<?php

namespace App\Http\Utils;


use Exception;

use Illuminate\Database\Eloquent\Builder;

trait DefaultQueryScopesTrait
{
    public function getIsActiveAttribute($value)
    {
        return $value;
        $is_active = $value;
        $user = auth()->user();

        if(!empty($user)) {
            if (empty($user->business_id)) {
                if (empty($this->business_id) && $this->is_default == 1) {
                    if (!$user->hasRole("superadmin")) {
                        $disabled = $this->disabled()->where([
                            "created_by" => $user->id
                        ])
                            ->first();
                        if ($disabled) {
                            $is_active = 0;
                        }
                    }
                }
            } else {
                if (empty($this->business_id)) {
                    $disabled = $this->disabled()->where([
                        "business_id" => $user->business_id
                    ])
                        ->first();
                    if ($disabled) {
                        $is_active = 0;
                    }
                }
            }
        }





        return $is_active;
    }



    public function getIsDefaultAttribute($value)
    {

        return $value;
        $is_default = $value;
        $user = auth()->user();

        if (!empty($user)) {

            if (!empty($user->business_id)) {
                if (empty($this->business_id) || $user->business_id !=  $this->business_id) {
                    $is_default = 1;
                }
            } else if ($user->hasRole("superadmin")) {
                $is_default = 0;
            }
        }

        return $is_default;
    }

    public function scopeForSuperAdmin(Builder $query, $table)
    {
        return $query->where($table . '.business_id', NULL)
                     ->where($table . '.is_default', 1)
                     ->when(request()->filled("is_active"), function ($query) use ($table) {
                         return $query->where($table . '.is_active', request()->boolean('is_active'));
                     });
    }


    public function scopeForNonSuperAdmin(Builder $query, $table, $created_by)
    {
        return $query->where($table . '.business_id', NULL)
        ->where($table . '.is_default', 0)
        ->where($table . '.created_by', $created_by)
        ->when(request()->has('is_active'), function ($query) use ($table) {
            return $query->where($table . '.is_active', request()->boolean('is_active'));
        });
    }


    public function scopeForBusiness(Builder $query, $table, $activeData = false)
    {
        return
            $query->where($table . '.business_id', auth()->user()->business_id)
            ->where($table . '.is_default', 0)
            ->when($activeData || request()->boolean('is_active'), function ($query) use ($table) {
                return $query->where($table . '.is_active', 1);
            });
    }






}
