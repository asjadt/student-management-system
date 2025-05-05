<?php

namespace App\Rules;

use App\Models\ClassRoutine;
use Illuminate\Contracts\Validation\Rule;

class TeacherAvailable implements Rule
{
    protected $day_of_week;
    protected $start_time;
    protected $end_time;
    protected $id;
    public function __construct($day_of_week, $start_time, $end_time, $id=NULL)
    {
        $this->day_of_week = $day_of_week;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->id = $id;
    }

    public function passes($attribute, $value)
    {
        return true;
        if (empty($this->day_of_week) || empty($this->start_time) || empty($this->end_time) || empty($value)) {
            return true; // Skip validation if any required value is missing
        }
        return !ClassRoutine::
        when(!empty($this->id), function($query) {
                $query->whereNotIn("id",[$this->id]);
        })
        ->where('teacher_id', $value)
            ->where('day_of_week', $this->day_of_week)
            ->where(function ($query) {
                $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                      ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                      ->orWhere(function ($q) {
                          $q->where('start_time', '<=', $this->start_time)
                            ->where('end_time', '>=', $this->end_time);
                      });
            })
            ->exists();
    }

    public function message()
    {
        return 'The selected teacher is not available at the specified time.';
    }
}
