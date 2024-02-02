<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;

class FormatDatesInRequest
{
    public function handle($request, Closure $next)
    {
        $data = $request->all();

        // Format dates in the request data
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Recursive call for nested arrays
                $data[$key] = $this->formatDates($value);
            } elseif ($this->isDateFormat($value)) {
                if ($this->isDateFormat($value, 'd-m-Y')) {
                    // Fix here: use $data instead of $array
                    $data[$key] = Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
                }
            }
        }

        $request->merge($data);

        return $next($request);
    }

    private function isDateFormat($value)
    {
        return is_string($value) && preg_match('/^\d{2}-\d{2}-\d{4}$/', $value);
    }

    private function formatDates($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->formatDates($value);
            } elseif ($this->isDateFormat($value)) {
                if ($this->isDateFormat($value, 'd-m-Y')) {
                    // Fix here: use $array instead of $data
                    $array[$key] = Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
                }
            }
        }

        return $array;
    }
}
