<?php



namespace App\Http\Requests;

use App\Models\Teacher;
use App\Rules\ValidateTeacherName;
use Illuminate\Foundation\Http\FormRequest;

class TeacherUpdateRequest extends BaseFormRequest
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

'id' => [
  'required',
  'numeric',
  function ($attribute, $value, $fail) {

      $teacher_query_params = [
          "id" => $this->id,
      ];
      $teacher = Teacher::where($teacher_query_params)
          ->first();
      if (!$teacher) {
          // $fail($attribute . " is invalid.");
          $fail("no teacher found");
          return 0;
      }
      if (empty(auth()->user()->business_id)) {

          if (auth()->user()->hasRole('superadmin')) {
              if (($teacher->business_id != NULL )) {
                  // $fail($attribute . " is invalid.");
                  $fail("You do not have permission to update this teacher due to role restrictions.");
              }
          } else {
              if (($teacher->business_id != NULL || $teacher->is_default != 0 || $teacher->created_by != auth()->user()->id)) {
                  // $fail($attribute . " is invalid.");
                  $fail("You do not have permission to update this teacher due to role restrictions.");
              }
          }
      } else {
          if (($teacher->business_id != auth()->user()->business_id || $teacher->is_default != 0)) {
              // $fail($attribute . " is invalid.");
              $fail("You do not have permission to update this teacher due to role restrictions.");
          }
      }
  },
],



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



