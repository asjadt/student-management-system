<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateTeacherName;

class TeacherCreateRequest extends BaseFormRequest
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

        'first_name' => [
        'required',
        'string',






    ],

        'middle_name' => [
        'nullable',
        'string',






    ],

        'last_name' => [
        'required',
        'string',






    ],

        'email' => [
        'required',
        'email',






    ],

        'phone' => [
        'required',
        'string',






    ],

        'qualification' => [
        'nullable',
        'string',






    ],

        'hire_date' => [
        'required',
        'date',






    ],


];



return $rules;
}
}


