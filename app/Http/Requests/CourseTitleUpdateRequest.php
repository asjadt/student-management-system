<?php

namespace App\Http\Requests;

use App\Models\CourseTitle;
use App\Rules\ValidateAwardingBody;
use Illuminate\Foundation\Http\FormRequest;

class CourseTitleUpdateRequest extends FormRequest
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

            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {

                    $course_title_query_params = [
                        "id" => $this->id,
                    ];
                    $course_title = CourseTitle::where($course_title_query_params)
                        ->first();
                    if (!$course_title) {
                            // $fail("$attribute is invalid.");
                            $fail("no student statuses found");
                            return 0;

                    }
                    if (empty(auth()->user()->business_id)) {

                        if(auth()->user()->hasRole('superadmin')) {
                            if(($course_title->business_id != NULL || $course_title->is_default != 1)) {
                                // $fail("$attribute is invalid.");
                                $fail("You do not have permission to update this student statuses due to role restrictions.");

                          }

                        } else {
                            if(($course_title->business_id != NULL || $course_title->is_default != 0 || $course_title->created_by != auth()->user()->id)) {
                                // $fail("$attribute is invalid.");
                                $fail("You do not have permission to update this student statuses due to role restrictions.");

                          }
                        }

                    } else {
                        if(($course_title->business_id != auth()->user()->business_id || $course_title->is_default != 0)) {
                               // $fail("$attribute is invalid.");
                            $fail("You do not have permission to update this student status due to role restrictions.");
                        }
                    }




                },
            ],

            'name' => [
                "required",
                'string',
                function ($attribute, $value, $fail) {

                        $created_by  = NULL;
                        if(auth()->user()->business) {
                            $created_by = auth()->user()->business->created_by;
                        }

                        $exists = CourseTitle::where("course_titles.name",$value)
                        ->whereNotIn("id",[$this->id])

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
            "awarding_body_id" =>   [
            "required",
            'numeric',
            new ValidateAwardingBody()
            ],

            'subject_ids' => [
                'present',
                'array',
            ],
            'subject_ids.*' => [
               "numeric",
               "exists:subjects,id"
            ],
          


        ];

        // if (!empty(auth()->user()->business_id)) {
        //     $rules['name'] .= '|unique:course_titles,name,'.$this->id.',id,business_id,' . auth()->user()->business_id;
        // } else {
        //     if(auth()->user()->hasRole('superadmin')){
        //         $rules['name'] .= '|unique:course_titles,name,'.$this->id.',id,is_default,' . 1 . ',business_id,' . NULL;
        //     }
        //     else {
        //         $rules['name'] .= '|unique:course_titles,name,'.$this->id.',id,is_default,' . 0 . ',business_id,' . NULL;
        //     }

        // }

return $rules;
    }







}
