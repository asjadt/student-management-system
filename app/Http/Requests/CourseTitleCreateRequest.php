<?php

namespace App\Http\Requests;

use App\Models\CourseTitle;
use Illuminate\Foundation\Http\FormRequest;

class CourseTitleCreateRequest extends FormRequest
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
        $rules = [
            'name' => [
                "required",
                'string',
                function ($attribute, $value, $fail) {

                        $created_by  = NULL;
                        if(auth()->user()->business) {
                            $created_by = auth()->user()->business->created_by;
                        }

                        $exists = CourseTitle::where("course_titles.name",$value)


                        ->when(empty(auth()->user()->business_id), function ($query) use ( $created_by, $value) {
                            if (auth()->user()->hasRole('superadmin')) {
                                return $query->where('course_titles.business_id', NULL)
                                    ->where('course_titles.is_default', 1)
                                    ->where('course_titles.is_active', 1);

                            } else {
                                return $query->where('course_titles.business_id', NULL)
                                    ->where('course_titles.is_default', 1)
                                    ->where('course_titles.is_active', 1)
                                    ->whereDoesntHave("disabled", function($q) {
                                        $q->whereIn("disabled_course_titles.created_by", [auth()->user()->id]);
                                    })

                                    ->orWhere(function ($query) use($value)  {
                                        $query->where("course_titles.id",$value)->where('course_titles.business_id', NULL)
                                            ->where('course_titles.is_default', 0)
                                            ->where('course_titles.created_by', auth()->user()->id)
                                            ->where('course_titles.is_active', 1);


                                    });
                            }
                        })
                            ->when(!empty(auth()->user()->business_id), function ($query) use ($created_by, $value) {
                                return $query->where('course_titles.business_id', NULL)
                                    ->where('course_titles.is_default', 1)
                                    ->where('course_titles.is_active', 1)
                                    ->whereDoesntHave("disabled", function($q) use($created_by) {
                                        $q->whereIn("disabled_course_titles.created_by", [$created_by]);
                                    })
                                    ->whereDoesntHave("disabled", function($q)  {
                                        $q->whereIn("disabled_course_titles.business_id",[auth()->user()->business_id]);
                                    })

                                    ->orWhere(function ($query) use( $created_by, $value){
                                        $query->where("course_titles.id",$value)->where('course_titles.business_id', NULL)
                                            ->where('course_titles.is_default', 0)
                                            ->where('course_titles.created_by', $created_by)
                                            ->where('course_titles.is_active', 1)
                                            ->whereDoesntHave("disabled", function($q) {
                                                $q->whereIn("disabled_course_titles.business_id",[auth()->user()->business_id]);
                                            });
                                    })
                                    ->orWhere(function ($query) use($value)  {
                                        $query->where("course_titles.id",$value)->where('course_titles.business_id', auth()->user()->business_id)
                                            ->where('course_titles.is_default', 0)
                                            ->where('course_titles.is_active', 1);

                                    });
                            })
                        ->exists();

                    if ($exists) {
                        $fail("$attribute is already exist.");
                    }


                },
            ],
            'description' => 'nullable|string',
            'color' => 'required|string',
        ];

        // if (!empty(auth()->user()->business_id)) {
        //     $rules['name'] .= '|unique:course_titles,name,NULL,id,business_id,' . auth()->user()->business_id;
        // } else {
        //     $rules['name'] .= '|unique:course_titles,name,NULL,id,is_default,' . (auth()->user()->hasRole('superadmin') ? 1 : 0);
        // }


return $rules;
    }
}
