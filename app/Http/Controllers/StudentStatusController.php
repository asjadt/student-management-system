<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentStatusCreateRequest;
use App\Http\Requests\StudentStatusUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\DisabledStudentStatus;
use App\Models\StudentStatus;
use App\Models\SettingPaidLeaveStudentStatus;
use App\Models\SettingUnpaidLeaveStudentStatus;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentStatusController extends Controller
{
    use ErrorUtil, UserActivityUtil, BusinessUtil;
    /**
     *
     * @OA\Post(
     *      path="/v1.0/student-statuses",
     *      operationId="createStudentStatus",
     *      tags={"student.student_statuses"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store student status",
     *      description="This method is to store student status",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
 * @OA\Property(property="name", type="string", format="string", example="tttttt"),
 *  * @OA\Property(property="color", type="string", format="string", example="red"),
 * @OA\Property(property="description", type="string", format="string", example="erg ear ga&nbsp;")
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

    public function createStudentStatus(StudentStatusCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('student_status_create')) {
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



                $student_status =  StudentStatus::create($request_data);




                return response($student_status, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/v1.0/student-statuses-update",
     *      operationId="updateStudentStatus",
     *      tags={"student.student_statuses"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update student status ",
     *      description="This method is to update student status",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
*      @OA\Property(property="id", type="number", format="number", example="Updated Christmas"),
 * @OA\Property(property="name", type="string", format="string", example="tttttt"),
 *  *  * @OA\Property(property="color", type="string", format="string", example="red"),
 * @OA\Property(property="description", type="string", format="string", example="erg ear ga&nbsp;")


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

    public function updateStudentStatus(StudentStatusUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('student_status_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();



                $student_status_query_params = [
                    "id" => $request_data["id"],
                ];


                $student_status  =  tap(StudentStatus::where($student_status_query_params))->update(
                    collect($request_data)->only([
                        'name',
                        'color',
                        'description',
                        // "is_active",
                        // "business_id",

                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();
                if (!$student_status) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }

                return response($student_status, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

  /**
     *
     * @OA\Post(
     *      path="/v1.0/student-statuses/toggle-active",
     *      operationId="toggleActiveStudentStatus",
     *      tags={"student.student_statuses"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to toggle student status",
     *      description="This method is to toggle student status",
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

     public function toggleActiveStudentStatus(GetIdRequest $request)
     {

         try {
             $this->storeActivity($request, "DUMMY activity", "DUMMY description");
             if (!$request->user()->hasPermissionTo('student_status_activate')) {
                 return response()->json([
                     "message" => "You can not perform this action"
                 ], 401);
             }
             $request_data = $request->validated();

             $student_status =  StudentStatus::where([
                 "id" => $request_data["id"],
             ])
                 ->first();
             if (!$student_status) {
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
             $should_update = 0;
             $should_disable = 0;
             if (empty(auth()->user()->business_id)) {

                 if (auth()->user()->hasRole('superadmin')) {
                     if (($student_status->business_id != NULL || $student_status->is_default != 1)) {
                        $this->storeError(
                            "You do not have permission to update this due to role restrictions.",
                            403,
                            "front end error",
                            "front end error"
                           );
                         return response()->json([
                             "message" => "You do not have permission to update this student status due to role restrictions."
                         ], 403);
                     } else {
                         $should_update = 1;
                     }
                 } else {
                     if ($student_status->business_id != NULL) {
                        $this->storeError(
                            "You do not have permission to update this due to role restrictions.",
                            403,
                            "front end error",
                            "front end error"
                           );
                         return response()->json([
                             "message" => "You do not have permission to update this student status due to role restrictions."
                         ], 403);
                     } else if ($student_status->is_default == 0) {

                         if($student_status->created_by != auth()->user()->id) {
                            $this->storeError(
                                "You do not have permission to update this due to role restrictions.",
                                403,
                                "front end error",
                                "front end error"
                               );
                             return response()->json([
                                 "message" => "You do not have permission to update this student status due to role restrictions."
                             ], 403);
                         }
                         else {
                             $should_update = 1;
                         }



                     }
                     else {
                      $should_disable = 1;

                     }
                 }
             } else {
                 if ($student_status->business_id != NULL) {
                     if (($student_status->business_id != auth()->user()->business_id)) {
                        $this->storeError(
                            "You do not have permission to update this due to role restrictions.",
                            403,
                            "front end error",
                            "front end error"
                           );
                         return response()->json([
                             "message" => "You do not have permission to update this student status due to role restrictions."
                         ], 403);
                     } else {
                         $should_update = 1;
                     }
                 } else {
                     if ($student_status->is_default == 0) {
                         if ($student_status->created_by != auth()->user()->created_by) {
                            $this->storeError(
                                "You do not have permission to update this due to role restrictions.",
                                403,
                                "front end error",
                                "front end error"
                               );
                             return response()->json([
                                 "message" => "You do not have permission to update this student status due to role restrictions."
                             ], 403);
                         } else {
                             $should_disable = 1;

                         }
                     } else {
                         $should_disable = 1;

                     }
                 }
             }

             if ($should_update) {
                 $student_status->update([
                     'is_active' => !$student_status->is_active
                 ]);
             }

             if($should_disable) {

                 $disabled_student_status =    DisabledStudentStatus::where([
                     'student_status_id' => $student_status->id,
                     'business_id' => auth()->user()->business_id,
                     'created_by' => auth()->user()->id,
                 ])->first();
                 if(!$disabled_student_status) {
                    DisabledStudentStatus::create([
                         'student_status_id' => $student_status->id,
                         'business_id' => auth()->user()->business_id,
                         'created_by' => auth()->user()->id,
                     ]);
                 } else {
                     $disabled_student_status->delete();
                 }
             }


             return response()->json(['message' => 'student status status updated successfully'], 200);
         } catch (Exception $e) {
             error_log($e->getMessage());
             return $this->sendError($e, 500, $request);
         }
     }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/student-statuses",
     *      operationId="getStudentStatuses",
     *      tags={"student.student_statuses"},
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

     *      summary="This method is to get student statuses  ",
     *      description="This method is to get student statuses ",
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

    public function getStudentStatuses(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('student_status_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $created_by  = NULL;
            if(auth()->user()->business) {
                $created_by = auth()->user()->business->created_by;
            }


            $student_statuses = StudentStatus::when(empty($request->user()->business_id), function ($query) use ($request, $created_by) {
                if (auth()->user()->hasRole('superadmin')) {
                    return $query->where('student_statuses.business_id', NULL)
                        ->where('student_statuses.is_default', 1)
                        ->when(isset($request->is_active), function ($query) use ($request) {
                            return $query->where('student_statuses.is_active', intval($request->is_active));
                        });
                } else {
                    return $query

                    ->where(function($query) use($request) {
                        $query->where('student_statuses.business_id', NULL)
                        ->where('student_statuses.is_default', 1)
                        ->where('student_statuses.is_active', 1)
                        ->when(isset($request->is_active), function ($query) use ($request) {
                            if(intval($request->is_active)) {
                                return $query->whereDoesntHave("disabled", function($q) {
                                    $q->whereIn("disabled_student_statuses.created_by", [auth()->user()->id]);
                                });
                            }

                        })
                        ->orWhere(function ($query) use ($request) {
                            $query->where('student_statuses.business_id', NULL)
                                ->where('student_statuses.is_default', 0)
                                ->where('student_statuses.created_by', auth()->user()->id)
                                ->when(isset($request->is_active), function ($query) use ($request) {
                                    return $query->where('student_statuses.is_active', intval($request->is_active));
                                });
                        });

                    });
                }
            })
                ->when(!empty($request->user()->business_id), function ($query) use ($request, $created_by) {
                    return $query
                    ->where(function($query) use($request, $created_by) {


                        $query->where('student_statuses.business_id', NULL)
                        ->where('student_statuses.is_default', 1)
                        ->where('student_statuses.is_active', 1)
                        ->whereDoesntHave("disabled", function($q) use($created_by) {
                            $q->whereIn("disabled_student_statuses.created_by", [$created_by]);
                        })
                        ->when(isset($request->is_active), function ($query) use ($request, $created_by)  {
                            if(intval($request->is_active)) {
                                return $query->whereDoesntHave("disabled", function($q) use($created_by) {
                                    $q->whereIn("disabled_student_statuses.business_id",[auth()->user()->business_id]);
                                });
                            }

                        })


                        ->orWhere(function ($query) use($request, $created_by){
                            $query->where('student_statuses.business_id', NULL)
                                ->where('student_statuses.is_default', 0)
                                ->where('student_statuses.created_by', $created_by)
                                ->where('student_statuses.is_active', 1)

                                ->when(isset($request->is_active), function ($query) use ($request) {
                                    if(intval($request->is_active)) {
                                        return $query->whereDoesntHave("disabled", function($q) {
                                            $q->whereIn("disabled_student_statuses.business_id",[auth()->user()->business_id]);
                                        });
                                    }

                                })


                                ;
                        })
                        ->orWhere(function ($query) use($request) {
                            $query->where('student_statuses.business_id', auth()->user()->business_id)
                                ->where('student_statuses.is_default', 0)
                                ->when(isset($request->is_active), function ($query) use ($request) {
                                    return $query->where('student_statuses.is_active', intval($request->is_active));
                                });;
                        });
                    });


                })
                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                        $query->where("student_statuses.name", "like", "%" . $term . "%")
                            ->orWhere("student_statuses.description", "like", "%" . $term . "%");
                    });
                })
                //    ->when(!empty($request->product_category_id), function ($query) use ($request) {
                //        return $query->where('product_category_id', $request->product_category_id);
                //    })
                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('student_statuses.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('student_statuses.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("student_statuses.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("student_statuses.id", "DESC");
                })
                ->when(!empty($request->per_page), function ($query) use ($request) {
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    return $query->get();
                });;



            return response()->json($student_statuses, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
 /**
     *
     * @OA\Get(
     *      path="/v1.0/client/student-statuses",
     *      operationId="getStudentStatusesClient",
     *      tags={"student.student_statuses"},
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

     *      summary="This method is to get student statuses  ",
     *      description="This method is to get student statuses ",
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

     public function getStudentStatusesClient(Request $request)
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


             $student_statuses = StudentStatus::
             where('student_statuses.business_id', $business_id)
             ->where('student_statuses.is_active', 1)
                 ->when(!empty($request->search_key), function ($query) use ($request) {
                     return $query->where(function ($query) use ($request) {
                         $term = $request->search_key;
                         $query->where("student_statuses.name", "like", "%" . $term . "%")
                             ->orWhere("student_statuses.description", "like", "%" . $term . "%");
                     });
                 })
                 //    ->when(!empty($request->product_category_id), function ($query) use ($request) {
                 //        return $query->where('product_category_id', $request->product_category_id);
                 //    })
                 ->when(!empty($request->start_date), function ($query) use ($request) {
                     return $query->where('student_statuses.created_at', ">=", $request->start_date);
                 })
                 ->when(!empty($request->end_date), function ($query) use ($request) {
                     return $query->where('student_statuses.created_at', "<=", ($request->end_date . ' 23:59:59'));
                 })
                 ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                     return $query->orderBy("student_statuses.id", $request->order_by);
                 }, function ($query) {
                     return $query->orderBy("student_statuses.id", "DESC");
                 })
                 ->when(!empty($request->per_page), function ($query) use ($request) {
                     return $query->paginate($request->per_page);
                 }, function ($query) {
                     return $query->get();
                 });;



             return response()->json($student_statuses, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/student-statuses/{id}",
     *      operationId="getStudentStatusById",
     *      tags={"student.student_statuses"},
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
     *      summary="This method is to get student status by id",
     *      description="This method is to get student status by id",
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


    public function getStudentStatusById($id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('student_status_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $student_status =  StudentStatus::where([
                "id" => $id,

            ])
                ->first();
                if (!$student_status) {
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
                        if (($student_status->business_id != NULL || $student_status->is_default != 1)) {
                            $this->storeError(
                                "You do not have permission to update this due to role restrictions.",
                                403,
                                "front end error",
                                "front end error"
                               );
                            return response()->json([
                                "message" => "You do not have permission to update this student status due to role restrictions."
                            ], 403);
                        }
                    } else {
                        if ($student_status->business_id != NULL) {
                            $this->storeError(
                                "You do not have permission to update this due to role restrictions.",
                                403,
                                "front end error",
                                "front end error"
                               );
                            return response()->json([
                                "message" => "You do not have permission to update this student status due to role restrictions."
                            ], 403);
                        } else if ($student_status->is_default == 0 && $student_status->created_by != auth()->user()->id) {
                            $this->storeError(
                                "You do not have permission to update this due to role restrictions.",
                                403,
                                "front end error",
                                "front end error"
                               );
                                return response()->json([
                                    "message" => "You do not have permission to update this student status due to role restrictions."
                                ], 403);

                        }
                    }
                } else {
                    if ($student_status->business_id != NULL) {
                        if (($student_status->business_id != auth()->user()->business_id)) {
                            $this->storeError(
                                "You do not have permission to update this due to role restrictions.",
                                403,
                                "front end error",
                                "front end error"
                               );
                            return response()->json([
                                "message" => "You do not have permission to update this student status due to role restrictions."
                            ], 403);
                        }
                    } else {
                        if ($student_status->is_default == 0) {
                            if ($student_status->created_by != auth()->user()->created_by) {
                                $this->storeError(
                                    "You do not have permission to update this due to role restrictions.",
                                    403,
                                    "front end error",
                                    "front end error"
                                   );
                                return response()->json([
                                    "message" => "You do not have permission to update this student status due to role restrictions."
                                ], 403);
                            }
                        }
                    }
                }


            return response()->json($student_status, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     *     @OA\Post(
     *      path="/v1.0/student-statuses/{ids}",
     *      operationId="deleteStudentStatusesByIds",
     *      tags={"student.student_statuses"},
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
     *      summary="This method is to delete student statuses by ids",
     *      description="This method is to delete student statuses by ids",
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


    public function deleteStudentStatusesByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('student_status_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $idsArray = explode(',', $ids);
            $existingIds = StudentStatus::whereIn('id', $idsArray)
            ->when(empty($request->user()->business_id), function ($query) use ($request) {
                if ($request->user()->hasRole("superadmin")) {
                    return $query->where('student_statuses.business_id', NULL)
                        ->where('student_statuses.is_default', 1);
                } else {
                    return $query->where('student_statuses.business_id', NULL)
                        ->where('student_statuses.is_default', 0)
                        ->where('student_statuses.created_by', $request->user()->id);
                }
            })
            ->when(!empty($request->user()->business_id), function ($query) use ($request) {
                return $query->where('student_statuses.business_id', $request->user()->business_id)
                    ->where('student_statuses.is_default', 0);
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

            $user_exists =  Student::whereIn("student_status_id",$existingIds)->exists();
            if($user_exists) {

                return response()->json([
                    "message" => "Some students are associated with the specified student statuses",
                ], 409);

            }

            StudentStatus::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully","deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
