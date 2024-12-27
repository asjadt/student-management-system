<?php

namespace App\Http\Requests;

use App\Models\CourseTitle;
use App\Rules\ValidateAwardingBody;
use Illuminate\Foundation\Http\FormRequest;

class CourseTitleUpdateRequest extends FormRequest
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

            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {

                    $course_title_query_params = [
                        "id" => $this->id,
                    ];
                    $course_title = CourseTitle::where($course_title_query_params)
                        ->first();
                    if (!$course_title) {
                            // $fail("$attribute is invalid.");
                            $fail("no student statuses found");
                            return 0;

                    }





                },
            ],

            'name' => [
                "required",
                'string',
                function ($attribute, $value, $fail) {

                        $created_by  = NULL;
                        if(auth()->user()->business) {
                            $created_by = auth()->user()->business->created_by;
                        }

                        $exists = CourseTitle::where("course_titles.name",$value)
                        ->whereNotIn("id",[$this->id])

                        ->exists();

                    if ($exists) {
                        $fail("$attribute is already exist.");
                    }


                },
            ],
            'level' => 'nullable|string',
            'description' => 'nullable|string',
            'color' => 'required|string',
            "awarding_body_id" =>   [
            "required",
            'numeric',
            new ValidateAwardingBody()
            ],

            'subject_ids' => [
                'present',
                'array',
            ],
            'subject_ids.*' => [
               "numeric",
               "exists:subjects,id"
            ],



        ];

        // if (!empty(auth()->user()->business_id)) {
        //     $rules['name'] .= '|unique:course_titles,name,'.$this->id.',id,business_id,' . auth()->user()->business_id;
        // } else {
        //     if(auth()->user()->hasRole('superadmin')){
        //         $rules['name'] .= '|unique:course_titles,name,'.$this->id.',id,is_default,' . 1 . ',business_id,' . NULL;
        //     }
        //     else {
        //         $rules['name'] .= '|unique:course_titles,name,'.$this->id.',id,is_default,' . 0 . ',business_id,' . NULL;
        //     }

        // }

return $rules;
    }







}
