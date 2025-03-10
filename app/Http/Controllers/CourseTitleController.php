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
use App\Models\Student;
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

     * @OA\Property(property="description", type="string", format="string", example="erg ear ga&nbsp;"),
     *  @OA\Property(property="awarding_body_id", type="string", format="string", example="awarding_body_id")
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

    /**
     * Creates a new course title.
     *
     * This function creates a new course title from the given request data.
     * The request data is validated using the CourseTitleCreateRequest validator.
     * The function first checks if the user has the right permission to create a course title.
     * If the user does not have the right permission, it returns a 401 Unauthorized response.
     * If the user has the right permission, it then creates a new course title using the validated request data.
     * The course title's is_active field is set to 1, indicating that the course title is active.
     * The course title's is_default field is set to 0, indicating that the course title is not the default course title.
     * If the user is a superadmin, the is_default field is set to 1, indicating that the course title is the default course title.
     * The course title's created_by field is set to the id of the user who created the course title.
     * The course title's business_id field is set to the id of the user's business.
     * If the user does not have a business, the business_id field is set to NULL.
     * The course title is then saved to the database.
     * If an exception is thrown during the creation of the course title, the function logs the error and returns a 500 Internal Server Error response.
     *
     * @param CourseTitleCreateRequest $request The request data to create a new course title.
     *
     * @return \Illuminate\Http\Response The response of the function.
     */
    public function createCourseTitle(CourseTitleCreateRequest $request)
    {
        try {
            // Log the activity of creating a new course title.
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            // Start a database transaction.
            return DB::transaction(function () use ($request) {
                // Check if the user has the right permission to create a course title.
                if (!$request->user()->hasPermissionTo('course_title_create')) {
                    // If the user does not have the right permission, return a 401 Unauthorized response.
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Get the validated request data.
                $request_data = $request->validated();

                // Set the course title's is_active field to 1.
                $request_data["is_active"] = 1;
                // Set the course title's is_default field to 0.
                $request_data["is_default"] = 0;

                // Set the course title's created_by field to the id of the user who created the course title.
                $request_data["created_by"] = $request->user()->id;
                // Set the course title's business_id field to the id of the user's business.
                $request_data["business_id"] = $request->user()->business_id;
                // If the user does not have a business, set the business_id field to NULL.
                if (empty($request->user()->business_id)) {
                    $request_data["business_id"] = NULL;
                    // If the user is a superadmin, set the is_default field to 1.
                    if ($request->user()->hasRole('superadmin')) {
                        $request_data["is_default"] = 1;
                    }
                }

                // Create a new course title using the validated request data.
                $course_title =  CourseTitle::create($request_data);




                // Return a 201 Created response with the new course title.
                return response($course_title, 201);
            });
        } catch (Exception $e) {
            // Log the error.
            error_log($e->getMessage());
            // Return a 500 Internal Server Error response.
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

     * @OA\Property(property="description", type="string", format="string", example="erg ear ga&nbsp;"),
     *  * *  * @OA\Property(property="awarding_body_id", type="string", format="string", example="awarding_body_id"),



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
        /**
         * This is the request to update a course title.
         * It takes the course title id and the new course title name, color, description, and awarding body id as parameters.
         * It will update the course title in the database.
         * It will also update the subjects associated with this course title.
         * If the course title is not found in the database, it will return a 404 response.
         * If the user does not have permission to update the course title, it will return a 401 response.
         * If there is an error, it will return a 500 response.
         */
        try {
            // log the activity of the user
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {
                // check if the user has permission to update course titles
                if (!$request->user()->hasPermissionTo('course_title_update')) {
                    // if the user doesn't have the permission, return a 401 response
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // get the validated request data
                $request_data = $request->validated();
                // set the query params for the course title model
                $course_title_query_params = [
                    "id" => $request_data["id"],
                ];

                // find the course title by id and update it
                $course_title  =  tap(CourseTitle::where($course_title_query_params))->update(
                    collect($request_data)->only([
                        'name',
                        'level',
                        'description',
                        "awarding_body_id"
                        // "is_active",
                        // "business_id",

                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();
                // if the course title is not found, return a 404 response
                if (!$course_title) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }



                // return the course title
                return response($course_title, 201);
            });
        } catch (Exception $e) {
            // log the error
            error_log($e->getMessage());
            // return a 500 response
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

    /**
     * Toggle the active status of a course title
     *
     * This method is protected by the "course_title_activate" permission.
     * If the user does not have this permission, a 401 Unauthorized response will be returned.
     *
     * @param GetIdRequest $request The request containing the id of the course title to toggle
     *
     * @return \Illuminate\Http\Response A JSON response containing a success message
     */
    public function toggleActiveCourseTitle(GetIdRequest $request)
    {

        try {
            // log the activity of the user in the user_activity table
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // check if the user has the necessary permission to perform this action
            if (!$request->user()->hasPermissionTo('course_title_activate')) {
                // if the user does not have the permission, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // validate the request data
            $request_data = $request->validated();

            // call the toggleActivation method to either enable or disable the course title
            // the toggleActivation method takes the following parameters:
            // - the class name of the primary model
            // - the class name of the model representing the disabled state
            // - the name of the id attribute of the primary model
            // - the id of the record to toggle
            // - the currently authenticated user
            $this->toggleActivation(
                CourseTitle::class,        // the primary model class
                DisabledCourseTitle::class, // the model class representing the disabled state
                'course_title_id',         // the name of the id attribute of the primary model
                $request_data["id"],       // the id of the record to toggle
                auth()->user()             // the currently authenticated user
            );

            // return a success response with a JSON message
            return response()->json(['message' => 'course title status updated successfully'], 200);
        } catch (Exception $e) {
            // log any exceptions that occur
            error_log($e->getMessage());
            // return a 500 Internal Server Error response with a JSON error message
            return $this->sendError($e, 500, $request);
        }
    }

    public function query_filters_v2($query)
    {
        // Initialize the created_by variable
        $created_by = NULL;

        // Check if the authenticated user has a business
        if (auth()->user()->business) {
            // If the user has a business, set created_by to the creator of the business
            $created_by = auth()->user()->business->created_by;
        }

        // Apply a filter to the query based on the business_id of the authenticated user
        return $query->where([
            "business_id" => auth()->user()->business_id
        ])

            // If the user has a business_id, apply additional business-specific filtering
            ->when(!empty(auth()->user()->business_id), function ($query) use ($created_by) {
                // Use a custom scope 'forBusiness' to apply business-specific logic to the query
                $query->forBusiness('course_titles', "remove_letter_templates", $created_by);
            })

            // If a search key is provided in the request, filter the query based on the search key
            ->when(!empty(request()->search_key), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->search_key;
                    // Search for the term in the name and description columns of course titles
                    $query->where("course_titles.name", "like", "%" . $term . "%")
                        ->orWhere("course_titles.description", "like", "%" . $term . "%");
                });
            })

            // If an awarding_body_id is provided in the request, filter the query by it
            ->when(!empty(request()->awarding_body_id), function ($query) {
                return $query->where('course_titles.awarding_body_id', request()->awarding_body_id);
            })

            // If a start date is provided in the request, filter the query for records created after it
            ->when(!empty(request()->start_date), function ($query) {
                return $query->where('course_titles.created_at', ">=", request()->start_date);
            })

            // If an end date is provided in the request, filter the query for records created before it
            ->when(!empty(request()->end_date), function ($query) {
                return $query->where('course_titles.created_at', "<=", (request()->end_date . ' 23:59:59'));
            });
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

    /**
     * Get a list of course titles.
     *
     * This method returns a list of course titles, along with their awarding bodies and subjects.
     * It also logs the activity of the user in the user_activity table.
     * If the user does not have the right permission to view course titles, a 401 Unauthorized response will be returned.
     * If an exception is thrown during the execution of this method, a 500 Internal Server Error response will be returned.
     *
     * @param Request $request The request containing the parameters to filter the course titles.
     *
     * @return \Illuminate\Http\Response A JSON response containing the list of course titles.
     */
    public function getCourseTitles(Request $request)
    {
        try {
            // Log the activity of the user in the user_activity table.
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the right permission to view course titles.
            if (!$request->user()->hasPermissionTo('course_title_view')) {
                // If the user does not have the permission, return a 401 Unauthorized response.
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Start building the query to retrieve the course titles.
            $query = CourseTitle::with("awarding_body", "subjects");

            // Call the query_filters_v2 method to add the filters to the query.
            $query = $this->query_filters_v2($query);

            // Call the retrieveData method to execute the query and retrieve the data.
            $course_titles = $this->retrieveData($query, "id", "course_titles");

            // Return a JSON response containing the list of course titles.
            return response()->json($course_titles, 200);
        } catch (Exception $e) {
            // Log any exceptions that occur.
            error_log($e->getMessage());

            // Return a 500 Internal Server Error response with a JSON error message.
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v2.0/course-titles",
     *      operationId="getCourseTitlesV2",
     *      tags={"student.course_titles"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="id",
     *         required=true,
     *  example="6"
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

    public function getCourseTitlesV2(Request $request)
    {
        try {
            // Log the activity of the user in the user_activity table.
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the right permission to view course titles.
            if (!$request->user()->hasPermissionTo('course_title_view')) {
                // If the user does not have the permission, return a 401 Unauthorized response.
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Start building the query to retrieve the course titles.
            $query = CourseTitle::with(
                [
                    "awarding_body" => function ($query) {
                        // Select only the id and name columns from the awarding bodies table.
                        $query->select(
                            "awarding_bodies.id",
                            "awarding_bodies.name",
                        );
                    }

                ]
            );

            // Call the query_filters_v2 method to add the filters to the query.
            $query = $this->query_filters_v2($query);

            // Select the id, name, level, description, awarding body id, and is active columns from the course titles table.
            $query->select(
                "course_titles.id",
                'course_titles.name',
                'course_titles.level',
                'course_titles.description',
                "course_titles.awarding_body_id",
                "course_titles.is_active"
            );

            // Call the retrieveData method to execute the query and retrieve the data.
            $course_titles = $this->retrieveData($query, "id", "course_titles");

            // Return a JSON response containing the list of course titles.
            return response()->json($course_titles, 200);
        } catch (Exception $e) {
            // Log any exceptions that occur.
            error_log($e->getMessage());

            // Return a 500 Internal Server Error response with a JSON error message.
            return $this->sendError($e, 500, $request);
        }
    }


    public function query_filters($query)
    {
        // Retrieve the business ID from the request
        $business_id = request()->business_id;

        // Check if the business ID is provided
        if (!$business_id) {
            // If not, prepare an error message indicating that the business ID field is required
            $error = [
                "message" => "The given data was invalid.",
                "errors" => ["business_id" => ["The business id field is required."]]
            ];
            // Throw an exception with the error message and a 422 status code
            throw new Exception(json_encode($error), 422);
        }

        // Start building the query for course titles
        return $query->where('course_titles.business_id', $business_id) // Filter the query by business ID
            ->where('course_titles.is_active', 1) // Filter the query to include only active course titles
            ->when(!empty(request()->search_key), function ($query) {
                // If a search key is provided, further filter the query
                return $query->where(function ($query) {
                    $term = request()->search_key; // Retrieve the search key from the request
                    // Filter course titles by matching the search key with the name or description
                    $query->where("course_titles.name", "like", "%" . $term . "%")
                        ->orWhere("course_titles.description", "like", "%" . $term . "%");
                });
            })
            ->when(!empty(request()->start_date), function ($query) {
                // If a start date is provided, filter the query to include records created on or after the start date
                return $query->where('course_titles.created_at', ">=", request()->start_date);
            })
            ->when(!empty(request()->end_date), function ($query) {
                // If an end date is provided, filter the query to include records created on or before the end date
                return $query->where('course_titles.created_at', "<=", (request()->end_date . ' 23:59:59'));
            });
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

    /**
     * Get a list of course titles for the client (student).
     *
     * This endpoint is protected by the "student.course_titles" permission.
     * If the user does not have this permission, a 403 Forbidden response will be returned.
     *
     * @param Request $request The request containing the parameters to filter the course titles.
     *
     * @return \Illuminate\Http\Response A JSON response containing the list of course titles.
     */
    public function getCourseTitlesClient(Request $request)
    {
        try {
            // store the activity of the user in the user_activity table
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // build the query to retrieve the course titles
            $query = CourseTitle::query();

            // add the filters to the query
            $query = $this->query_filters($query);

            // execute the query and retrieve the data
            $course_titles = $this->retrieveData($query, "id", "course_titles");

            // return a JSON response containing the list of course titles
            return response()->json($course_titles, 200);
        } catch (Exception $e) {
            // log any exceptions that occur
            error_log($e->getMessage());

            // return a 500 Internal Server Error response with a JSON error message
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v2.0/client/course-titles",
     *      operationId="getCourseTitlesClientV2",
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

    /**
     * This method is to get course titles
     * It takes the request with the search query parameters
     * It logs the activity of the user in the user_activity table
     * It builds the query to retrieve the course titles
     * It adds the filters to the query
     * It executes the query and retrieves the data
     * It returns a JSON response containing the list of course titles
     *
     * @param Request $request The request containing the search query parameters
     * @return \Illuminate\Http\Response A JSON response containing the list of course titles
     */
    public function getCourseTitlesClientV2(Request $request)
    {
        try {
            // log the activity of the user in the user_activity table
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            // build the query to retrieve the course titles
            $query = CourseTitle::query();

            // add the filters to the query
            $query = $this->query_filters($query);

            // select the columns to be retrieved
            $query = $query
                ->select(
                    'course_titles.id',
                    'course_titles.name'
                );

            // execute the query and retrieve the data
            $course_titles = $this->retrieveData($query, "id", "course_titles");

            // return a JSON response containing the list of course titles
            return response()->json($course_titles, 200);
        } catch (Exception $e) {

            // log any exceptions that occur
            error_log($e->getMessage());

            // return a 500 Internal Server Error response with a JSON error message
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
            // Log the activity of the user in the user_activity table.
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the right permission to view course titles.
            if (!$request->user()->hasPermissionTo('course_title_view')) {
                // If the user does not have the permission, return a 401 Unauthorized response.
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Start building the query to retrieve the course title.
            $query = CourseTitle::with(
                [
                    "subjects",
                    "awarding_body" => function ($query) {
                        // Select only the id and name columns from the awarding bodies table.
                        $query->select("awarding_bodies.id", "awarding_bodies.name");
                    }

                ]

            )->where([
                "id" => $id,
            ]);

            // Execute the query and retrieve the course title.
            $course_title = $query->first();

            // If the course title is not found, return a 404 response.
            if (!$course_title) {
                // Log the error in the error log.
                $this->storeError(
                    "no data found",
                    404,
                    "front end error",
                    "front end error"
                );
                // Return a 404 response with a message.
                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            // If the user does not have a business id, check for role restrictions.
            if (empty(auth()->user()->business_id)) {

                // If the user is a super admin, check if the course title belongs to a business.
                if (auth()->user()->hasRole('superadmin')) {
                    // If the course title belongs to a business, check if the user has the permission to update the course title.
                    if (($course_title->business_id != NULL || $course_title->is_default != 1)) {
                        // If the user does not have the permission, return a 403 Forbidden response.
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
                    // If the course title belongs to a business, return a 403 Forbidden response.
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
                    }

                    // If the course title does not belong to a business and the user is not the creator of the course title, return a 403 Forbidden response.
                    if ($course_title->is_default == 0 && $course_title->created_by != auth()->user()->id) {
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
                // If the user has a business id, check if the course title belongs to the user's business.
                if ($course_title->business_id != NULL) {
                    if (($course_title->business_id != auth()->user()->business_id)) {
                        // If the course title does not belong to the user's business, return a 403 Forbidden response.
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
                    // If the course title does not belong to a business and the user is not the creator of the course title, return a 403 Forbidden response.
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

            // Return the course title in a 200 response.
            return response()->json($course_title, 200);
        } catch (Exception $e) {

            // If there is an exception, log the error in the error log and return a 500 Internal Server Error response.
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
            // Log the activity of the user in the user_activity table with a dummy activity and description.
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has permission to delete course titles.
            if (!$request->user()->hasPermissionTo('course_title_delete')) {
                // If the user does not have permission, return a 401 Unauthorized response.
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Split the comma-separated string of ids into an array.
            $idsArray = explode(',', $ids);

            // Retrieve the existing course title IDs from the database that match the provided IDs.
            $existingIds = CourseTitle::whereIn('id', $idsArray)
                ->when(empty($request->user()->business_id), function ($query) use ($request) {
                    // If the user does not have a business ID.
                    if ($request->user()->hasRole("superadmin")) {
                        // If the user is a superadmin, retrieve course titles that are not associated with any business and are default.
                        return $query->where('course_titles.business_id', NULL)
                            ->where('course_titles.is_default', 1);
                    } else {
                        // If the user is not a superadmin, retrieve course titles that are not associated with any business, are not default, and were created by the user.
                        return $query->where('course_titles.business_id', NULL)
                            ->where('course_titles.is_default', 0)
                            ->where('course_titles.created_by', $request->user()->id);
                    }
                })
                ->when(!empty($request->user()->business_id), function ($query) use ($request) {
                    // If the user has a business ID, retrieve course titles that are associated with the user's business and are not default.
                    return $query->where('course_titles.business_id', $request->user()->business_id)
                        ->where('course_titles.is_default', 0);
                })
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();

            // Determine which provided IDs do not exist in the database.
            $nonExistingIds = array_diff($idsArray, $existingIds);

            // If there are any non-existing IDs, log an error and return a 404 response.
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

            // Check if there are any users associated with the existing course titles.

            $student_exists = Student::
            whereIn("course_title_id", $existingIds)
            ->exists();

            if ($student_exists) {
                // If there are, retrieve the conflicting users' details.
                $conflictingStudents = Student::
                whereIn("course_title_id", $existingIds)->get([
                    'id',
                    "title",
                    'first_name',
                    'middle_name',
                    'last_name',
                ]);
                // Log an error and return a 409 Conflict response with the conflicting users' details.
                $this->storeError(
                    "Some users are associated with the specified course titles",
                    409,
                    "front end error",
                    "front end error"
                );
                
                return response()->json([
                    "message" => "Some students are associated with the specified course titles",
                    "conflicting_users" => $conflictingStudents
                ], 409);

            }

            // Delete the existing course titles from the database.
            CourseTitle::destroy($existingIds);

            // Return a 200 OK response with a success message and the list of deleted IDs.
            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);




        } catch (Exception $e) {
            // If an exception occurs, log the error and return a 500 Internal Server Error response.
            return $this->sendError($e, 500, $request);
        }
    }
}
