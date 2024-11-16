<?php







namespace App\Http\Requests;

use App\Models\AwardingBody;
use App\Rules\ValidateAwardingBodyName;
use Illuminate\Foundation\Http\FormRequest;

class AwardingBodyUpdateRequest extends BaseFormRequest
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

                    $awarding_body_query_params = [
                        "id" => $this->id,
                    ];
                    $awarding_body = AwardingBody::where($awarding_body_query_params)
                        ->first();
                    if (!$awarding_body) {
                        // $fail($attribute . " is invalid.");
                        $fail("no awarding body found");
                        return 0;
                    }
                    if (empty(auth()->user()->business_id)) {

                        if (auth()->user()->hasRole('superadmin')) {
                            if (($awarding_body->business_id != NULL)) {
                                // $fail($attribute . " is invalid.");
                                $fail("You do not have permission to update this awarding body due to role restrictions.");
                            }
                        } else {
                            if (($awarding_body->business_id != NULL || $awarding_body->is_default != 0 || $awarding_body->created_by != auth()->user()->id)) {
                                // $fail($attribute . " is invalid.");
                                $fail("You do not have permission to update this awarding body due to role restrictions.");
                            }
                        }
                    } else {
                        if (($awarding_body->business_id != auth()->user()->business_id || $awarding_body->is_default != 0)) {
                            // $fail($attribute . " is invalid.");
                            $fail("You do not have permission to update this awarding body due to role restrictions.");
                        }
                    }
                },
            ],



            'name' => [
                'required',
                'string',



                new ValidateAwardingBodyName($this->id)




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
