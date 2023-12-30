<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceMultipleCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'employee_id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $all_manager_department_ids = [];
                    $manager_departments = Department::where("manager_id", auth()->user()->id)->get();
                    foreach ($manager_departments as $manager_department) {
                        $all_manager_department_ids[] = $manager_department->id;
                        $all_manager_department_ids = array_merge($all_manager_department_ids, $manager_department->getAllDescendantIds());
                    }

                  $exists =  User::where(
                    [
                        "users.id" => $value,
                        "users.business_id" => auth()->user()->business_id

                    ])
                    ->whereHas("departments", function($query) use($all_manager_department_ids) {
                        $query->whereIn("departments.id",$all_manager_department_ids);
                     })
                     ->first();

            if (!$exists) {
                $fail("$attribute is invalid.");
                return;
            }



                },
            ],


            'attendance_details' => 'required|array',

            'attendance_details.*.note' => 'nullable|string',
            'attendance_details.*.in_geolocation' => 'nullable|string',
            'attendance_details.*.out_geolocation' => 'nullable|string',

            'attendance_details.*.in_time' => 'nullable|date_format:H:i:s',
            'attendance_details.*.out_time' => [
                'required',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1]; // Extract the index from the attribute name
                    $inTime = request('attendance_details')[$index]['in_time'] ?? false;

                    if ($value !== null && strtotime($value) < strtotime($inTime)) {
                        $fail("$attribute must be after or equal to in_time.");
                    }


                },
            ],

            'attendance_details.*.in_date' => [
                 "required",
                 "date",
                 function ($attribute, $value, $fail) {
                    $exists = Attendance::where('attendances.employee_id', $this->id)
                    ->whereDate('attendances.business_id', '=', auth()->user()->business_id)
                    ->exists();

                if ($exists) {
                    $fail("$attribute is invalid.");
                }

                },

            ],



            'attendance_details.*.does_break_taken' => "required|boolean",



        ];
    }
}
