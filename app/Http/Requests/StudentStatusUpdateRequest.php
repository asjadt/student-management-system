<?php

namespace App\Http\Requests;


use App\Models\StudentStatus;
use Illuminate\Foundation\Http\FormRequest;

class StudentStatusUpdateRequest extends BaseFormRequest
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

                    $student_status_query_params = [
                        "id" => $this->id,
                    ];
                    $student_status = StudentStatus::where($student_status_query_params)
                        ->first();
                    if (!$student_status) {
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



                        $exists = StudentStatus::where("student_statuses.name",$value)
                        ->whereNotIn("id",[$this->id])
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

        // if (!empty(auth()->user()->business_id)) {
        //     $rules['name'] .= '|unique:student_statuses,name,'.$this->id.',id,business_id,' . auth()->user()->business_id;
        // } else {
        //     if(auth()->user()->hasRole('superadmin')){
        //         $rules['name'] .= '|unique:student_statuses,name,'.$this->id.',id,is_default,' . 1 . ',business_id,' . NULL;
        //     }
        //     else {
        //         $rules['name'] .= '|unique:student_statuses,name,'.$this->id.',id,is_default,' . 0 . ',business_id,' . NULL;
        //     }

        // }

return $rules;
    }








}
