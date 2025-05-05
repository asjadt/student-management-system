<?php

namespace App\Rules;

use App\Models\ClassRoutine;
use Illuminate\Contracts\Validation\Rule;

class UniqueSchedulePerSession implements Rule
{
    protected $day;
    protected $start;
    protected $end;
    protected $session_id;
    protected $id;

    public function __construct($day, $start, $end, $session_id,$id)
    {
        $this->day = $day;
        $this->start = $start;
        $this->end = $end;
        $this->session_id = $session_id;
        $this->id = $id;
    }

    public function passes($attribute, $value)
    {
        if (!$this->session_id) {
            return true; // skip if session_id is null
        }

        return !ClassRoutine::where('session_id', $this->session_id)
            ->where('day_of_week', $this->day)
            ->when(!empty($this->id), function($query) {
                $query->whereNotIn("id",[$this->id]);
        })
            ->where(function ($query) {
                $query->whereBetween('start_time', [$this->start, $this->end])
                      ->orWhereBetween('end_time', [$this->start, $this->end])
                      ->orWhere(function ($q) {
                          $q->where('start_time', '<=', $this->start)
                            ->where('end_time', '>=', $this->end);
                      });
            })
            ->exists();
    }

    public function message()
    {
        return 'A schedule already exists for the same session at this time.';
    }
}
