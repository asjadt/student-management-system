<?php







namespace App\Http\Controllers;

use App\Http\Requests\AwardingBodyCreateRequest;
use App\Http\Requests\AwardingBodyUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\AwardingBody;
use App\Models\DisabledAwardingBody;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AwardingBodyController extends Controller
{

    use ErrorUtil, UserActivityUtil, BusinessUtil, BasicUtil;


    /**
     *
     * @OA\Post(
     *      path="/v1.0/awarding-bodies",
     *      operationId="createAwardingBody",
     *      tags={"awarding_bodies"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store awarding bodies",
     *      description="This method is to store awarding bodies",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     * @OA\Property(property="name", type="string", format="string", example="name"),
     * @OA\Property(property="description", type="string", format="string", example="description"),
     * @OA\Property(property="accreditation_start_date", type="string", format="string", example="accreditation_start_date"),
     * @OA\Property(property="accreditation_expiry_date", type="string", format="string", example="accreditation_expiry_date"),
     * @OA\Property(property="logo", type="string", format="string", example="logo"),
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

    public function createAwardingBody(AwardingBodyCreateRequest $request)
    {
        try {
            // Log the user's activity for creating an awarding body
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start a database transaction to ensure data consistency
            return DB::transaction(function () use ($request) {
                // Check if the authenticated user has permission to create an awarding body
                if (!auth()->user()->hasPermissionTo('awarding_body_create')) {
                    // If not, return a 401 Unauthorized response
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Validate the request data
                $request_data = $request->validated();

                // Set the default status for the new awarding body as active
                $request_data["is_active"] = 1;

                // Set the default flag to indicate this is not a default awarding body
                $request_data["is_default"] = 0;

                // Set the user who created this awarding body
                $request_data["created_by"] = auth()->user()->id;

                // Set the business ID from the authenticated user's business ID
                $request_data["business_id"] = auth()->user()->business_id;

                // If the user does not belong to a business
                if (empty(auth()->user()->business_id)) {
                    // Set business ID to NULL
                    $request_data["business_id"] = NULL;
                    // If the user has a 'superadmin' role, mark this awarding body as default
                    if (auth()->user()->hasRole('superadmin')) {
                        $request_data["is_default"] = 1;
                    }
                }

                // Create a new awarding body with the validated data
                $awarding_body = AwardingBody::create($request_data);

                // Return the newly created awarding body with a 201 Created response
                return response($awarding_body, 201);
            });
        } catch (Exception $e) {
            // If an exception occurs, log the error and return a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     *      path="/v1.0/awarding-bodies",
     *      operationId="updateAwardingBody",
     *      tags={"awarding_bodies"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update awarding bodies ",
     *      description="This method is to update awarding bodies ",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *      @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="name", type="string", format="string", example="name"),
     * @OA\Property(property="description", type="string", format="string", example="description"),
     * @OA\Property(property="accreditation_start_date", type="string", format="string", example="accreditation_start_date"),
     * @OA\Property(property="accreditation_expiry_date", type="string", format="string", example="accreditation_expiry_date"),
     * @OA\Property(property="logo", type="string", format="string", example="logo"),
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

    public function updateAwardingBody(AwardingBodyUpdateRequest $request)
    {
        try {
            // store the user's activity for updating an awarding body
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            return DB::transaction(function () use ($request) {
                // check if the authenticated user has permission to update an awarding body
                if (!auth()->user()->hasPermissionTo('awarding_body_update')) {
                    // if not, return a 401 Unauthorized response
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // validate the request data
                $request_data = $request->validated();


                // construct the query parameters to retrieve the awarding body
                $awarding_body_query_params = [
                    "id" => $request_data["id"],
                ];

                // retrieve the awarding body
                $awarding_body = AwardingBody::where($awarding_body_query_params)->first();

                // if the awarding body exists
                if ($awarding_body) {
                    // fill the awarding body with the validated data
                    $awarding_body->fill(collect($request_data)->only([

                        "name",
                        "description",
                        "accreditation_start_date",
                        "accreditation_expiry_date",
                        "logo",
                        // "is_default",
                        // "is_active",
                        // "business_id",
                        // "created_by"
                    ])->toArray());
                    // save the awarding body
                    $awarding_body->save();
                } else {
                    // if the awarding body does not exist, return a 500 Internal Server Error response
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }

                // return the updated awarding body with a 201 Created response
                return response($awarding_body, 201);
            });
        } catch (Exception $e) {
            // if an exception occurs, log the error and return a 500 Internal Server Error response
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="/v1.0/awarding-bodies/toggle-active",
     *      operationId="toggleActiveAwardingBody",
     *      tags={"awarding_bodies"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to toggle awarding bodies",
     *      description="This method is to toggle awarding bodies",
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
     * Toggle active awarding bodies.
     *
     * This function is responsible for toggling the active status of an awarding body.
     *
     * @param GetIdRequest $request
     * @return JsonResponse
     */
    public function toggleActiveAwardingBody(GetIdRequest $request)
    {

        try {
            // log the user's activity for toggling awarding body
            $this->storeActivity($request, "toggle active awarding body", "toggle active awarding body");

            // check if the authenticated user has permission to toggle awarding body
            if (!$request->user()->hasPermissionTo('awarding_body_activate')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // get the validated request data
            $request_data = $request->validated();

            // get the awarding body by the given id
            $awarding_body =  AwardingBody::where([
                "id" => $request_data["id"],
            ])
                ->first();

            // if no awarding body is found, return a 404 response
            if (!$awarding_body) {

                return response()->json([
                    "message" => "no data found"
                ], 404);
            }


            // toggle the active status of the awarding body
            $this->toggleActivation(
                AwardingBody::class,
                DisabledAwardingBody::class,
                'awarding_body',
                $request_data["id"],
                auth()->user()
            );


            // return a success response
            return response()->json(['message' => 'awarding body status updated successfully'], 200);
        } catch (Exception $e) {
            // if an exception occurs, log the error and return a 500 Internal Server Error response
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function query_filters($query)
    {
        // If the user is not a super admin, we need to filter the results by the business they belong to
        // or by the businesses that the user is allowed to see.
        $created_by  = NULL;
        if (auth()->user()->business) {
            $created_by = auth()->user()->business->created_by;
        }
        return   $query->when(empty(auth()->user()->business_id), function ($query) use ($created_by) {
            // if the user is not assigned to a business, we filter the results
            // by the businesses they are allowed to see, or by the businesses
            // that the user is allowed to see, and that are created by the user
            $query->when(auth()->user()->hasRole('superadmin'), function ($query) {
                // if the user is a super admin, we return all the awarding bodies
                $query->forSuperAdmin('awarding_bodies');
            }, function ($query) use ($created_by) {
                // if the user is not a super admin, we filter the results by the businesses
                // that the user is allowed to see, and that are created by the user
                $query->forNonSuperAdmin('awarding_bodies', 'remove_awarding_bodies', $created_by);
            });
        })
            // if the user is assigned to a business, we filter the results by the business
            ->when(!empty(auth()->user()->business_id), function ($query) use ($created_by) {
                $query->forBusiness('awarding_bodies', "remove_awarding_bodies", $created_by);
            })
            // filter the results by the name of the awarding body
            ->when(!empty(request()->name), function ($query) {
                return $query->where('awarding_bodies.name', request()->name);
            })
            // filter the results by the start date of the accreditation
            ->when(!empty(request()->start_accreditation_start_date), function ($query) {
                return $query->where('awarding_bodies.accreditation_start_date', ">=", request()->start_accreditation_start_date);
            })
            // filter the results by the end date of the accreditation
            ->when(!empty(request()->end_accreditation_start_date), function ($query) {
                return $query->where('awarding_bodies.accreditation_start_date', "<=", (request()->end_accreditation_start_date . ' 23:59:59'));
            })
            // filter the results by the start date of the accreditation
            ->when(!empty(request()->start_accreditation_expiry_date), function ($query) {
                return $query->where('awarding_bodies.accreditation_expiry_date', ">=", request()->start_accreditation_expiry_date);
            })
            // filter the results by the end date of the accreditation
            ->when(!empty(request()->end_accreditation_expiry_date), function ($query) {
                return $query->where('awarding_bodies.accreditation_expiry_date', "<=", (request()->end_accreditation_expiry_date . ' 23:59:59'));
            })
            // search the awarding body by name, description and logo
            ->when(!empty(request()->search_key), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->search_key;
                    $query
                        ->orWhere("awarding_bodies.name", "like", "%" . $term . "%")
                        ->where("awarding_bodies.description", "like", "%" . $term . "%")
                        ->orWhere("awarding_bodies.logo", "like", "%" . $term . "%");
                });
            })
            // filter the results by the start date of the awarding body
            ->when(!empty(request()->start_date), function ($query) {
                return $query->where('awarding_bodies.created_at', ">=", request()->start_date);
            })
            // filter the results by the end date of the awarding body
            ->when(!empty(request()->end_date), function ($query) {
                return $query->where('awarding_bodies.created_at', "<=", (request()->end_date . ' 23:59:59'));
            });
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/awarding-bodies",
     *      operationId="getAwardingBodies",
     *      tags={"awarding_bodies"},
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
     *         name="start_accreditation_start_date",
     *         in="query",
     *         description="start_accreditation_start_date",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="end_accreditation_start_date",
     *         in="query",
     *         description="end_accreditation_start_date",
     *         required=true,
     *  example="6"
     *      ),



     *         @OA\Parameter(
     *         name="start_accreditation_expiry_date",
     *         in="query",
     *         description="start_accreditation_expiry_date",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="end_accreditation_expiry_date",
     *         in="query",
     *         description="end_accreditation_expiry_date",
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





     *      summary="This method is to get awarding bodies  ",
     *      description="This method is to get awarding bodies ",
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
     * Get a list of awarding bodies that match the given filters.
     *
     * The filters are as follows:
     * - name: the name of the awarding body
     * - start_accreditation_start_date: the start date of the accreditation period
     * - end_accreditation_start_date: the end date of the accreditation period
     * - start_accreditation_expiry_date: the start date of the accreditation expiry period
     * - end_accreditation_expiry_date: the end date of the accreditation expiry period
     * - per_page: the number of awarding bodies to return per page
     * - is_active: whether the awarding body is active or not
     * - start_date: the start date of the awarding body
     * - end_date: the end date of the awarding body
     * - search_key: the search key to filter the awarding bodies by
     * - order_by: the field to order the awarding bodies by
     * - id: the ID of the awarding body
     *
     * This method also logs the user's activity in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getAwardingBodies(Request $request)
    {
        try {
            // Log the user's activity in the database
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the necessary permissions to view the awarding bodies
            if (!$request->user()->hasPermissionTo('awarding_body_view')) {
                // If not, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Build the query to retrieve the awarding bodies
            $query = AwardingBody::with("courses.subjects");
            $query = $this->query_filters($query);

            // Retrieve the awarding bodies from the database
            $awarding_bodies = $this->retrieveData($query, "id", "awarding_bodies");

            // Return the awarding bodies as JSON
            return response()->json($awarding_bodies, 200);
        } catch (Exception $e) {
            // If an exception occurs, log the error and return a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/v2.0/awarding-bodies",
     *      operationId="getAwardingBodiesV2",
     *      tags={"awarding_bodies"},
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
     *         name="start_accreditation_start_date",
     *         in="query",
     *         description="start_accreditation_start_date",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="end_accreditation_start_date",
     *         in="query",
     *         description="end_accreditation_start_date",
     *         required=true,
     *  example="6"
     *      ),



     *         @OA\Parameter(
     *         name="start_accreditation_expiry_date",
     *         in="query",
     *         description="start_accreditation_expiry_date",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="end_accreditation_expiry_date",
     *         in="query",
     *         description="end_accreditation_expiry_date",
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





     *      summary="This method is to get awarding bodies  ",
     *      description="This method is to get awarding bodies ",
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

    public function getAwardingBodiesV2(Request $request)
    {
        try {
            // Log the user's activity with a dummy activity and description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the 'awarding_body_view' permission
            if (!$request->user()->hasPermissionTo('awarding_body_view')) {
                // If the user does not have permission, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Initialize the query for AwardingBody model
            $query = AwardingBody::query();

            // Apply filters to the query using the query_filters method
            $query = $this->query_filters($query)
                ->select(
                    // Select the specified columns from the awarding_bodies table
                    'awarding_bodies.id',
                    'awarding_bodies.name',
                    'awarding_bodies.description',
                    'awarding_bodies.accreditation_start_date',
                    'awarding_bodies.accreditation_expiry_date',
                    'awarding_bodies.logo',
                    'awarding_bodies.is_active',
                );

            // Execute the query and retrieve the awarding bodies data
            $awarding_bodies = $this->retrieveData($query, "id", "awarding_bodies");

            // Return the retrieved data as a JSON response with a 200 OK status
            return response()->json($awarding_bodies, 200);
        } catch (Exception $e) {
            // If an exception occurs, handle the error and return a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/awarding-bodies/{ids}",
     *      operationId="deleteAwardingBodiesByIds",
     *      tags={"awarding_bodies"},
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
     *      summary="This method is to delete awarding body by id",
     *      description="This method is to delete awarding body by id",
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

    public function deleteAwardingBodiesByIds(Request $request, $ids)
    {
        // Log the user's activity in the database
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the necessary permissions to delete awarding bodies
            if (!$request->user()->hasPermissionTo('awarding_body_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Split the given IDs by comma and convert to an array
            $idsArray = explode(',', $ids);

            // Retrieve the existing IDs in the database
            $existingIds = AwardingBody::whereIn('id', $idsArray)
                // If the user is a superadmin, retrieve the IDs that have a NULL business ID
                // and are default awarding bodies
                ->when(empty(auth()->user()->business_id), function ($query) use ($request) {
                    if ($request->user()->hasRole("superadmin")) {
                        return $query->where('awarding_bodies.business_id', NULL)
                            ->where('awarding_bodies.is_default', 1);
                    } else {
                        return $query->where('awarding_bodies.business_id', NULL)
                            ->where('awarding_bodies.is_default', 0)
                            ->where('awarding_bodies.created_by', $request->user()->id);
                    }
                })
                // If the user is not a superadmin, retrieve the IDs that have the same business ID
                // and are not default awarding bodies
                ->when(!empty(auth()->user()->business_id), function ($query) use ($request) {
                    return $query->where('awarding_bodies.business_id', auth()->user()->business_id)
                        ->where('awarding_bodies.is_default', 0);
                })

                // Select only the 'id' column
                ->select('id')
                // Retrieve the data from the database
                ->get()
                // Convert the data to an array of IDs
                ->pluck('id')
                ->toArray();

            // Calculate the IDs that do not exist in the database
            $nonExistingIds = array_diff($idsArray, $existingIds);

            // If there are any non-existing IDs, return a 404 error
            if (!empty($nonExistingIds)) {

                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }

            // Delete the existing awarding bodies
            AwardingBody::destroy($existingIds);

            // Return a success response with the deleted IDs
            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {
            // If an exception occurs, handle the error and return a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }
}
