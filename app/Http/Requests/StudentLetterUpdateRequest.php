<?php

namespace App\Http\Requests;

use App\Models\StudentLetter;
use Illuminate\Foundation\Http\FormRequest;

class StudentLetterUpdateRequest extends FormRequest
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
                    $exists = StudentLetter::where('id', $value)
                        ->where('student_letters.student_id', '=', $this->user_id)
                        ->exists();

                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }
                },
            ],
            'student_id' => [
                'required',
                'numeric'
            ],

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









        ];



        return $rules;
    }
}
