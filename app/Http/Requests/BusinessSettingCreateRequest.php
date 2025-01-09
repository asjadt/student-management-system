<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessSettingCreateRequest extends FormRequest
{
    public function authorize()
    {
        // Authorize the request (you can modify this based on your requirements)
        return true;
    }

    public function rules()
    {
        return [
            // Validate online_student_status_id exists in student_statuses table
            'online_student_status_id' => 'required|numeric|exists:student_statuses,id',

            // Validate online_verification_data array format
            'student_data_fields' => 'present|array',
            'student_verification_fields' => 'present|array',



        ];
    }


}
