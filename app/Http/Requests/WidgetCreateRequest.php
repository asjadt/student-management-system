<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WidgetCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "widgets" => "required|array",
            "widgets.*.id" => "nullable|numeric",
            'widgets.*.widget_name' => "required|string",
            'widgets.*.widget_order' => "required|numeric"
        ];
    }
}