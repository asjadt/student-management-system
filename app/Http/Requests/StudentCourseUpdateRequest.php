<?php

namespace App\Http\Requests;

use App\Models\CourseTitle;
use App\Models\Session;
use App\Models\Student;
use App\Models\StudentStatus;
use Illuminate\Foundation\Http\FormRequest;

class StudentCourseUpdateRequest extends FormRequest
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

            'student_id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = Student::where('id', $value)
                        ->where('students.business_id', '=', auth()->user()->business_id)
                        ->exists();

                    if (!$exists) {
                        $fail("$attribute is invalid.");
                    }
                },
            ],

    'sessions' => 'required|array',
    'sessions.*.session_id' => [
        'required',
        'numeric',
        function ($attribute, $value, $fail) {
            $exists = Session::where("id", $value)->exists();
            if (!$exists) {
                $fail("$attribute is invalid.");
            }
        },
    ],

    // Course level
    'sessions.*.courses' => 'required|array',
    'sessions.*.courses.*.course_start_date' => 'required|date',
    'sessions.*.courses.*.course_end_date' => 'nullable|date',
    'sessions.*.courses.*.course_title_id' => [
        'required',
        'numeric',
        function ($attribute, $value, $fail) {
            $exists = CourseTitle::where("id", $value)->exists();
            if (!$exists) {
                $fail("$attribute is invalid.");
            }
        },
    ],
    'sessions.*.courses.*.course_fee' => 'required|numeric',
    'sessions.*.courses.*.fee_paid' => 'required|numeric',
    'sessions.*.courses.*.level' => 'nullable|string',
    'sessions.*.courses.*.course_duration' => 'nullable|string',
    'sessions.*.courses.*.course_detail' => 'nullable|string',

    // Subject level
    'sessions.*.courses.*.subjects' => 'required|array',
    'sessions.*.courses.*.subjects.*.subject_id' => 'required|numeric|exists:subjects,id',


        ];
    }
}
