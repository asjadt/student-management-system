<?php

namespace App\Http\Requests;

use App\Models\CourseTitle;
use App\Models\StudentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StudentCreateRequestClient extends BaseFormRequest
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
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'nationality' => 'required|string',
            "course_fee" => "nullable|numeric",
            "fee_paid" => "nullable|numeric",
            'course_end_date' => 'nullable|date',
            'level' => 'nullable|string',

            'passport_number' => 'nullable|string',
            'school_id' => 'nullable|string',
            'date_of_birth' => 'required|date',
            'course_start_date' => 'nullable|date',
            'letter_issue_date' => 'nullable|date',
            'business_id' => 'required|numeric|exists:businesses,id',

            'course_title_id' => [
                "required",
                'numeric',
                function ($attribute, $value, $fail) {

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
            'lat' => 'nullable|string|regex:/^-?\d{1,3}\.\d+$/', // Validates latitude format
            'long' => 'nullable|string|regex:/^-?\d{1,3}\.\d+$/', // Validates longitude format
            'emergency_contact_details' => 'nullable|json',
            'previous_education_history' => 'nullable|json',
            'passport_issue_date' => 'nullable|date|before_or_equal:today',
            'passport_expiry_date' => 'nullable|date|after:passport_issue_date',
            'place_of_issue' => 'nullable|string|max:255',



        ];




        return $rules;


    }

    public function messages()
    {
        return [


        ];
    }
}
