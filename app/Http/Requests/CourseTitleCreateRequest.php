<?php

namespace App\Http\Requests;

use App\Models\CourseTitle;
use App\Rules\ValidateAwardingBody;
use Illuminate\Foundation\Http\FormRequest;

class CourseTitleCreateRequest extends FormRequest
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
            'name' => [
                "required",
                'string',
                function ($attribute, $value, $fail) {

                        $created_by  = NULL;
                        if(auth()->user()->business) {
                            $created_by = auth()->user()->business->created_by;
                        }

                        $exists = CourseTitle::where("course_titles.name",$value)



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
        //     $rules['name'] .= '|unique:course_titles,name,NULL,id,business_id,' . auth()->user()->business_id;
        // } else {
        //     $rules['name'] .= '|unique:course_titles,name,NULL,id,is_default,' . (auth()->user()->hasRole('superadmin') ? 1 : 0);
        // }


return $rules;
    }
}
