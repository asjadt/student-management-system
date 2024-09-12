<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateSemesterName;

class SemesterCreateRequest extends BaseFormRequest
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

        $rules = [

            'name' => [
                'required',
                'string',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
            ],
            'course_id' => [
                'nullable',
                'numeric',
                "exists:course_titles,id"
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



        return $rules;
    }
}
