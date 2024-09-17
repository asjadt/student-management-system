<?php



namespace App\Http\Requests;

use App\Models\Subject;
use App\Rules\ValidateSubjectName;
use Illuminate\Foundation\Http\FormRequest;

class SubjectUpdateRequest extends BaseFormRequest
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

                    $subject_query_params = [
                        "id" => $this->id,
                    ];
                    $subject = Subject::where($subject_query_params)
                        ->first();
                    if (!$subject) {
                        // $fail($attribute . " is invalid.");
                        $fail("no subject found");
                        return 0;
                    }
                    if (empty(auth()->user()->business_id)) {

                        if (auth()->user()->hasRole('superadmin')) {
                            if (($subject->business_id != NULL)) {
                                // $fail($attribute . " is invalid.");
                                $fail("You do not have permission to update this subject due to role restrictions.");
                            }
                        } else {
                            if (($subject->business_id != NULL || $subject->is_default != 0 || $subject->created_by != auth()->user()->id)) {
                                // $fail($attribute . " is invalid.");
                                $fail("You do not have permission to update this subject due to role restrictions.");
                            }
                        }
                    } else {
                        if (($subject->business_id != auth()->user()->business_id || $subject->is_default != 0)) {
                            // $fail($attribute . " is invalid.");
                            $fail("You do not have permission to update this subject due to role restrictions.");
                        }
                    }
                },
            ],



            'name' => [
                'required',
                'string',



                new ValidateSubjectName(NULL)




            ],

            'description' => [
                'nullable',
                'string',

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
