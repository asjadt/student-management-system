<?php





namespace App\Http\Controllers;

use App\Http\Requests\ClassRoutineCreateRequest;
use App\Http\Requests\ClassRoutineUpdateRequest;
use App\Http\Requests\ClassRoutineWeeklyCreateRequest;
use App\Http\Requests\ClassRoutineWeeklyUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\ClassRoutine;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassRoutineController extends Controller
{

    use ErrorUtil, UserActivityUtil, BusinessUtil;


    /**
     *
     * @OA\Post(
     *      path="/v1.0/class-routines",
     *      operationId="createClassRoutine",
     *      tags={"class_routines"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store class routines",
     *      description="This method is to store class routines",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     * @OA\Property(property="day_of_week", type="string", format="string", example="day_of_week"),
     * @OA\Property(property="start_time", type="string", format="string", example="start_time"),
     * @OA\Property(property="end_time", type="string", format="string", example="end_time"),
     * @OA\Property(property="room_number", type="string", format="string", example="room_number"),
     * @OA\Property(property="subject_id", type="string", format="string", example="subject_id"),
   * @OA\Property(property="course_id", type="string", format="string", example="course_id"),
     *
     * @OA\Property(property="teacher_id", type="string", format="string", example="teacher_id"),
     * @OA\Property(property="semester_id", type="string", format="string", example="semester_id"),
     * @OA\Property(property="session_id", type="string", format="string", example="session_id"),
     *
     *
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

    public function createClassRoutine(ClassRoutineCreateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!auth()->user()->hasPermissionTo('class_routine_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();

                $request_data["is_active"] = 1;





                $request_data["created_by"] = auth()->user()->id;
                $request_data["business_id"] = auth()->user()->business_id;

                if (empty(auth()->user()->business_id)) {
                    $request_data["business_id"] = NULL;
                    if (auth()->user()->hasRole('superadmin')) {
                        $request_data["is_default"] = 1;
                    }
                }




                $class_routine =  ClassRoutine::create($request_data);




                return response($class_routine, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
 * @OA\Post(
 *     path="/v1.0/class-routines/week",
 *     operationId="createWeeklyClassRoutine",
 *     tags={"class_routines"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     summary="This method is to store class routines for each day of the week",
 *     description="This method is to store class routines for multiple days with a common session ID",
 *
 * @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *     @OA\Property(
 *         property="course_data",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(
 *                 property="course_id",
 *                 type="integer",
 *                 example=1
 *             ),
 *             @OA\Property(
 *                 property="days",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="day_of_week", type="string", example="Monday"),
 *                     @OA\Property(property="start_time", type="string", format="time", example="09:00"),
 *                     @OA\Property(property="end_time", type="string", format="time", example="10:00"),
 *                     @OA\Property(property="room_number", type="string", example="101"),
 *                     @OA\Property(property="subject_id", type="integer", example=1),
 *                     @OA\Property(property="course_id", type="integer", example=1),
 *                     @OA\Property(property="teacher_id", type="integer", example=2)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Property(property="semester_id", type="string", example="semester_id"),
 *     @OA\Property(property="session_id", type="string", example="session_id")
 *
 *     ),
 * ),
 *
 * @OA\Response(
 *     response=200,
 *     description="Successful operation",
 *     @OA\JsonContent(),
 * ),
 * @OA\Response(
 *     response=401,
 *     description="Unauthenticated",
 *     @OA\JsonContent(),
 * ),
 * @OA\Response(
 *     response=422,
 *     description="Unprocessable Content",
 *     @OA\JsonContent(),
 * ),
 * @OA\Response(
 *     response=403,
 *     description="Forbidden",
 *     @OA\JsonContent(),
 * ),
 * @OA\Response(
 *     response=400,
 *     description="Bad Request",
 *     @OA\JsonContent(),
 * ),
 * @OA\Response(
 *     response=404,
 *     description="Not found",
 *     @OA\JsonContent(),
 * )
 * )
 */

 public function createWeeklyClassRoutine(ClassRoutineWeeklyCreateRequest $request)
 {
     try {
         $this->storeActivity($request, "DUMMY activity", "DUMMY description");

         DB::beginTransaction();

             if (!auth()->user()->hasPermissionTo('class_routine_create')) {
                 return response()->json([
                     "message" => "You cannot perform this action"
                 ], 401);
             }

             $request_data = $request->validated();

             $semester_id = $request_data['semester_id']??NULL;
             $session_id = $request_data['session_id'];

             $course_data = $request_data['course_data'];

             $created_routines = [];

             foreach ($course_data as $course) {
                // Ensure the course_id is available
                $course_id = $course['course_id'];

                foreach ($course['days'] as $day) {
                    $day['semester_id'] = $semester_id;
                    $day['session_id'] = $session_id;
                    $day['course_id'] = $course_id;  // Add course_id to each day's data
                    $day['is_active'] = 1;
                    $day['created_by'] = auth()->user()->id;
                    $day['business_id'] = auth()->user()->business_id ?? null;

                    // Check if business_id is empty and user is a superadmin to set is_default
                    if (empty(auth()->user()->business_id) && auth()->user()->hasRole('superadmin')) {
                        $day['is_default'] = 1;
                    }

                    // Create ClassRoutine instance for each day and store it in created_routines
                    $class_routine = ClassRoutine::create($day);
                    $created_routines[] = $class_routine;
                }
            }
             DB::commit();
             return response($created_routines, 201);
     } catch (Exception $e) {

        DB::rollBack();
         return $this->sendError($e, 500, $request);

     }
 }


 /**
 * @OA\Put(
 *     path="/v1.0/class-routines/week",
 *     operationId="updateWeeklyClassRoutine",
 *     tags={"class_routines"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     summary="This method is to update class routines for each day of the week",
 *     description="This method is to update class routines for multiple days with a common session ID",
 *
 * @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(
 *             property="course_data",
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="course_id", type="integer", example=1),
 *                 @OA\Property(property="days", type="array",
 *                     @OA\Items(
 *                         @OA\Property(property="day_of_week", type="string", example="Monday"),
 *                         @OA\Property(property="start_time", type="string", format="time", example="09:00"),
 *                         @OA\Property(property="end_time", type="string", format="time", example="10:00"),
 *                         @OA\Property(property="room_number", type="string", example="101"),
 *                         @OA\Property(property="subject_id", type="integer", example=1),
 *                         @OA\Property(property="teacher_id", type="integer", example=2)
 *                     )
 *                 )
 *             )
 *         ),
 *         @OA\Property(property="semester_id", type="string", example="semester_id"),
 *         @OA\Property(property="session_id", type="string", example="session_id")
 *     ),
 * ),
 *
 * @OA\Response(
 *     response=200,
 *     description="Successful operation",
 *     @OA\JsonContent(),
 * ),
 * @OA\Response(
 *     response=401,
 *     description="Unauthenticated",
 *     @OA\JsonContent(),
 * ),
 * @OA\Response(
 *     response=422,
 *     description="Unprocessable Content",
 *     @OA\JsonContent(),
 * ),
 * @OA\Response(
 *     response=403,
 *     description="Forbidden",
 *     @OA\JsonContent(),
 * ),
 * @OA\Response(
 *     response=400,
 *     description="Bad Request",
 *     @OA\JsonContent(),
 * ),
 * @OA\Response(
 *     response=404,
 *     description="Not found",
 *     @OA\JsonContent(),
 * )
 * )
 */
public function updateWeeklyClassRoutine(ClassRoutineWeeklyUpdateRequest $request)
{
    try {
        $this->storeActivity($request, "DUMMY activity", "DUMMY description");

        DB::beginTransaction();

        if (!auth()->user()->hasPermissionTo('class_routine_update')) {
            return response()->json([
                "message" => "You cannot perform this action"
            ], 401);
        }

        $request_data = $request->validated();

        // Get the ID from the request body
        $routine_id = $request_data['id'];

        // Ensure the requested data exists
        $semester_id = $request_data['semester_id'] ?? NULL;
        $session_id = $request_data['session_id'];
        $course_data = $request_data['course_data'];

        $updated_routines = [];

        foreach ($course_data as $course) {
            $course_id = $course['course_id'];

            foreach ($course['days'] as $day) {
                $existing_routine = ClassRoutine::find($routine_id);

                if (!$existing_routine) {
                    return response()->json([
                        'message' => 'Routine not found'
                    ], 404);
                }

                // Update the existing routine
                $day['semester_id'] = $semester_id;
                $day['session_id'] = $session_id;
                $day['course_id'] = $course_id;  // Ensure course_id is set
                $day['is_active'] = 1;
                $day['updated_by'] = auth()->user()->id;
                $day['business_id'] = auth()->user()->business_id ?? null;

                if (empty(auth()->user()->business_id) && auth()->user()->hasRole('superadmin')) {
                    $day['is_default'] = 1;
                }

                $existing_routine->fill($day);
                $existing_routine->save();

                $updated_routines[] = $existing_routine;
            }
        }

        DB::commit();

        return response($updated_routines, 200);
    } catch (Exception $e) {
        DB::rollBack();
        return $this->sendError($e, 500, $request);
    }
}



    /**
     *
     * @OA\Put(
     *      path="/v1.0/class-routines",
     *      operationId="updateClassRoutine",
     *      tags={"class_routines"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update class routines ",
     *      description="This method is to update class routines ",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *      @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="day_of_week", type="string", format="string", example="day_of_week"),
     * @OA\Property(property="start_time", type="string", format="string", example="start_time"),
     * @OA\Property(property="end_time", type="string", format="string", example="end_time"),
     * @OA\Property(property="room_number", type="string", format="string", example="room_number"),
     * @OA\Property(property="subject_id", type="string", format="string", example="subject_id"),
    * @OA\Property(property="course_id", type="string", format="string", example="course_id"),
     *
     * @OA\Property(property="teacher_id", type="string", format="string", example="teacher_id"),
     * @OA\Property(property="semester_id", type="string", format="string", example="semester_id"),
      * @OA\Property(property="session_id", type="string", format="string", example="session_id"),
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

    public function updateClassRoutine(ClassRoutineUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!auth()->user()->hasPermissionTo('class_routine_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $request_data = $request->validated();



                $class_routine_query_params = [
                    "id" => $request_data["id"],
                ];

                $class_routine = ClassRoutine::where($class_routine_query_params)->first();

                if ($class_routine) {
                    $class_routine->fill(collect($request_data)->only([

                        "day_of_week",
                        "start_time",
                        "end_time",
                        "room_number",
                        "subject_id",
                        "course_id",
                        "teacher_id",
                        "semester_id",
                        "session_id"
                        // "is_default",
                        // "is_active",
                        // "business_id",
                        // "created_by"
                    ])->toArray());
                    $class_routine->save();
                } else {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }




                return response($class_routine, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Put(
     *      path="/v1.0/class-routines/toggle-active",
     *      operationId="toggleActiveClassRoutine",
     *      tags={"class_routines"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to toggle class routines",
     *      description="This method is to toggle class routines",
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

    public function toggleActiveClassRoutine(GetIdRequest $request)
    {

        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('class_routine_activate')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $request_data = $request->validated();

            $class_routine =  ClassRoutine::where([
                "id" => $request_data["id"],
            ])
                ->first();
            if (!$class_routine) {

                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            $class_routine->update([
                'is_active' => !$class_routine->is_active
            ]);




            return response()->json(['message' => 'class routine status updated successfully'], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     * @OA\Get(
     *      path="/v1.0/class-routines",
     *      operationId="getClassRoutines",
     *      tags={"class_routines"},
     *       security={
     *           {"bearerAuth": {}}
     *       },


     *         @OA\Parameter(
     *         name="start_time",
     *         in="query",
     *         description="start_time",
     *         required=true,
     *  example="6"
     *      ),



     *         @OA\Parameter(
     *         name="end_time",
     *         in="query",
     *         description="end_time",
     *         required=true,
     *  example="6"
     *      ),



     *         @OA\Parameter(
     *         name="room_number",
     *         in="query",
     *         description="room_number",
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




     *      summary="This method is to get class routines  ",
     *      description="This method is to get class routines ",
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

    public function getClassRoutines(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('class_routine_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $created_by  = NULL;
            if (auth()->user()->business) {
                $created_by = auth()->user()->business->created_by;
            }



            $class_routines = ClassRoutine::with("teacher", "subject", "semester")
                ->where('class_routines.business_id', auth()->user()->business_id)



                ->when(!empty($request->id), function ($query) use ($request) {
                    return $query->where('class_routines.id', $request->id);
                })


                ->when(!empty($request->start_time), function ($query) use ($request) {
                    return $query->where('class_routines.id', $request->string);
                })





                ->when(!empty($request->end_time), function ($query) use ($request) {
                    return $query->where('class_routines.id', $request->string);
                })





                ->when(!empty($request->room_number), function ($query) use ($request) {
                    return $query->where('class_routines.id', $request->string);
                })


                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                        $query

                            ->where("class_routines.start_time", "like", "%" . $term . "%")
                            ->orWhere("class_routines.end_time", "like", "%" . $term . "%")
                            ->orWhere("class_routines.room_number", "like", "%" . $term . "%")
                        ;
                    });
                })


                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('class_routines.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('class_routines.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("class_routines.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("class_routines.id", "DESC");
                })
                ->when($request->filled("id"), function ($query) use ($request) {
                    return $query
                        ->where("class_routines.id", $request->input("id"))
                        ->first();
                }, function ($query) {
                    return $query->when(!empty(request()->per_page), function ($query) {
                        return $query->paginate(request()->per_page);
                    }, function ($query) {
                        return $query->get();
                    });
                });

            if ($request->filled("id") && empty($class_routines)) {
                throw new Exception("No data found", 404);
            }


            return response()->json($class_routines, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/class-routines/{ids}",
     *      operationId="deleteClassRoutinesByIds",
     *      tags={"class_routines"},
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
     *      summary="This method is to delete class routine by id",
     *      description="This method is to delete class routine by id",
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

    public function deleteClassRoutinesByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('class_routine_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $idsArray = explode(',', $ids);
            $existingIds = ClassRoutine::whereIn('id', $idsArray)
                ->where('class_routines.business_id', auth()->user()->business_id)

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





            ClassRoutine::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
