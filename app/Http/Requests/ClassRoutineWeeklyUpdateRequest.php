<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassRoutineWeeklyUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return  bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return  array
     */
    public function rules()
    {
        return [
            'id' => 'required|integer|exists:class_routines,id', // Validate the ID
          'course_data' => [
    'required',
    'array',
],
'course_data.*.course_id' => [
    'required',
    'numeric',
    'exists:course_titles,id',
],
'course_data.*.days' => [
    'required',
    'array',
],
'course_data.*.days.*.day_of_week' => [
    'required',
    'string', // Use string if day_of_week is a name like "Monday"
],
'course_data.*.days.*.start_time' => [
    'required',
    'string', // Ensure valid time format if needed
],
'course_data.*.days.*.end_time' => [
    'required',
    'string', // Ensure valid time format if needed
],
'course_data.*.days.*.room_number' => [
    'required',
    'string',
],
'course_data.*.days.*.subject_id' => [
    'required',
    'numeric',
    'exists:subjects,id',
],

'course_data.*.days.*.teacher_id' => [
    'required',
    'numeric',
    'exists:users,id',
],


            'semester_id' => [
                'nullable',
                'numeric',
                'exists:semesters,id',
            ],
            'session_id' => [
                'nullable',
                'numeric',
                'exists:sessions,id',
            ],

        ];
    }





}
