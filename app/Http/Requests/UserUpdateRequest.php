<?php

namespace App\Http\Requests;




use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'id' => "required|numeric",
            'first_Name' => 'required|string|max:255',
        'middle_Name' => 'nullable|string|max:255',

        'last_Name' => 'required|string|max:255',


        // 'email' => 'required|string|email|indisposable|max:255|unique:users',
        'email' => 'required|string|unique:users,email,' . $this->id . ',id',

        'password' => 'nullable|string|min:6',
        'phone' => 'nullable|string',
        'image' => 'nullable|string',
        'address_line_1' => 'nullable|string',
        'address_line_2' => 'nullable|string',
        'country' => 'nullable|string',
        'city' => 'nullable|string',
        'postcode' => 'nullable|string',
        'lat' => 'nullable|numeric',
        'long' => 'nullable|numeric',
        'role' => [
            "required",
            'string',
            function ($attribute, $value, $fail) {
                $role  = Role::where(["name" => $value])->first();

                if (!$role){
                         // $fail("$attribute is invalid.")
                         $fail("Role does not exists.");
                }

                if(!empty(auth()->user()->business_id)) {
                    if (empty($role->business_id)){
                        // $fail("$attribute is invalid.")
                      $fail("You don't have this role");

                  }
                    if ($role->business_id != auth()->user()->business_id){
                          // $fail("$attribute is invalid.")
                        $fail("You don't have this role");

                    }
                } else {
                    if (!empty($role->business_id)){
                        // $fail("$attribute is invalid.")
                      $fail("You don't have this role");

                  }
                }


            },
        ],

        'gender' => 'nullable|string|in:male,female,other',

        ];
    }

    public function messages()
    {
        return [
            'gender.in' => 'The :attribute field must be in "male","female","other".',
        ];
    }
}
