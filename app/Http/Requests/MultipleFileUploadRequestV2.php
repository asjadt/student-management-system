<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;

use Illuminate\Foundation\Http\FormRequest;

class MultipleFileUploadRequestV2 extends FormRequest
{
    use BasicUtil;
    /**
     * Determine if the student is authorized to make this request.
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




        return [
            'files' => 'required|array',
            'files.*' => 'required|file|max:5120',
            "student_id" => [
                "nullable",
                "numeric",
            ],
            "folder_location" => "required|string",
            "is_public" => "required|boolean",
        ];
    }
}
