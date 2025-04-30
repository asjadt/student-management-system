<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCreateRequest extends FormRequest
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
            'class_routine_id' => 'nullable|numeric|exists:class_routines,id',
            'day_of_week' => 'nullable|integer|between:1,7', // Validates that day_of_week is an integer between 1 and 7
            'start_time' => 'nullable|date_format:H:i', // Validates the start_time format (HH:mm)
            'end_time' => 'nullable|date_format:H:i|after:start_time', // Ensures end_time is after start_time
            'room_number' => 'nullable|string|max:255', // Room number as a string with a maximum length
            'teacher_id' => [
                'nullable',
                'numeric',
                "exists:users,id"
            ],
            'subject_id' => 'nullable|numeric|exists:subjects,id', // Ensures the subject_id exists in the subjects table
            'session_id' => 'nullable|numeric|exists:sessions,id', // Ensures session_id exists in the sessions table if provided
            'course_id' => 'nullable|numeric|exists:course_titles,id', // Ensures course_id exists in the course_titles table
            'attendance_date' => 'required|date',
            'students' => 'required|array',
            'students.*.id' => 'required|exists:students,id',
            'students.*.status' => 'required|in:present,absent,late,excused',
            'students.*.remarks' => 'nullable|string',
        ];
    }
}
