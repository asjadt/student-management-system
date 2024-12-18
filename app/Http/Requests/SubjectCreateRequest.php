<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateSubjectName;

class SubjectCreateRequest extends BaseFormRequest
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


                new ValidateSubjectName(NULL)




    ],

        'description' => [
        'nullable',
        'string',

    ],
    'course_id' => [
        "nullable",
        "numeric",
        "exists:course_titles,id"
     ],

    'teacher_ids' => [
        'present',
        'array',
    ],

    'teacher_ids.*' => [
        'numeric',
        'exists:users,id',
    ],



];



return $rules;
}
}


