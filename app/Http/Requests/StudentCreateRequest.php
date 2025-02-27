<?php

namespace App\Http\Requests;

use App\Models\CourseTitle;
use App\Models\StudentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StudentCreateRequest extends BaseFormRequest
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
        $rules = [

            'title' => 'required|string',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'nationality' => 'required|string',
            "course_fee" => "required|numeric",
            "fee_paid" => "required|numeric",

            'passport_number' => 'nullable|string',
            'student_id' => 'nullable|string',
            'date_of_birth' => 'required|date',
            'course_start_date' => 'required|date',
            'course_end_date' => 'nullable|date',
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

                    ->exists();

                if (!$exists) {
                    $fail("$attribute is invalid.");
                }

                },
            ],
            'attachments' => 'present|array',
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
            'lat' => 'nullable|numeric', // Validates latitude format
            'long' => 'nullable|numeric', // Validates longitude format
            'emergency_contact_details' => 'nullable|json',
            'previous_education_history' => 'nullable|json',
            'passport_issue_date' => 'nullable|date',
            'passport_expiry_date' => 'nullable|date|after:passport_issue_date',
            'place_of_issue' => 'nullable|string|max:255',

            "agency_id" => "nullable|numeric|exists:agencies,id",
            "agency_commission" => "nullable|required_with:agency_id|numeric|min:0",



        ];


        if(empty(auth()->user())) {
            $rules['business_id'] = 'required|numeric|exists:businesses,id';
        }

        return $rules;


    }

    public function messages()
    {
        return [


        ];
    }
}
