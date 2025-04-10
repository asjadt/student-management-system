<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\Designation;

use App\Models\Role;
use App\Models\WorkShift;
use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends BaseFormRequest
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
        'first_Name' => 'required|string|max:255',
        'middle_Name' => 'nullable|string|max:255',
        'last_Name' => 'required|string|max:255',


        // 'email' => 'required|string|email|indisposable|max:255|unique:users',
        'email' => 'required|string|email|max:255|unique:users',
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


                if (empty($role)){
                         // $fail("$attribute is invalid.")
                         $fail("Role does not exists.");
                         return 0;

                }

                if(!empty(auth()->user()->business_id)) {
                    if (empty($role->business_id)){
                        // $fail("$attribute is invalid.")
                      $fail("You don't have this role");
                      return 0;

                  }
                if ($role->business_id != auth()->user()->business_id){
                          // $fail("$attribute is invalid.")
                        $fail("You don't have this role");
                        return 0;
                    }
                } else {
                    if (!empty($role->business_id)){
                        // $fail("$attribute is invalid.")
                      $fail("You don't have this role");
                      return 0;
                  }
                }


            },
        ],



        'gender' => 'nullable|string|in:male,female,other',




    ];

    }
}
