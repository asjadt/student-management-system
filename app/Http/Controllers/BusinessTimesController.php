<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessTimesUpdateRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\BusinessTime;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessTimesController extends Controller
{
    use ErrorUtil, BusinessUtil, UserActivityUtil;
    /**
     *
     * @OA\Patch(
     *      path="/v1.0/business-times",
     *      operationId="updateBusinessTimes",
     *      tags={"business_times_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update business times",
     *      description="This method is to update business times",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"business_id","times"},
     *    @OA\Property(property="times", type="string", format="array",example={
     *{"day":0,"start_at":"10:10:00","end_at":"10:15:00","is_weekend":true},
     *{"day":1,"start_at":"10:10:00","end_at":"10:15:00","is_weekend":true},
     *{"day":2,"start_at":"10:10:00","end_at":"10:15:00","is_weekend":true},
     *{"day":3,"start_at":"10:10:00","end_at":"10:15:00","is_weekend":true},
     *{"day":4,"start_at":"10:10:00","end_at":"10:15:00","is_weekend":true},
     *{"day":5,"start_at":"10:10:00","end_at":"10:15:00","is_weekend":true},
     *{"day":6,"start_at":"10:10:00","end_at":"10:15:00","is_weekend":true}
     *
     * }),

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

    public function updateBusinessTimes(BusinessTimesUpdateRequest $request)
    {
        try {
            // Record the activity of the user for logging or audit purposes
            $this->storeActivity($request, "");

            // Use a database transaction to ensure atomicity of the operation
            return DB::transaction(function () use ($request) {
                // Check if the user has permission to update business times
                if (!$request->user()->hasPermissionTo('business_times_update')) {
                    // If not, return a 401 Unauthorized response
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Retrieve and validate the request data
                $request_data = $request->validated();

                // Ensure each day in the times array is unique
                $timesArray = collect($request_data["times"])->unique("day");

                // Delete any existing business times for the current user
                BusinessTime::where([
                    "business_id" => auth()->user()->business_id
                ])->delete();

                // Iterate over the unique times array to create new business time entries
                foreach ($timesArray as $business_time) {
                    BusinessTime::create([
                        "business_id" => auth()->user()->business_id,
                        "day" => $business_time["day"],
                        "start_at" => $business_time["start_at"],
                        "end_at" => $business_time["end_at"],
                        "is_weekend" => $business_time["is_weekend"],
                    ]);
                }

                // Return a success message upon successful insertion
                return response(["message" => "data inserted"], 201);
            });
        } catch (Exception $e) {
            // Log the error message for troubleshooting
            error_log($e->getMessage());
            // Return an error response in case of an exception
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/business-times",
     *      operationId="getBusinessTimes",
     *      tags={"business_times_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },


     *      summary="This method is to get business times ",
     *      description="This method is to get business times",
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

    public function getBusinessTimes(Request $request)
    {
        try {
            // Store a dummy activity for user tracking and analytics
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the 'business_times_view' permission
            if (!$request->user()->hasPermissionTo('business_times_view')) {
                // If the user does not have permission, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Retrieve all business times for the current business from the database
            // and order them in descending order based on their 'id'
            $business_times = BusinessTime::where([
                "business_id" => auth()->user()->business_id
            ])->orderByDesc("id")->get();

            // Return a JSON response with the retrieved data and a 200 OK status
            return response()->json($business_times, 200);
        } catch (Exception $e) {
            // If an exception occurs, handle the error by logging and returning a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }
}
