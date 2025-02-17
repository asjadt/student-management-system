<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessSettingCreateRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\BusinessSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessSettingController extends Controller
{
    use ErrorUtil, UserActivityUtil, BusinessUtil;
    /**
     *
     * @OA\Post(
     *      path="/v1.0/business-settings",
     *      operationId="createBusinessSetting",
     *      tags={"business_setting"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store business setting",
     *      description="This method is to store business setting. all student obj fields 'title', 'first_name', 'middle_name', 'last_name', 'nationality', 'course_fee', 'fee_paid', 'passport_number', 'student_id', 'date_of_birth', 'course_start_date', 'course_end_date', 'level', 'letter_issue_date', 'student_status_id', 'course_title_id', 'attachments', 'course_duration', 'course_detail', 'email', 'contact_number', 'sex', 'address', 'country', 'city', 'postcode', 'lat', 'long', 'emergency_contact_details', 'previous_education_history', 'passport_issue_date', 'passport_expiry_date', 'place_of_issue' ............................................................................................ optional fields: 'middle_name', 'passport_number', 'student_id', 'course_end_date', 'level', 'letter_issue_date', 'course_duration', 'course_detail', 'email', 'contact_number', 'sex', 'address', 'country', 'city', 'postcode', 'lat', 'long', 'emergency_contact_details', 'previous_education_history', 'passport_issue_date', 'passport_expiry_date', 'place_of_issue' ",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *     @OA\Property(
     *         property="online_student_status_id",
     *         type="integer",
     *         description="The ID of the online student status.",
     *         example=1
     *     ),
     *
     *     @OA\Property(
     *         property="student_verification_fields",
     *         type="integer",
     *         description="The ID of the student verification field.",
     *         example=1
     *     ),
     *
     *
     *
     *
     *       @OA\Property(
     *         property="student_data_fields",
     *         type="array",
     *         description="The ID of the online student status.",
     *         @OA\Items(
     *             type="object",
     *             required={"title"},
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 description="The name of the online verification query field. 'title', 'first_name', 'middle_name', 'last_name', 'nationality', 'course_fee', 'fee_paid', 'passport_number', 'student_id', 'date_of_birth', 'course_start_date', 'course_end_date', 'level', 'letter_issue_date', 'student_status_id', 'course_title_id', 'attachments', 'course_duration', 'course_detail', 'email', 'contact_number', 'sex', 'address', 'country', 'city', 'postcode', 'lat', 'long', 'emergency_contact_details', 'previous_education_history', 'passport_issue_date', 'passport_expiry_date', 'place_of_issue'",
     *                 example="verification_code"
     *             )
     *         )
     *     )
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

    public function createBusinessSetting(BusinessSettingCreateRequest $request)
    {
        /**
         * This endpoint is for creating a new business setting.
         * It will validate the request data using the BusinessSettingCreateRequest class.
         * If the request is valid, it will store a new business setting in the database.
         * If the request is not valid, it will return a 422 Unprocessable Entity response with an error message.
         *
         * @param BusinessSettingCreateRequest $request The request object containing the data to be validated.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            // Log the user activity with a dummy activity and description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start a database transaction
            return DB::transaction(function () use ($request) {
                /**
                 * Check if the user has the business_admin role.
                 * If the user does not have the business_admin role, return a 401 Unauthorized response.
                 */
                if (!$request->user()->hasRole('business_admin')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                /**
                 * Validate the request data using the BusinessSettingCreateRequest class.
                 * If the request data is invalid, return a 422 Unprocessable Entity response with an error message.
                 */
                $request_data = $request->validated();
                $request_data["business_id"] = auth()->user()->business_id;


                /**
                 * Find a business setting with the business ID.
                 * If no business setting is found, create a new one.
                 * If a business setting is found, update the existing one.
                 */
                $business_setting =     BusinessSetting::updateOrCreate($request_data, $request_data);
                $business_setting = BusinessSetting::where([
                    "business_id" => $request_data["business_id"]
                ])
                    ->first();

                if ($business_setting) {
                    // Update existing record
                    $business_setting->update($request_data);



                    // Fill the model with data except for the ID
                    $business_setting->fill(collect($request_data)->only([

                        'online_student_status_id',

                    ])->toArray());
                    $business_setting->save();
                } else {
                    // Create new record
                    $business_setting = BusinessSetting::create($request_data);
                }


                // Return the newly created business setting
                return response($business_setting, 201);
            });
        } catch (Exception $e) {
            // If an exception occurs, log the error and return a 500 Internal Server Error response
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/business-settings",
     *      operationId="getBusinessSetting",
     *      tags={"business_setting"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *              @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="per_page",
     *         required=false,
     *  example=""
     *      ),

     *      * *  @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=false,
     * example=""
     * ),
     * *  @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=false,
     * example=""
     * ),
     * *  @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=false,
     * example=""
     * ),
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=false,
     * example=""
     * ),

     *      summary="This method is to get business setting",
     *      description="This method is to get business setting",
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
     * Get business settings
     *
     * This method is to get business settings
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBusinessSetting(Request $request)
    {
        try {
            // Store activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check permission
            if (!$request->user()->hasRole('business_admin')) {
                // Return 401 Unauthorized if the user does not have the permission
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Construct the query
            $business_setting = BusinessSetting::where('business_settings.business_id', auth()->user()->business_id)

                // Search by name
                ->when(!empty($request->search_key), function ($query) use ($request) {
                    // Search by name
                    return $query->where(function ($query) use ($request) {
                        // Use the search key
                        $term = $request->search_key;
                        // Search by name
                        $query->where("business_settings.name", "like", "%" . $term . "%");
                    });
                })

                // Search by date
                ->when(!empty($request->start_date), function ($query) use ($request) {
                    // Search by start date
                    return $query->where('business_settings.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    // Search by end date
                    return $query->where('business_settings.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })

                // Order by
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    // Order by
                    return $query->orderBy("business_settings.id", $request->order_by);
                }, function ($query) {
                    // Default order by
                    return $query->orderBy("business_settings.id", "DESC");
                })

                // Paginate
                ->when(!empty($request->per_page), function ($query) use ($request) {
                    // Paginate
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    // Get all records
                    return $query->get();
                });

            // Return the result as a JSON response
            return response()->json($business_setting, 200);
        } catch (Exception $e) {
            // Handle the error
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/client/v1.0/business-settings",
     *      operationId="getBusinessSettingClient",
     *      tags={"business_setting"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *              @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="per_page",
     *         required=false,
     *  example=""
     *      ),

     *      * *  @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=false,
     * example=""
     * ),
     * *  @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=false,
     * example=""
     * ),
     * *  @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=false,
     * example=""
     * ),
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=false,
     * example=""
     * ),

     *      summary="This method is to get business setting",
     *      description="This method is to get business setting",
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

    public function getBusinessSettingClient(Request $request)
    {
        try {
            // Log the user activity with a dummy activity and description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the 'business_id' is provided in the request
            if (empty(request()->filled("business_id"))) {
                // Throw an exception if 'business_id' is not present
                throw new Exception("Business Id is required", 400);
            }

            // Construct a query to retrieve business settings based on the provided 'business_id'
            $business_setting = BusinessSetting::where('business_settings.business_id', request()->input("business_id"))

                // If a search key is provided, filter the query results by the 'name' field
                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                        $query->where("business_settings.name", "like", "%" . $term . "%");
                    });
                })

                // Filter the query results by start date if 'start_date' is provided
                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('business_settings.created_at', ">=", $request->start_date);
                })

                // Filter the query results by end date if 'end_date' is provided
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('business_settings.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })

                // Order the query results by 'id' based on 'order_by' parameter if provided, default to descending order
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("business_settings.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("business_settings.id", "DESC");
                })

                // Paginate results if 'per_page' parameter is provided, otherwise retrieve all results
                ->when(!empty($request->per_page), function ($query) use ($request) {
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    return $query->get();
                });

            // Return the query results as a JSON response with a 200 OK status
            return response()->json($business_setting, 200);
        } catch (Exception $e) {
            // Handle any exceptions by logging the error and returning a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }
}
