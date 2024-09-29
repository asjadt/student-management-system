<?php



namespace App\Http\Requests;

use App\Models\InstallmentPlan;
use App\Rules\ValidateInstallmentPlanName;
use Illuminate\Foundation\Http\FormRequest;

class InstallmentPlanUpdateRequest extends BaseFormRequest
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

      $installment_plan_query_params = [
          "id" => $this->id,
      ];
      $installment_plan = InstallmentPlan::where($installment_plan_query_params)
          ->first();
      if (!$installment_plan) {
          // $fail($attribute . " is invalid.");
          $fail("no installment plan found");
          return 0;
      }
      if (empty(auth()->user()->business_id)) {

          if (auth()->user()->hasRole('superadmin')) {
              if (($installment_plan->business_id != NULL )) {
                  // $fail($attribute . " is invalid.");
                  $fail("You do not have permission to update this installment plan due to role restrictions.");
              }
          } else {
              if (($installment_plan->business_id != NULL || $installment_plan->is_default != 0 || $installment_plan->created_by != auth()->user()->id)) {
                  // $fail($attribute . " is invalid.");
                  $fail("You do not have permission to update this installment plan due to role restrictions.");
              }
          }
      } else {
          if (($installment_plan->business_id != auth()->user()->business_id || $installment_plan->is_default != 0)) {
              // $fail($attribute . " is invalid.");
              $fail("You do not have permission to update this installment plan due to role restrictions.");
          }
      }
  },
],

'name' => [
    'required',
    'string',
    'unique:installment_plans,name,' . $this->id . ',id'
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



