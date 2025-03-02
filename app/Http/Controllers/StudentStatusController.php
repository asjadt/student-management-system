<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentStatusCreateRequest;
use App\Http\Requests\StudentStatusUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\BusinessSetting;
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
    use ErrorUtil, UserActivityUtil, BusinessUtil, BasicUtil;
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
            // Store the activity for logging purpose
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start transaction
            return DB::transaction(function () use ($request) {
                // Check if the user has permission to create a new student status
                if (!$request->user()->hasPermissionTo('student_status_create')) {
                    // If not, return a 401 unauthorized response
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Validate the request and get the data
                $request_data = $request->validated();


                // Set the default values for the student status
                $request_data["is_active"] = 1;
                $request_data["is_default"] = 0;


                // Set the created by and business id from the authenticated user
                $request_data["created_by"] = $request->user()->id;
                $request_data["business_id"] = $request->user()->business_id;


                // If the user is a super admin and has no business id
                if (empty($request->user()->business_id)) {
                    // Set the business id to null
                    $request_data["business_id"] = NULL;

                    // If the user is a super admin
                    if ($request->user()->hasRole('superadmin')) {
                        // Set the is_default to 1
                        $request_data["is_default"] = 1;
                    }
                }


                // Create the student status
                $student_status =  StudentStatus::create($request_data);


                // Return the student status as a response with a 201 status code
                return response($student_status, 201);
            });
        } catch (Exception $e) {
            // Log the error
            error_log($e->getMessage());

            // Return the error with a 500 status code
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Put(
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
        /**
         * This method will update a student status.
         *
         * @param StudentStatusUpdateRequest $request
         * @return \Illuminate\Http\Response
         */
        try {
            // Log this action
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            return DB::transaction(function () use ($request) {
                // Check if the user has permission to update student statuses
                if (!$request->user()->hasPermissionTo('student_status_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Get the validated request data
                $request_data = $request->validated();

                // Set the query params for the student status model
                $student_status_query_params = [
                    "id" => $request_data["id"],
                    "business_id" => auth()->user()->business_id

                ];

                // Find the student status and update it
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

                // If the student status was not found, return an error
                if (!$student_status) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }

                // Return the updated student status
                return response($student_status, 201);
            });
        } catch (Exception $e) {
            // Log the error
            error_log($e->getMessage());
            // Return the error with a 500 status code
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Put(
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
            // Log the activity with a dummy description for tracking purposes
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the necessary permission to activate student status
            if (!$request->user()->hasPermissionTo('student_status_activate')) {
                // If not, return an unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Validate the incoming request and retrieve the validated data
            $request_data = $request->validated();

            // Call the toggleActivation method to either enable or disable the student status
            // Pass the necessary class names and identifiers to perform the toggling action
            $this->toggleActivation(
                StudentStatus::class,        // The primary model class
                DisabledStudentStatus::class, // The model class representing the disabled state
                'student_status_id',         // The ID attribute name
                $request_data["id"],         // The specific ID to toggle
                auth()->user()               // The currently authenticated user
            );

            // Return a success response if the operation was completed
            return response()->json(['message' => 'student status status updated successfully'], 200);
        } catch (Exception $e) {
            // Log any exceptions that occur for debugging purposes
            error_log($e->getMessage());

            // Return a generic error response with status 500
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     * Filter the student statuses based on the query parameters provided.
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function query_filters($query)
    {
        // Get the business setting for the current business
        $business_setting = BusinessSetting::where([
            "business_id" => auth()->user()->business_id
        ])
            ->first();

        // Filter the student statuses based on the business id
        $query->where([
            "business_id" => auth()->user()->business_id
        ]);

        // If a search key is provided, filter the student statuses that match the search key
        if (!empty(request()->search_key)) {
            $query->where(function ($query) {
                $term = request()->search_key;
                // Search for the term in the name and description columns
                $query->where("student_statuses.name", "like", "%" . $term . "%")
                    ->orWhere("student_statuses.description", "like", "%" . $term . "%");
            });
        }

        // If the exclude_online_status parameter is true, exclude the online student status
        if (request()->boolean("exclude_online_status")) {
            $query->whereNotIn('student_statuses.id', [$business_setting->online_student_status_id]);
        }

        // If a start date is provided, filter the student statuses that are created after the start date
        if (!empty(request()->start_date)) {
            $query->where('student_statuses.created_at', ">=", request()->start_date);
        }

        // If an end date is provided, filter the student statuses that are created before the end date
        if (!empty(request()->end_date)) {
            $query->where('student_statuses.created_at', "<=", (request()->end_date . ' 23:59:59'));
        }

        return $query;
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
            // Store the activity of the user
            // This is just a placeholder, the real activity will be stored later
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the permission to view student statuses
            if (!$request->user()->hasPermissionTo('student_status_view')) {
                // If the user doesn't have the permission, return a 401 response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Get the query builder for the student statuses
            $query = StudentStatus::query();

            // Apply the filters to the query
            $query = $this->query_filters($query);

            // Retrieve the student statuses using the query builder
            // This will return a collection of student statuses
            $student_statuses = $this->retrieveData($query, "id", "student_statuses");

            // Return the collection of student statuses as a JSON response
            return response()->json($student_statuses, 200);
        } catch (Exception $e) {

            // If an error occurs, return a 500 response with the error message
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Get(
     *      path="/v2.0/student-statuses",
     *      operationId="getStudentStatusesV2",
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

    public function getStudentStatusesV2(Request $request)
    {
        // This method is to get all student statuses
        try {
            // Store the activity of the user
            // This is just a placeholder, the real activity will be stored later
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the permission to view student statuses
            if (!$request->user()->hasPermissionTo('student_status_view')) {
                // If the user doesn't have the permission, return a 401 response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Get the query builder for the student statuses
            $query = StudentStatus::query();

            // Apply the filters to the query
            // The filters are defined in the query_filters method
            $query = $this->query_filters($query)
                ->select(
                    'student_statuses.id',
                    'student_statuses.name',
                    'student_statuses.description',
                    "student_statuses.is_active",
                    "student_statuses.business_id",
                );

            // Retrieve the student statuses using the query builder
            // This will return a collection of student statuses
            $student_statuses = $this->retrieveData($query, "id", "student_statuses");

            // Return the collection of student statuses as a JSON response
            return response()->json($student_statuses, 200);
        } catch (Exception $e) {

            // If an error occurs, return a 500 response with the error message
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     * Filters the student statuses query based on the provided parameters.
     *
     * The query will be filtered based on the following parameters:
     * - business_id: The id of the business.
     * - search_key: The search key to filter the student statuses.
     * - start_date: The start date of the range to filter the student statuses.
     * - end_date: The end date of the range to filter the student statuses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query_filters_v2($query)
    {
        // Get the business id from the request
        $business_id =  request()->business_id;

        // If the business id is not provided, return a 422 response with an error message
        if (!$business_id) {
            $error = [
                "message" => "The given data was invalid.",
                "errors" => ["business_id" => ["The business id field is required."]]
            ];
            throw new Exception(json_encode($error), 422);
        }

        // Filter the student statuses based on the business id and active status
        $query->where('student_statuses.is_active', 1)
            ->where('student_statuses.business_id', $business_id);

        // If a search key is provided, filter the student statuses that match the search key
        if (!empty(request()->search_key)) {
            $query->where(function ($query) {
                $term = request()->search_key;
                $query->where("student_statuses.name", "like", "%" . $term . "%")
                    ->orWhere("student_statuses.description", "like", "%" . $term . "%");
            });
        }

        // If a start date is provided, filter the student statuses that are created after the start date
        if (!empty(request()->start_date)) {
            $query->where('student_statuses.created_at', ">=", request()->start_date);
        }

        // If an end date is provided, filter the student statuses that are created before the end date
        if (!empty(request()->end_date)) {
            $query->where('student_statuses.created_at', "<=", (request()->end_date . ' 23:59:59'));
        }

        return $query;
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

    /**
     * This method is to get all student statuses.
     * This method is not using the paginate method from the parent class.
     * Instead, it is using the retrieveData method from the parent class.
     * The retrieveData method takes the query builder as an argument and returns
     * the data in a format that is suitable for the API.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getStudentStatusesClient(Request $request)
    {
        try {
            // Store the activity of the user
            // This is just a placeholder, the real activity will be stored later
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Get the query builder for the student statuses
            $query = StudentStatus::query();

            // Apply the filters to the query
            // The filters are defined in the query_filters method
            $query = $this->query_filters_v2($query);

            // Retrieve the student statuses using the query builder
            // This will return a collection of student statuses
            $student_statuses = $this->retrieveData($query, "id", "student_statuses");

            // Return the collection of student statuses as a JSON response
            return response()->json($student_statuses, 200);
        } catch (Exception $e) {

            // If an error occurs, return a 500 response with the error message
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Get(
     *      path="/v2.0/client/student-statuses",
     *      operationId="getStudentStatusesClientV2",
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

    /**
     * @OA\Get(
     *      path="/v1.0/client/student-statuses-v2",
     *      operationId="getStudentStatusesClientV2",
     *      tags={"student.student_statuses"},
     *      summary="This method is to get student statuses for client v2",
     *      description="This method is to get student statuses for client v2",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found",
     *          @OA\JsonContent(),
     *      )
     *      )
     *     )
     */
    public function getStudentStatusesClientV2(Request $request)
    {
        try {
            // Store the activity for logging purpose
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Create a query builder for the student statuses model
            $query = StudentStatus::query();

            // Apply the filters to the query builder
            $query = $this->query_filters_v2($query);

            // Select the columns to be retrieved
            $query = $query
                ->select(
                    'student_statuses.id',
                    'student_statuses.name'
                );

            // Execute the query and retrieve the data
            $student_statuses = $this->retrieveData($query, "id", "student_statuses");

            // Return the student statuses as a response with a 200 status code
            return response()->json($student_statuses, 200);
        } catch (Exception $e) {
            // Log any exceptions that occur for debugging purposes
            error_log($e->getMessage());

            // Return a generic error response with status 500
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
            // Store the activity of the user for logging purposes
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the permission to view student statuses
            if (!$request->user()->hasPermissionTo('student_status_view')) {
                // If the user doesn't have the permission, return a 401 response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Get the student status by id
            $student_status =  StudentStatus::where([
                "id" => $id,
            ])
                // Check if the student status belongs to the current business
                ->where([
                    "business_id" => auth()->user()->business_id
                ])
                ->first();

            // If the student status is not found, return a 404 response
            if (!$student_status) {
                // Store an error for debugging purposes
                $this->storeError(
                    "no data found",
                    404,
                    "front end error",
                    "front end error"
                );
                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            // Check if the user has a business id
            if (empty(auth()->user()->business_id)) {

                // If the user is a super admin
                if (auth()->user()->hasRole('superadmin')) {
                    // Check if the student status belongs to a business
                    if (($student_status->business_id != NULL || $student_status->is_default != 1)) {
                        // If the student status belongs to a business, check if the user has the permission to update the student status
                        // If the user doesn't have the permission, return a 403 response
                        if (!$request->user()->hasPermissionTo('student_status_update')) {
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
                    // If the user is not a super admin, check if the student status belongs to a business
                    if ($student_status->business_id != NULL) {
                        // If the student status belongs to a business, check if the user has the permission to update the student status
                        // If the user doesn't have the permission, return a 403 response
                        if (!$request->user()->hasPermissionTo('student_status_update')) {
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
                        // If the student status doesn't belong to a business, check if the user has the permission to update the student status
                        // If the user doesn't have the permission, return a 403 response
                        if ($student_status->is_default == 0 && $student_status->created_by != auth()->user()->id) {
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
            } else {
                // If the user has a business id, check if the student status belongs to the user's business
                if ($student_status->business_id != NULL) {
                    // If the student status belongs to the user's business, check if the user has the permission to update the student status
                    // If the user doesn't have the permission, return a 403 response
                    if ($student_status->business_id != auth()->user()->business_id) {
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
                    // If the student status doesn't belong to a business, check if the user has the permission to update the student status
                    // If the user doesn't have the permission, return a 403 response
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

            // Return the student status as a JSON response
            return response()->json($student_status, 200);
        } catch (Exception $e) {

            // If an error occurs, return a 500 response with the error message
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     *     @OA\Delete(
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
            // Log this action
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has permission to delete student statuses
            if (!$request->user()->hasPermissionTo('student_status_delete')) {
                // If not, return a 401 unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Split the ids parameter into an array
            $idsArray = explode(',', $ids);

            // Get the existing ids from the database
            $existingIds = StudentStatus::whereIn('id', $idsArray)
                ->where([
                    "business_id" => auth()->user()->business_id
                ])
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();

            // Get the non-existing ids from the array
            $nonExistingIds = array_diff($idsArray, $existingIds);

            // If there are non-existing ids, return a 404 response
            if (!empty($nonExistingIds)) {
                $this->storeError(
                    "no data found",
                    404,
                    "front end error",
                    "front end error"
                );
                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }

            // Check if there are any students associated with the existing student statuses
            $user_exists =  Student::whereIn("student_status_id", $existingIds)->exists();
            if ($user_exists) {
                // If there are, return a 409 response
                return response()->json([
                    "message" => "Some students are associated with the specified student statuses",
                ], 409);
            }

            // If there are no students associated with the existing student statuses, delete the student statuses
            StudentStatus::destroy($existingIds);

            // Return a success response with the deleted ids
            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {
            // If an error occurs, return a 500 response with the error message
            return $this->sendError($e, 500, $request);
        }
    }
}
