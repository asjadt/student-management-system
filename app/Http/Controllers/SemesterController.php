<?php





namespace App\Http\Controllers;

use App\Http\Requests\SemesterCreateRequest;
use App\Http\Requests\SemesterUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Semester;
use App\Models\DisabledSemester;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SemesterController extends Controller
{

    use ErrorUtil, UserActivityUtil, BusinessUtil;


    /**
     *
     * @OA\Post(
     *      path="/v1.0/semesters",
     *      operationId="createSemester",
     *      tags={"semesters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store semesters",
     *      description="This method is to store semesters",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     * @OA\Property(property="name", type="string", format="string", example="name"),
     * @OA\Property(property="start_date", type="string", format="string", example="start_date"),
     * @OA\Property(property="end_date", type="string", format="string", example="end_date"),
     *      * @OA\Property(property="break_start_date", type="string", format="string", example="break_start_date"),
     * @OA\Property(property="break_end_date", type="string", format="string", example="break_end_date"),

     * @OA\Property(property="course_ids", type="string", format="array", example={1,2,3})
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

    public function createSemester(SemesterCreateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!auth()->user()->hasPermissionTo('semester_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();



                $request_data["created_by"] = auth()->user()->id;
                $request_data["business_id"] = auth()->user()->business_id;

                if (empty(auth()->user()->business_id)) {
                    $request_data["business_id"] = NULL;
                    if (auth()->user()->hasRole('superadmin')) {
                        $request_data["is_default"] = 1;
                    }
                }


                $semester =  Semester::create($request_data);

                $semester->courses()->sync($request_data["course_ids"]);


                return response($semester, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     *      path="/v1.0/semesters",
     *      operationId="updateSemester",
     *      tags={"semesters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update semesters ",
     *      description="This method is to update semesters ",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *      @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="name", type="string", format="string", example="name"),
     * @OA\Property(property="start_date", type="string", format="string", example="start_date"),
     * @OA\Property(property="end_date", type="string", format="string", example="end_date"),
     *      *      * @OA\Property(property="break_start_date", type="string", format="string", example="break_start_date"),
     * @OA\Property(property="break_end_date", type="string", format="string", example="break_end_date"),

     * @OA\Property(property="course_ids", type="string", format="array", example={1,2,3})
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

    public function updateSemester(SemesterUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!auth()->user()->hasPermissionTo('semester_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $request_data = $request->validated();

                $semester_query_params = [
                    "id" => $request_data["id"],
                ];

                $semester = Semester::where($semester_query_params)->first();

                if ($semester) {
                    $semester->fill(collect($request_data)->only([

                        "name",
                        "start_date",
                        "end_date",
                        
                        "break_start_date",
                        "break_end_date",

                        // "is_default",
                        // "is_active",
                        // "business_id",
                        // "created_by"
                    ])->toArray());
                    $semester->save();
                } else {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }

                $semester->courses()->sync($request_data["course_ids"]);


                return response($semester, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


  /**
*
* @OA\Put(
*      path="/v1.0/semesters/toggle-active",
*      operationId="toggleActiveSemester",
*      tags={"semesters"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to toggle semesters",
*      description="This method is to toggle semesters",
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

public function toggleActiveSemester(GetIdRequest $request)
{

   try {

       $this->storeActivity($request, "DUMMY activity", "DUMMY description");

       if (!$request->user()->hasPermissionTo('semester_activate')) {
           return response()->json([
               "message" => "You can not perform this action"
           ], 401);
       }
       $request_data = $request->validated();

       $semester =  Semester::where([
           "id" => $request_data["id"],
       ])
           ->first();
       if (!$semester) {

           return response()->json([
               "message" => "no data found"
           ], 404);
       }

       $semester->update([
        'is_active' => !$semester->is_active
    ]);




       return response()->json(['message' => 'semester status updated successfully'], 200);
   } catch (Exception $e) {
       error_log($e->getMessage());
       return $this->sendError($e, 500, $request);
   }
}



    /**
     *
     * @OA\Get(
     *      path="/v1.0/semesters",
     *      operationId="getSemesters",
     *      tags={"semesters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *         @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="name",
     *         required=true,
     *  example="6"
     *      ),



     *         @OA\Parameter(
     *         name="start_start_date",
     *         in="query",
     *         description="start_start_date",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="end_start_date",
     *         in="query",
     *         description="end_start_date",
     *         required=true,
     *  example="6"
     *      ),



     *         @OA\Parameter(
     *         name="start_end_date",
     *         in="query",
     *         description="start_end_date",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="end_end_date",
     *         in="query",
     *         description="end_end_date",
     *         required=true,
     *  example="6"
     *      ),



     *         @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="per_page",
     *         required=true,
     *  example="6"
     *      ),

     *     @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=true,
     * example="1"
     * ),
     *     @OA\Parameter(
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
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=true,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="is_single_search",
     * in="query",
     * description="is_single_search",
     * required=true,
     * example="ASC"
     * ),




     *      summary="This method is to get semesters  ",
     *      description="This method is to get semesters ",
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

    public function getSemesters(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('semester_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }




            $semesters = Semester::with("courses")
            ->where('semesters.business_id', auth()->user()->business_id)



                ->when(!empty($request->id), function ($query) use ($request) {
                    return $query->where('semesters.id', $request->id);
                })

                ->when(!empty($request->name), function ($query) use ($request) {
                    return $query->where('semesters.id', $request->string);
                })





                ->when(!empty($request->start_start_date), function ($query) use ($request) {
                    return $query->where('semesters.start_date', ">=", $request->start_start_date);
                })
                ->when(!empty($request->end_start_date), function ($query) use ($request) {
                    return $query->where('semesters.start_date', "<=", ($request->end_start_date . ' 23:59:59'));
                })





                ->when(!empty($request->start_end_date), function ($query) use ($request) {
                    return $query->where('semesters.end_date', ">=", $request->start_end_date);
                })
                ->when(!empty($request->end_end_date), function ($query) use ($request) {
                    return $query->where('semesters.end_date', "<=", ($request->end_end_date . ' 23:59:59'));
                })





                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                        $query

                            ->orWhere("semesters.name", "like", "%" . $term . "%");
                    });
                })


                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('semesters.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('semesters.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("semesters.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("semesters.id", "DESC");
                })
                ->when($request->filled("id"), function ($query)  {
                    return $query
                    ->where('semesters.id',request()->input("id"))
                    ->first();
                }, function ($query) {
                    return $query->when(!empty(request()->per_page), function ($query) {
                        return $query->paginate(request()->per_page);
                    }, function ($query) {
                        return $query->get();
                    });
                });

            if ($request->filled("id") && empty($semesters)) {
                throw new Exception("No data found", 404);
            }


            return response()->json($semesters, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/semesters/{ids}",
     *      operationId="deleteSemestersByIds",
     *      tags={"semesters"},
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
     *      summary="This method is to delete semester by id",
     *      description="This method is to delete semester by id",
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

    public function deleteSemestersByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('semester_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $idsArray = explode(',', $ids);
            $existingIds = Semester::whereIn('id', $idsArray)
                ->where('semesters.business_id', auth()->user()->business_id)

                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            $nonExistingIds = array_diff($idsArray, $existingIds);

            if (!empty($nonExistingIds)) {

                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }





            Semester::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
