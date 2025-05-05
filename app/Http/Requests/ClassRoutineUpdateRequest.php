<?php



namespace App\Http\Requests;

use App\Models\ClassRoutine;
use App\Rules\TeacherAvailable;
use App\Rules\UniqueSchedulePerSession;
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
          "business_id" => auth()->user()->business_id
      ];
      $class_routine = ClassRoutine::where($class_routine_query_params)
          ->first();
      if (!$class_routine) {
          // $fail($attribute . " is invalid.");
          $fail("no class routine found");
          return 0;
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
    'string'
],

    'room_number' => [
    'required',
    'string'
],

'subject_id' => [
    'required',
    'numeric',
    'exists:subjects,id'
],

'course_id' => [
    'required',
    'numeric',
    'exists:course_titles,id',
],


'teacher_id' => [
    'required',
    'numeric',
    'exists:users,id',
     new TeacherAvailable($this->day_of_week, $this->start_time, $this->end_time,$this->id),
],

'semester_id' => [
    'nullable',
    'numeric',
    "exists:semesters,id"

],
'session_id' => [
    'nullable',
    'numeric',
    'exists:sessions,id',
    new UniqueSchedulePerSession($this->day_of_week, $this->start_time, $this->end_time, $this->session_id,$this->id)
],




];



return $rules;
}
}



