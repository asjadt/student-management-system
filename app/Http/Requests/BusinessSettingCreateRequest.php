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

            // Validate online_form_fields array format
            'online_form_fields' => 'present|array',
            'online_form_fields.*.field_name' => 'required|string',
            'online_form_fields.*.is_display' => 'required|boolean',
            'online_form_fields.*.is_required' => 'required|boolean',

            // Validate online_verification_data array format
            'online_verification_data' => 'present|array',
            'online_verification_data.*.field_name' => 'required|string',
            'online_verification_data.*.is_display' => 'required|boolean',
        ];
    }


}
