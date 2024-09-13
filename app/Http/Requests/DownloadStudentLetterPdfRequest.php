<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Models\StudentLetter;
use Illuminate\Foundation\Http\FormRequest;

class DownloadStudentLetterPdfRequest extends FormRequest
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
                'student_letter_id' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) {
                        $exists = StudentLetter::where('id', $value)
                            ->where('student_letters.student_id', '=', $this->student_id)
                            ->exists();

                        if (!$exists) {
                            $fail($attribute . " is invalid.");
                        }
                    },
                ],

                'student_id' => [
                    'required',
                    'numeric',

                ],


        ];

        return $rules;
    }
}
