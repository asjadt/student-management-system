<?php





namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateAwardingBodyName;

class AwardingBodyCreateRequest extends BaseFormRequest
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


                new ValidateAwardingBodyName(NULL)




    ],

        'description' => [
        'nullable',
        'string',






    ],

        'accreditation_start_date' => [
        'required',
        'string',






    ],

        'accreditation_expiry_date' => [
        'required',
        'string',






    ],

        'logo' => [
        'nullable',
        'string',






    ],


];



return $rules;
}
}




