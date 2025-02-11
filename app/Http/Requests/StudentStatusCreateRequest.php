<?php

namespace App\Http\Requests;


use App\Models\StudentStatus;
use Illuminate\Foundation\Http\FormRequest;

class StudentStatusCreateRequest extends BaseFormRequest
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

                    $exists = StudentStatus::where("student_statuses.name",$value)

                        ->where("business_id",auth()->user()->business_id)


                        ->exists();

                    if ($exists) {
                        $fail("$attribute is already exist.");
                    }



                },
            ],
            'description' => 'nullable|string',
            'color' => 'nullable|string',
        ];

        if(empty(auth()->user())) {
            $rules['business_id'] = 'required|numeric|exists:businesses,id';
        }

        // if (!empty(auth()->user()->business_id)) {
        //     $rules['name'] .= '|unique:student_statuses,name,NULL,id,business_id,' . auth()->user()->business_id;
        // } else {
        //     $rules['name'] .= '|unique:student_statuses,name,NULL,id,is_default,' . (auth()->user()->hasRole('superadmin') ? 1 : 0);
        // }


       return $rules;








    }
}
