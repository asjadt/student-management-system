<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:student,customer'],
            // For students
            'student_id' => ['required_if:type,student', 'exists:students,student_id'],
            'date_of_birth' => ['required_if:type,student', 'exists:students,date_of_birth'],
            // For customers
            'first_name' => ['required_if:type,customer', 'string', 'max:255'],
            'last_name' => ['required_if:type,customer', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'comment' => ['nullable', 'string'],
            "business_id" => ['required', 'exists:businesses,id'],
        ];
    }
}
