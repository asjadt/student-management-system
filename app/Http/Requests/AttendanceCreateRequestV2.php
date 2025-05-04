<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCreateRequestV2 extends FormRequest
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
        'attendance_date' => 'required|date',
        'students' => 'required|array|min:1',
        'students.*.id' => 'required|exists:students,id',
        'students.*.subjects' => 'required|array|min:1',
        'students.*.subjects.*.class_routine_id' => 'nullable|numeric|exists:class_routines,id',
        'students.*.subjects.*.day_of_week' => 'nullable|integer|between:1,7',
        'students.*.subjects.*.start_time' => 'nullable|date_format:H:i',
        'students.*.subjects.*.end_time' => 'nullable|date_format:H:i|after:students.*.subjects.*.start_time',
        'students.*.subjects.*.room_number' => 'nullable|string|max:255',
        'students.*.subjects.*.subject_id' => 'nullable|numeric|exists:subjects,id',
        'students.*.subjects.*.teacher_id' => 'nullable|numeric|exists:users,id',
        'students.*.subjects.*.session_id' => 'nullable|numeric|exists:sessions,id',
        'students.*.subjects.*.course_id' => 'nullable|numeric|exists:course_titles,id',
        'students.*.subjects.*.status' => 'required|in:present,absent,late,excused',
        'students.*.subjects.*.remarks' => 'nullable|string',
    ];
}



}
