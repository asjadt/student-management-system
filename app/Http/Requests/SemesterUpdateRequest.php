<?php



namespace App\Http\Requests;

use App\Models\Semester;
use App\Rules\ValidateSemesterName;
use Illuminate\Foundation\Http\FormRequest;

class SemesterUpdateRequest extends BaseFormRequest
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

                    $semester_query_params = [
                        "id" => $this->id,
                    ];
                    $semester = Semester::where($semester_query_params)
                        ->first();
                    if (!$semester) {
                        // $fail($attribute . " is invalid.");
                        $fail("no semester found");
                        return 0;
                    }
                    if (empty(auth()->user()->business_id)) {

                        if (auth()->user()->hasRole('superadmin')) {
                            if (($semester->business_id != NULL)) {
                                // $fail($attribute . " is invalid.");
                                $fail("You do not have permission to update this semester due to role restrictions.");
                            }
                        } else {
                            if (($semester->business_id != NULL || $semester->is_default != 0 || $semester->created_by != auth()->user()->id)) {
                                // $fail($attribute . " is invalid.");
                                $fail("You do not have permission to update this semester due to role restrictions.");
                            }
                        }
                    } else {
                        if (($semester->business_id != auth()->user()->business_id || $semester->is_default != 0)) {
                            // $fail($attribute . " is invalid.");
                            $fail("You do not have permission to update this semester due to role restrictions.");
                        }
                    }
                },
            ],



            'name' => [
                'required',
                'string',







            ],

            'start_date' => [
                'required',
                'string',







            ],

            'end_date' => [
                'required',
                'string',







            ],







        ];



        return $rules;
    }
}
