<?php




namespace App\Http\Requests;

use App\Models\Session;
use App\Rules\ValidateSessionName;
use Illuminate\Foundation\Http\FormRequest;

class SessionUpdateRequest extends BaseFormRequest
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

      $session_query_params = [
          "id" => $this->id,
      ];
      $session = Session::where($session_query_params)
          ->first();
      if (!$session) {
          // $fail($attribute . " is invalid.");
          $fail("no session found");
          return 0;
      }
      if (empty(auth()->user()->business_id)) {

          if (auth()->user()->hasRole('superadmin')) {
              if (($session->business_id != NULL )) {
                  // $fail($attribute . " is invalid.");
                  $fail("You do not have permission to update this session due to role restrictions.");
              }
          } else {
              if (($session->business_id != NULL || $session->is_default != 0 || $session->created_by != auth()->user()->id)) {
                  // $fail($attribute . " is invalid.");
                  $fail("You do not have permission to update this session due to role restrictions.");
              }
          }
      } else {
          if (($session->business_id != auth()->user()->business_id || $session->is_default != 0)) {
              // $fail($attribute . " is invalid.");
              $fail("You do not have permission to update this session due to role restrictions.");
          }
      }
  },
],



    'start_date' => [
    'required',
    'date',


],

'end_date' => [
    'required',
    'date',
    'after_or_equal:start_date'
],

    'holiday_dates' => [
    'present',
    'array',

],







];



return $rules;
}
}



