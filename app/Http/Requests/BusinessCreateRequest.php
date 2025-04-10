<?php

namespace App\Http\Requests;

use App\Rules\DayValidation;
use App\Rules\SomeTimes;
use App\Rules\TimeOrderRule;
use App\Rules\TimeValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class BusinessCreateRequest extends BaseFormRequest
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

        return [

            'business.owner_id' => 'required|numeric',
            'business.name' => 'required|string|unique:businesses,name',
            'business.url' => 'nullable|string|max:255',

            'business.trail_end_date' => 'required|string|date',
            'business.color_theme_name' => 'nullable|string',
            'business.about' => 'nullable|string',
            'business.web_page' => 'nullable|string',
            'business.phone' => 'nullable|string',
            // 'business.email' => 'required|string|email|indisposable|max:255|unique:businesses,email',
            'business.email' => 'nullable|string|email|max:255|unique:businesses,email',
            'business.additional_information' => 'nullable|string',

            'business.lat' => 'nullable|numeric',
            'business.long' => 'nullable|numeric',
            'business.country' => 'nullable|string',
            'business.city' => 'nullable|string',

            'business.currency' => 'nullable|string',

            'business.postcode' => 'nullable|string',
            'business.address_line_1' => 'nullable|string',
            'business.address_line_2' => 'nullable|string',

        'business.student_disabled_fields' => 'nullable|array',
        'business.student_disabled_fields.*' => 'string',

        'business.student_optional_fields' => 'nullable|array',
        'business.student_optional_fields.*' => 'string',


            'business.logo' => 'nullable|string',

            'business.image' => 'nullable|string',

            'business.images' => 'nullable|array',
            'business.images.*' => 'nullable|string',









        ];


    }

    public function messages()
    {
        return [
            'business.owner_id.required' => 'The owner ID field is required.',
            'business.owner_id.numeric' => 'The owner ID must be a numeric value.',

            'business.name.required' => 'The name field is required.',
            'business.name.string' => 'The name field must be a string.',
            'business.name.max' => 'The name field may not be greater than :max characters.',
            'business.about.string' => 'The about field must be a string.',
            'business.web_page.string' => 'The web page field must be a string.',
            'business.phone.string' => 'The phone field must be a string.',
            // 'business.email.required' => 'The email field is required.',
            'business.email.string' => 'The email field must be a string.',
            'business.email.email' => 'The email field must be a valid email address.',
            'business.email.max' => 'The email field may not be greater than :max characters.',
            'business.email.unique' => 'The email has already been taken.',
            'business.additional_information.string' => 'The additional information field must be a string.',
            'business.lat.required' => 'The latitude field is required.',
            'business.lat.string' => 'The latitude field must be a string.',
            'business.long.required' => 'The longitude field is required.',
            'business.long.string' => 'The longitude field must be a string.',
            'business.country.required' => 'The country field is required.',
            'business.country.string' => 'The country field must be a string.',
            'business.city.required' => 'The city field is required.',
            'business.city.string' => 'The city field must be a string.',
            'business.currency.string' => 'The currency field must be a string.',
            'business.currency.string' => 'The currency field must be a string.',
            'business.postcode.required' => 'The postcode field is required.',
            'business.postcode.string' => 'The postcode field must be a string.',
            'business.address_line_1.required' => 'The address line 1 field is required.',
            'business.address_line_1.string' => 'The address line 1 field must be a string.',
            'business.address_line_2.string' => 'The address line 2 field must be a string.',
            'business.logo.string' => 'The logo field must be a string.',
            'business.image.string' => 'The image field must be a string.',
            'business.images.array' => 'The images field must be an array.',
            'business.images.*.string' => 'Each image in the images field must be a string.',




        ];
    }



}
