<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReminderCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string',
            'duration' => 'nullable|required_if:entity_name,passport_expiry_reminder|numeric',
            'duration_unit' => 'required|in:days,weeks,months',
            'send_time' => 'nullable|required_if:entity_name,passport_expiry_reminder|in:before_expiry,after_expiry',
            'frequency_after_first_reminder' => 'required|integer',
            'reminder_limit' => "nullable|integer",
            'keep_sending_until_update' => 'nullable|required_if:entity_name,passport_expiry_reminder|boolean',
            'entity_name' => 'required|string|in:passport_expiry_reminder,attendance_reminder',
            'course_id' => 'nullable|required_if:entity_name,attendance_reminder|numeric|exists:course_titles,id',
            'attendance_threshold' => 'nullable|required_if:entity_name,attendance_reminder|numeric',
        ];
    }
    public function messages()
    {
        return [
            'duration_unit.in' => 'The :attribute valid values are days, weeks, months.',
            'send_time.in' => 'The :attribute valid values are before_expiry, after_expiry.',

               'entity_name.in' => 'The :attribute valid values are document_expiry_reminder,maintainance_expiry_reminder .'
        ];
    }
}
