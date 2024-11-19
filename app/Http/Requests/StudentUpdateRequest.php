<?php

namespace App\Http\Requests;

use App\Models\CourseTitle;
use App\Models\Student;
use App\Models\StudentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StudentUpdateRequest extends BaseFormRequest
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
            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = Student::
                          where('id', $value)
                        ->where('students.business_id', '=', auth()->user()->business_id)
                        ->exists();

                    if (!$exists) {
                        $fail("$attribute is invalid.");
                    }
                },
            ],
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'nationality' => 'required|string',
            "course_fee" => "required|numeric",
            "fee_paid" => "required|numeric",
            'passport_number' => 'nullable|string',
            'school_id' => 'nullable|string',
            'date_of_birth' => 'required|date',
            'course_start_date' => 'required|date',
            'course_end_date' => 'required|date',
            'level' => 'nullable|string',
            'letter_issue_date' => 'nullable|date',

            'student_status_id' => [
                "required",
                'numeric',
                function ($attribute, $value, $fail) {

                    $created_by  = NULL;
                    if(auth()->user()->business) {
                        $created_by = auth()->user()->business->created_by;
                    }

                    $exists = StudentStatus::where("student_statuses.id",$value)
                    ->when(empty(auth()->user()->business_id), function ($query) use ( $created_by, $value) {
                        if (auth()->user()->hasRole('superadmin')) {
                            return $query->where('student_statuses.business_id', NULL)
                                ->where('student_statuses.is_default', 1)
                                ->where('student_statuses.is_active', 1);

                        } else {
                            return $query->where('student_statuses.business_id', NULL)
                                ->where('student_statuses.is_default', 1)
                                ->where('student_statuses.is_active', 1)
                                ->whereDoesntHave("disabled", function($q) {
                                    $q->whereIn("disabled_student_statuses.created_by", [auth()->user()->id]);
                                })

                                ->orWhere(function ($query) use($value)  {
                                    $query->where("student_statuses.id",$value)->where('student_statuses.business_id', NULL)
                                        ->where('student_statuses.is_default', 0)
                                        ->where('student_statuses.created_by', auth()->user()->id)
                                        ->where('student_statuses.is_active', 1);


                                });
                        }
                    })
                        ->when(!empty(auth()->user()->business_id), function ($query) use ($created_by, $value) {
                            return $query->where('student_statuses.business_id', NULL)
                                ->where('student_statuses.is_default', 1)
                                ->where('student_statuses.is_active', 1)
                                ->whereDoesntHave("disabled", function($q) use($created_by) {
                                    $q->whereIn("disabled_student_statuses.created_by", [$created_by]);
                                })
                                ->whereDoesntHave("disabled", function($q)  {
                                    $q->whereIn("disabled_student_statuses.business_id",[auth()->user()->business_id]);
                                })

                                ->orWhere(function ($query) use( $created_by, $value){
                                    $query->where("student_statuses.id",$value)->where('student_statuses.business_id', NULL)
                                        ->where('student_statuses.is_default', 0)
                                        ->where('student_statuses.created_by', $created_by)
                                        ->where('student_statuses.is_active', 1)
                                        ->whereDoesntHave("disabled", function($q) {
                                            $q->whereIn("disabled_student_statuses.business_id",[auth()->user()->business_id]);
                                        });
                                })
                                ->orWhere(function ($query) use($value)  {
                                    $query->where("student_statuses.id",$value)->where('student_statuses.business_id', auth()->user()->business_id)
                                        ->where('student_statuses.is_default', 0)
                                        ->where('student_statuses.is_active', 1);

                                });
                        })
                    ->exists();

                if (!$exists) {
                    $fail("$attribute is invalid.");
                }

                },
            ],
            'course_title_id' => [
                "required",
                'numeric',
                function ($attribute, $value, $fail) {

                    $created_by  = NULL;
                    if(auth()->user()->business) {
                        $created_by = auth()->user()->business->created_by;
                    }

                    $exists = CourseTitle::where("course_titles.id",$value)
                    ->when(empty(auth()->user()->business_id), function ($query) use ( $created_by, $value) {
                        if (auth()->user()->hasRole('superadmin')) {
                            return $query->where('course_titles.business_id', NULL)
                                ->where('course_titles.is_default', 1)
                                ->where('course_titles.is_active', 1);

                        } else {
                            return $query->where('course_titles.business_id', NULL)
                                ->where('course_titles.is_default', 1)
                                ->where('course_titles.is_active', 1)
                                ->whereDoesntHave("disabled", function($q) {
                                    $q->whereIn("disabled_course_titles.created_by", [auth()->user()->id]);
                                })

                                ->orWhere(function ($query) use($value)  {
                                    $query->where("course_titles.id",$value)->where('course_titles.business_id', NULL)
                                        ->where('course_titles.is_default', 0)
                                        ->where('course_titles.created_by', auth()->user()->id)
                                        ->where('course_titles.is_active', 1);


                                });
                        }
                    })
                        ->when(!empty(auth()->user()->business_id), function ($query) use ($created_by, $value) {
                            return $query->where('course_titles.business_id', NULL)
                                ->where('course_titles.is_default', 1)
                                ->where('course_titles.is_active', 1)
                                ->whereDoesntHave("disabled", function($q) use($created_by) {
                                    $q->whereIn("disabled_course_titles.created_by", [$created_by]);
                                })
                                ->whereDoesntHave("disabled", function($q)  {
                                    $q->whereIn("disabled_course_titles.business_id",[auth()->user()->business_id]);
                                })

                                ->orWhere(function ($query) use( $created_by, $value){
                                    $query->where("course_titles.id",$value)->where('course_titles.business_id', NULL)
                                        ->where('course_titles.is_default', 0)
                                        ->where('course_titles.created_by', $created_by)
                                        ->where('course_titles.is_active', 1)
                                        ->whereDoesntHave("disabled", function($q) {
                                            $q->whereIn("disabled_course_titles.business_id",[auth()->user()->business_id]);
                                        });
                                })
                                ->orWhere(function ($query) use($value)  {
                                    $query->where("course_titles.id",$value)->where('course_titles.business_id', auth()->user()->business_id)
                                        ->where('course_titles.is_default', 0)
                                        ->where('course_titles.is_active', 1);

                                });
                        })
                    ->exists();

                if (!$exists) {
                    $fail("$attribute is invalid.");
                }

                },
            ],
            'attachments' => 'nullable|array',
            'attachments.*' => 'string',


            'course_duration'=> 'nullable|string',
            'course_detail'=> 'nullable|string',

            'email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:20',
            'sex' => 'nullable|string|in:Male,Female,Other',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'lat' => 'nullable|string|regex:/^-?\d{1,3}\.\d+$/', // Validates latitude format
            'long' => 'nullable|string|regex:/^-?\d{1,3}\.\d+$/', // Validates longitude format
            'emergency_contact_details' => 'nullable|json',
            'previous_education_history' => 'nullable|json',
            'passport_issue_date' => 'nullable|date|before_or_equal:today',
            'passport_expiry_date' => 'nullable|date|after:passport_issue_date',
            'place_of_issue' => 'nullable|string|max:255',




        ];
    }

    public function messages()
    {
        return [


        ];
    }
}
