<?php



namespace App\Http\Requests;

use App\Models\ClassRoutine;
use App\Rules\ValidateClassRoutineName;
use Illuminate\Foundation\Http\FormRequest;

class ClassRoutineUpdateRequest extends BaseFormRequest
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

      $class_routine_query_params = [
          "id" => $this->id,
      ];
      $class_routine = ClassRoutine::where($class_routine_query_params)
          ->first();
      if (!$class_routine) {
          // $fail($attribute . " is invalid.");
          $fail("no class routine found");
          return 0;
      }
      if (empty(auth()->user()->business_id)) {

          if (auth()->user()->hasRole('superadmin')) {
              if (($class_routine->business_id != NULL )) {
                  // $fail($attribute . " is invalid.");
                  $fail("You do not have permission to update this class routine due to role restrictions.");
              }
          } else {
              if (($class_routine->business_id != NULL || $class_routine->is_default != 0 || $class_routine->created_by != auth()->user()->id)) {
                  // $fail($attribute . " is invalid.");
                  $fail("You do not have permission to update this class routine due to role restrictions.");
              }
          }
      } else {
          if (($class_routine->business_id != auth()->user()->business_id || $class_routine->is_default != 0)) {
              // $fail($attribute . " is invalid.");
              $fail("You do not have permission to update this class routine due to role restrictions.");
          }
      }
  },
],



    'day_of_week' => [
    'required',
    'numeric',







],

    'start_time' => [
    'required',
    'string',







],

    'end_time' => [
    'required',
    'string',







],

    'room_number' => [
    'required',
    'string',







],

'subject_id' => [
    'required',
    'numeric',
    'exists:subjects,id'

],

    'teacher_id' => [
    'required',
    'numeric',
    "exists:users,id"

],





];



return $rules;
}
}



