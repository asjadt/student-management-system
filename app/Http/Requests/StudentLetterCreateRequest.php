<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentLetterCreateRequest extends FormRequest
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

            'issue_date' => [
            'required',
            'string',

        ],

            'status' => [
            'required',
            'string',
        ],

            'letter_content' => [
            'required',
            'string',
        ],

            'sign_required' => [
            'required',
            'boolean',
        ],
        'letter_view_required' => [
            'required',
            'boolean',
        ],


            'attachments' => [
            'present',
            'array',

        ],

            'student_id' => [
            'required',
            'numeric',



        ],


    ];



    return $rules;
    }
}
