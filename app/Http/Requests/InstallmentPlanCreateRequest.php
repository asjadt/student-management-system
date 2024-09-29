<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateInstallmentPlanName;

class InstallmentPlanCreateRequest extends BaseFormRequest
{
/**
* Determine if the user is authorized to make this request.
*
* @return  bool
*/
public function authorize()
{
return true;
}

/**
* Get the validation rules that apply to the request.
*
* @return  array
*/
public function rules()
{

$rules = [



    'name' => [
        'required',
        'string',
        'unique:installment_plans,name'
    ],
        'course_id' => [
        'required',
        'numeric',
        'exists:course_titles,id'

    ],

        'number_of_installments' => [
        'required',
        'integer',
    ],

        'installment_amount' => [
        'required',
        'numeric',
    ],

        'start_date' => [
        'nullable',
        'date',
    ],

        'end_date' => [
        'nullable',
        'date',
    ],


];



return $rules;
}
}


