<?php

namespace App\Http\Controllers;

use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use Illuminate\Http\Request;

class AttendanceController extends Controller
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
            // Log the user's activity for creating a class routine
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start a database transaction to ensure data consistency
            return DB::transaction(function () use ($request) {
                // Check if the authenticated user has permission to create a class routine
                if (!auth()->user()->hasPermissionTo('class_routine_create')) {
                    // If not, return a 401 Unauthorized response
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Validate the request data
                $request_data = $request->validated();

                // Set the default status for the class routine as active
                $request_data["is_active"] = 1;

                // Set the user who created this class routine
                $request_data["created_by"] = auth()->user()->id;

                // Set the business ID from the authenticated user's business ID
                $request_data["business_id"] = auth()->user()->business_id;

                // If the user does not belong to a business
                if (empty(auth()->user()->business_id)) {
                    // Set business ID to NULL
                    $request_data["business_id"] = NULL;
                    // If the user has a 'superadmin' role, mark this class routine as default
                    if (auth()->user()->hasRole('superadmin')) {
                        $request_data["is_default"] = 1;
                    }
                }

                // Create a new class routine with the validated data
                $class_routine = ClassRoutine::create($request_data);

                // Return the newly created class routine with a 201 Created response
                return response($class_routine, 201);
            });
        } catch (Exception $e) {
            // If an exception occurs, handle the error by returning a 500 Internal Server Error response
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

    /**
     * Updates an existing class routine
     *
     * @param ClassRoutineUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateClassRoutine(ClassRoutineUpdateRequest $request)
    {

        try {
            // Store the activity for updating a class routine
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start a transaction to ensure data consistency
            return DB::transaction(function () use ($request) {
                // Check if the authenticated user has permission to update a class routine
                if (!auth()->user()->hasPermissionTo('class_routine_update')) {
                    // If not, return a 401 Unauthorized response
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Validate the request data
                $request_data = $request->validated();

                // Extract the class routine ID from the validated data
                $class_routine_id = $request_data["id"];

                // Find the class routine by the provided ID
                $class_routine_query_params = [
                    "id" => $class_routine_id,
                ];

                $class_routine = ClassRoutine::where($class_routine_query_params)->first();

                if ($class_routine) {
                    // Fill the class routine object with the validated request data
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

                    // Save the changes to the class routine
                    $class_routine->save();

                    // Return the updated class routine
                    return response($class_routine, 201);
                } else {
                    // Return a 500 Internal Server Error response if something went wrong
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }
            });
        } catch (Exception $e) {
            // Log the error
            error_log($e->getMessage());

            // Return a 500 Internal Server Error response with the error message
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

    /**
     * Gets a list of class routines based on the given filters.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClassRoutines(Request $request)
    {
        try {
            // Store the activity for getting class routines
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has permission to view class routines
            if (!$request->user()->hasPermissionTo('class_routine_view')) {
                // If not, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Get the business ID of the user
            $business_id = auth()->user()->business_id;

            // Initialize the class routines query
            $class_routines = ClassRoutine::with("teacher", "subject", "semester","session");

            // Filter the class routines by business ID
            $class_routines->where('class_routines.business_id', $business_id);

            // Filter the class routines by ID
            if ($request->filled("id")) {
                $class_routines->where('class_routines.id', $request->id);
            }

            // Filter the class routines by start time
            if ($request->filled("start_time")) {
                $class_routines->where('class_routines.start_time', $request->start_time);
            }

            // Filter the class routines by end time
            if ($request->filled("end_time")) {
                $class_routines->where('class_routines.end_time', $request->end_time);
            }

            // Filter the class routines by room number
            if ($request->filled("room_number")) {
                $class_routines->where('class_routines.room_number', $request->room_number);
            }

            // Filter the class routines by search key
            if ($request->filled("search_key")) {
                $search_key = $request->search_key;
                $class_routines->where(function ($query) use ($search_key) {
                    // Search the class routines by start time, end time, room number, teacher name, subject name, and semester name
                    $query
                        ->where("class_routines.start_time", "like", "%" . $search_key . "%")
                        ->orWhere("class_routines.end_time", "like", "%" . $search_key . "%")
                        ->orWhere("class_routines.room_number", "like", "%" . $search_key . "%")
                        ->orWhere("teachers.name", "like", "%" . $search_key . "%")
                        ->orWhere("subjects.name", "like", "%" . $search_key . "%")
                        ->orWhere("semesters.name", "like", "%" . $search_key . "%")
                    ;
                });
            }

            // Filter the class routines by start date
            if ($request->filled("start_date")) {
                $class_routines->where('class_routines.created_at', ">=", $request->start_date);
            }

            // Filter the class routines by end date
            if ($request->filled("end_date")) {
                $class_routines->where('class_routines.created_at', "<=", ($request->end_date . ' 23:59:59'));
            }

            // Order the class routines by ID
            if ($request->filled("order_by") && in_array(strtoupper($request->order_by), ['ASC', 'DESC'])) {
                $class_routines->orderBy("class_routines.id", $request->order_by);
            } else {
                $class_routines->orderBy("class_routines.id", "DESC");
            }

            // Get the class routines
            if ($request->filled("id")) {
                $class_routines = $class_routines->where("class_routines.id", $request->input("id"))->first();
            } else {
                $class_routines = $class_routines->when(!empty(request()->per_page), function ($query) {
                    return $query->paginate(request()->per_page);
                }, function ($query) {
                    return $query->get();
                });
            }

            // If no data is found, throw a 404 Not Found exception
            if ($request->filled("id") && empty($class_routines)) {
                throw new Exception("No data found", 404);
            }

            // Return the class routines
            return response()->json($class_routines, 200);
        } catch (Exception $e) {

            // Return an error response if an exception is thrown
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

    /**
     * Deletes one or more class routines by ID.
     *
     * @param Request $request
     * @param string $ids
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteClassRoutinesByIds(Request $request, $ids)
    {
        try {

            // Log the user's activity in the database
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has permission to delete class routines
            if (!$request->user()->hasPermissionTo('class_routine_delete')) {
                // If the user does not have permission, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Split the given IDs by comma and convert to an array
            $idsArray = explode(',', $ids);

            // Retrieve the existing IDs in the database
            $existingIds = ClassRoutine::whereIn('id', $idsArray)
                // The class routines must belong to the same business as the user
                ->where('class_routines.business_id', auth()->user()->business_id)

                // Select only the 'id' column
                ->select('id')
                // Retrieve the data from the database
                ->get()
                // Convert the data to an array of IDs
                ->pluck('id')
                ->toArray();

            // Calculate the IDs that do not exist in the database
            $nonExistingIds = array_diff($idsArray, $existingIds);

            // If there are any non-existing IDs, return a 404 Not Found response
            if (!empty($nonExistingIds)) {

                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }

            // Delete the class routines
            ClassRoutine::destroy($existingIds);

            // Return a 200 OK response with a message indicating that the data was deleted successfully
            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            // Return an error response if an exception is thrown
            return $this->sendError($e, 500, $request);
        }
    }
}
