<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseTitleCreateRequest;
use App\Http\Requests\CourseTitleUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\CourseTitle;
use App\Models\DisabledCourseTitle;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseTitleController extends Controller
{
    use ErrorUtil, UserActivityUtil, BusinessUtil, BasicUtil;
    /**
     *
     * @OA\Post(
     *      path="/v1.0/course-titles",
     *      operationId="createCourseTitle",
     *      tags={"student.course_titles"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store course title",
     *      description="This method is to store course title",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
 * @OA\Property(property="name", type="string", format="string", example="tttttt"),
 *  * @OA\Property(property="color", type="string", format="string", example="red"),
 * @OA\Property(property="description", type="string", format="string", example="erg ear ga&nbsp;"),
 *  @OA\Property(property="awarding_body_id", type="string", format="string", example="awarding_body_id"),
 *      * @OA\Property(property="subject_ids", type="string", format="array", example={1,2,3}),
 *
 *
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function createCourseTitle(CourseTitleCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('course_title_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();


                $request_data["is_active"] = 1;
                $request_data["is_default"] = 0;
                $request_data["created_by"] = $request->user()->id;
                $request_data["business_id"] = $request->user()->business_id;

                if (empty($request->user()->business_id)) {
                    $request_data["business_id"] = NULL;
                    if ($request->user()->hasRole('superadmin')) {
                        $request_data["is_default"] = 1;
                    }
                }



                $course_title =  CourseTitle::create($request_data);

                $course_title->subjects()->sync($request_data["subject_ids"]);


                return response($course_title, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/v1.0/course-titles-update",
     *      operationId="updateCourseTitle",
     *      tags={"student.course_titles"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update course title ",
     *      description="This method is to update course title",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
*      @OA\Property(property="id", type="number", format="number", example="Updated Christmas"),
 * @OA\Property(property="name", type="string", format="string", example="tttttt"),
 *  *  * @OA\Property(property="color", type="string", format="string", example="red"),
 * @OA\Property(property="description", type="string", format="string", example="erg ear ga&nbsp;"),
 *  * *  * @OA\Property(property="awarding_body_id", type="string", format="string", example="awarding_body_id"),
 *      * @OA\Property(property="subject_ids", type="string", format="array", example={1,2,3}),


     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function updateCourseTitle(CourseTitleUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('course_title_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();



                $course_title_query_params = [
                    "id" => $request_data["id"],
                ];


                $course_title  =  tap(CourseTitle::where($course_title_query_params))->update(
                    collect($request_data)->only([
                        'name',
                        'level',
                        'color',
                        'description',
                        "awarding_body_id"
                        // "is_active",
                        // "business_id",

                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();
                if (!$course_title) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }
                $course_title->subjects()->sync($request_data["subject_ids"]);

                return response($course_title, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

  /**
     *
     * @OA\Put(
     *      path="/v1.0/course-titles/toggle-active",
     *      operationId="toggleActiveCourseTitle",
     *      tags={"student.course_titles"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to toggle course title",
     *      description="This method is to toggle course title",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(

     *           @OA\Property(property="id", type="string", format="number",example="1"),
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

     public function toggleActiveCourseTitle(GetIdRequest $request)
     {

         try {
             $this->storeActivity($request, "DUMMY activity", "DUMMY description");
             if (!$request->user()->hasPermissionTo('course_title_activate')) {
                 return response()->json([
                     "message" => "You can not perform this action"
                 ], 401);
             }
             $request_data = $request->validated();

                $this->toggleActivation(
                    CourseTitle::class,
                    DisabledCourseTitle::class,
                    'course_title_id',
                    $request_data["id"],
                    auth()->user()
                );

             return response()->json(['message' => 'course title status updated successfully'], 200);
         } catch (Exception $e) {
             error_log($e->getMessage());
             return $this->sendError($e, 500, $request);
         }
     }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/course-titles",
     *      operationId="getCourseTitles",
     *      tags={"student.course_titles"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *              @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="per_page",
     *         required=true,
     *  example="6"
     *      ),

     *      * *  @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=true,
     * example="search_key"
     * ),
     * *      * *  @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=true,
     * example="1"
     * ),
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),

     *      summary="This method is to get course titles  ",
     *      description="This method is to get course titles ",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function getCourseTitles(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('course_title_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $created_by  = NULL;
            if(auth()->user()->business) {
                $created_by = auth()->user()->business->created_by;
            }


            $course_titles = CourseTitle::
               with("awarding_body","subjects")
               ->when(empty(auth()->user()->business_id), function ($query) use ($request, $created_by) {
                $query->when(auth()->user()->hasRole('superadmin'), function ($query) use ($request) {
                    $query->forSuperAdmin('course_titles');
                }, function ($query) use ($request, $created_by) {
                    $query->forNonSuperAdmin('course_titles', 'disabled_letter_templates', $created_by);
                });
            })

            ->when(!empty(auth()->user()->business_id), function ($query) use ( $created_by) {
                $query->forBusiness('course_titles', "disabled_letter_templates", $created_by);
            })

                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                        $query->where("course_titles.name", "like", "%" . $term . "%")
                            ->orWhere("course_titles.description", "like", "%" . $term . "%");
                    });
                })
                //    ->when(!empty($request->product_category_id), function ($query) use ($request) {
                //        return $query->where('product_category_id', $request->product_category_id);
                //    })
                ->when(!empty($request->awarding_body_id), function ($query) use ($request) {
                    return $query->where('course_titles.awarding_body_id', $request->awarding_body_id);
                })
                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('course_titles.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('course_titles.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("course_titles.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("course_titles.id", "DESC");
                })
                ->when(!empty($request->per_page), function ($query) use ($request) {
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    return $query->get();
                });;



            return response()->json($course_titles, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
 /**
     *
     * @OA\Get(
     *      path="/v1.0/client/course-titles",
     *      operationId="getCourseTitlesClient",
     *      tags={"student.course_titles"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     *              @OA\Parameter(
     *         name="business_id",
     *         in="query",
     *         description="business_id",
     *         required=true,
     *  example="2"
     *      ),

     *              @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="per_page",
     *         required=true,
     *  example="6"
     *      ),

     *      * *  @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=true,
     * example="search_key"
     * ),
     * *      * *  @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=true,
     * example="1"
     * ),
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),

     *      summary="This method is to get course statuses  ",
     *      description="This method is to get course statuses ",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

     public function getCourseTitlesClient(Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");




             $business_id =  $request->business_id;
             if(!$business_id) {
                $error = [ "message" => "The given data was invalid.",
                "errors" => ["business_id"=>["The business id field is required."]]
                ];
                    throw new Exception(json_encode($error),422);
             }


             $course_titles = CourseTitle::
             where('course_titles.business_id', $business_id)
             ->where('course_titles.is_active', 1)
                 ->when(!empty($request->search_key), function ($query) use ($request) {
                     return $query->where(function ($query) use ($request) {
                         $term = $request->search_key;
                         $query->where("course_titles.name", "like", "%" . $term . "%")
                             ->orWhere("course_titles.description", "like", "%" . $term . "%");
                     });
                 })
                 //    ->when(!empty($request->product_category_id), function ($query) use ($request) {
                 //        return $query->where('product_category_id', $request->product_category_id);
                 //    })
                 ->when(!empty($request->start_date), function ($query) use ($request) {
                     return $query->where('course_titles.created_at', ">=", $request->start_date);
                 })
                 ->when(!empty($request->end_date), function ($query) use ($request) {
                     return $query->where('course_titles.created_at', "<=", ($request->end_date . ' 23:59:59'));
                 })
                 ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                     return $query->orderBy("course_titles.id", $request->order_by);
                 }, function ($query) {
                     return $query->orderBy("course_titles.id", "DESC");
                 })
                 ->when(!empty($request->per_page), function ($query) use ($request) {
                     return $query->paginate($request->per_page);
                 }, function ($query) {
                     return $query->get();
                 });;



             return response()->json($course_titles, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/course-titles/{id}",
     *      operationId="getCourseTitleById",
     *      tags={"student.course_titles"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="6"
     *      ),
     *      summary="This method is to get course title by id",
     *      description="This method is to get course title by id",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */


    public function getCourseTitleById($id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('course_title_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $course_title =  CourseTitle::with("subjects")->where([
                "id" => $id,

            ])
                ->first();
                if (!$course_title) {
                    $this->storeError(
                        "no data found"
                        ,
                        404,
                        "front end error",
                        "front end error"
                       );
                    return response()->json([
                        "message" => "no data found"
                    ], 404);
                }

                if (empty(auth()->user()->business_id)) {

                    if (auth()->user()->hasRole('superadmin')) {
                        if (($course_title->business_id != NULL || $course_title->is_default != 1)) {
                            $this->storeError(
                                "You do not have permission to update this due to role restrictions.",
                                403,
                                "front end error",
                                "front end error"
                               );
                            return response()->json([
                                "message" => "You do not have permission to update this course title due to role restrictions."
                            ], 403);
                        }
                    } else {
                        if ($course_title->business_id != NULL) {
                            $this->storeError(
                                "You do not have permission to update this due to role restrictions.",
                                403,
                                "front end error",
                                "front end error"
                               );
                            return response()->json([
                                "message" => "You do not have permission to update this course title due to role restrictions."
                            ], 403);
                        } else if ($course_title->is_default == 0 && $course_title->created_by != auth()->user()->id) {
                            $this->storeError(
                                "You do not have permission to update this due to role restrictions.",
                                403,
                                "front end error",
                                "front end error"
                               );
                                return response()->json([
                                    "message" => "You do not have permission to update this course title due to role restrictions."
                                ], 403);

                        }
                    }
                } else {
                    if ($course_title->business_id != NULL) {
                        if (($course_title->business_id != auth()->user()->business_id)) {
                            $this->storeError(
                                "You do not have permission to update this due to role restrictions.",
                                403,
                                "front end error",
                                "front end error"
                               );
                            return response()->json([
                                "message" => "You do not have permission to update this course title due to role restrictions."
                            ], 403);
                        }
                    } else {
                        if ($course_title->is_default == 0) {
                            if ($course_title->created_by != auth()->user()->created_by) {
                                $this->storeError(
                                    "You do not have permission to update this due to role restrictions.",
                                    403,
                                    "front end error",
                                    "front end error"
                                   );
                                return response()->json([
                                    "message" => "You do not have permission to update this course title due to role restrictions."
                                ], 403);
                            }
                        }
                    }
                }


            return response()->json($course_title, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/course-titles/{ids}",
     *      operationId="deleteCourseTitlesByIds",
     *      tags={"student.course_titles"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="ids",
     *         in="path",
     *         description="ids",
     *         required=true,
     *  example="1,2,3"
     *      ),
     *      summary="This method is to delete course titles by ids",
     *      description="This method is to delete course titles by ids",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */


    public function deleteCourseTitlesByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('course_title_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $idsArray = explode(',', $ids);
            $existingIds = CourseTitle::whereIn('id', $idsArray)
            ->when(empty($request->user()->business_id), function ($query) use ($request) {
                if ($request->user()->hasRole("superadmin")) {
                    return $query->where('course_titles.business_id', NULL)
                        ->where('course_titles.is_default', 1);
                } else {
                    return $query->where('course_titles.business_id', NULL)
                        ->where('course_titles.is_default', 0)
                        ->where('course_titles.created_by', $request->user()->id);
                }
            })
            ->when(!empty($request->user()->business_id), function ($query) use ($request) {
                return $query->where('course_titles.business_id', $request->user()->business_id)
                    ->where('course_titles.is_default', 0);
            })
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            $nonExistingIds = array_diff($idsArray, $existingIds);

            if (!empty($nonExistingIds)) {
                $this->storeError(
                    "no data found"
                    ,
                    404,
                    "front end error",
                    "front end error"
                   );
                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }

            $user_exists =  User::whereIn("course_title_id",$existingIds)->exists();
            if($user_exists) {
                $conflictingUsers = User::whereIn("course_title_id", $existingIds)->get(['id', 'first_Name',
                'last_Name',]);
                $this->storeError(
                    "Some users are associated with the specified course titles"
                    ,
                    409,
                    "front end error",
                    "front end error"
                   );
                return response()->json([
                    "message" => "Some users are associated with the specified course titles",
                    "conflicting_users" => $conflictingUsers
                ], 409);

            }


            CourseTitle::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully","deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
