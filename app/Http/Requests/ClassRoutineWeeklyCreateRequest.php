<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidateClassRoutineName;

class ClassRoutineWeeklyCreateRequest extends BaseFormRequest
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
        return [
            'days' => [
                'required',
                'array',
            ],
            'days.*.day_of_week' => [
                'required',
                'string', // Use string if day_of_week is a name like "Monday"
            ],
            'days.*.start_time' => [
                'required',
                'string', // Ensure valid time format if needed
            ],
            'days.*.end_time' => [
                'required',
                'string', // Ensure valid time format if needed
            ],
            'days.*.room_number' => [
                'required',
                'string',
            ],
            'days.*.subject_id' => [
                'required',
                'numeric',
                'exists:subjects,id',
            ],
            'days.*.teacher_id' => [
                'required',
                'numeric',
                'exists:users,id',
            ],
            'semester_id' => [
                'nullable',
                'numeric',
                'exists:semesters,id',
            ],
        ];
    }





}
