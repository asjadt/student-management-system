<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateInstallmentPaymentName;

class InstallmentPaymentCreateRequest extends BaseFormRequest
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

        'installment_plan_id' => [
        'required',
        'numeric',
        "exists:installment_plans,id"

    ],

        'amount_paid' => [
        'required',
        'numeric',


    ],

        'payment_date' => [
        'required',
        'date',
    ],

    'status' => [
        'required',
        'string',
        'in:pending,paid,overdue'
    ],

        'student_id' => [
        'required',
        'numeric',
        'exists:students,id'

    ],


];



return $rules;
}

public function messages()
{
    return [
        'status.required' => 'The status field is required.',
        'status.string' => 'The status field must be a string.',
        'status.in' => 'The status must be one of the following values: pending, paid, overdue.',
    ];
}
}


