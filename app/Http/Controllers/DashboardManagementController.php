<?php

namespace App\Http\Controllers;

use App\Http\Requests\WidgetCreateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\AwardingBody;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\CourseTitle;
use App\Models\Student;
use App\Models\DashboardWidget;
use App\Models\Department;
use App\Models\EmployeePassportDetail;
use App\Models\EmployeeSponsorship;
use App\Models\EmployeeVisaDetail;
use App\Models\JobListing;
use App\Models\LeaveRecord;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardManagementController extends Controller
{
    use ErrorUtil, BusinessUtil, UserActivityUtil;

    /**
     *
     * @OA\Get(
     *      path="/v1.0/business-owner-dashboard/jobs-in-area/{business_id}",
     *      operationId="getBusinessOwnerDashboardDataJobList",
     *      tags={"dashboard_management.business_admin"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="business_id",
     *         in="path",
     *         description="business_id",
     *         required=true,
     *  example="1"
     *      ),
     *      *      * *  @OA\Parameter(
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
     *      summary="This should return list of jobs posted by drivers within same city and which are still not finalised and this business owner have not applied yet.",
     *      description="This should return list of jobs posted by drivers within same city and which are still not finalised and this business owner have not applied yet.",
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
     * Get list of jobs posted by drivers within same city and which are still not finalised and this business owner have not applied yet.
     * @param int $business_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBusinessOwnerDashboardDataJobList($business_id, Request $request)
    {
        try {
            // Store user activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Get business by id and owner id
            $business = Business::where([
                "id" => $business_id,
                "owner_id" => $request->user()->id
            ])
                ->first();

            // If business does not exist, return error
            if (!$business) {
                return response()->json([
                    "message" => "you are not the owner of the business or the request business does not exits"
                ], 404);
            }

            // Get pre bookings which are not applied by this business owner
            $prebookingQuery = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
                ->leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
                ->where([
                    "users.city" => $business->city
                ])
                ->whereNotIn('job_bids.business_id', [$business->id])
                ->where('pre_bookings.status', "pending");

            // Filter by start date
            if (!empty($request->start_date)) {
                $prebookingQuery = $prebookingQuery->where('pre_bookings.created_at', ">=", $request->start_date);
            }

            // Filter by end date
            if (!empty($request->end_date)) {
                $prebookingQuery = $prebookingQuery->where('pre_bookings.created_at', "<=", ($request->end_date . ' 23:59:59'));
            }

            // Get pre bookings, count of job bids and count of job bids of this business
            $data = $prebookingQuery->groupBy("pre_bookings.id")
                ->select(
                    "pre_bookings.*",
                    DB::raw('(SELECT COUNT(job_bids.id) FROM job_bids WHERE job_bids.pre_booking_id = pre_bookings.id) AS job_bids_count'),

                    DB::raw('(SELECT COUNT(job_bids.id) FROM job_bids
        WHERE
        job_bids.pre_booking_id = pre_bookings.id
        AND
        job_bids.business_id = ' . $business->id . '

        ) AS business_applied')

                )
                // Only get pre bookings which have less than 4 job bids
                ->havingRaw('(SELECT COUNT(job_bids.id) FROM job_bids WHERE job_bids.pre_booking_id = pre_bookings.id)  < 4')

                ->get();

            // Return data
            return response()->json($data, 200);
        } catch (Exception $e) {
            // Return error if any
            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     * @OA\Get(
     *      path="/v1.0/business-owner-dashboard/jobs-application/{business_id}",
     *      operationId="getBusinessOwnerDashboardDataJobApplications",
     *      tags={"dashboard_management.business_admin"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="business_id",
     *         in="path",
     *         description="business_id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="Total number of Jobs in the area and out of which total number of jobs this business owner have applied",
     *      description="Total number of Jobs in the area and out of which total number of jobs this business owner have applied",
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
     * Returns the total number of jobs in the area and out of which total number of jobs this business owner have applied
     * @param Request $request
     * @param int $business_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBusinessOwnerDashboardDataJobApplications($business_id, Request $request)
    {
        try {
            // Log the user activity with a dummy activity and description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Get the business by the business_id
            $business = Business::where([
                "id" => $business_id,
                "owner_id" => $request->user()->id
            ])
                ->first();

            // If the business does not exist, return a 404 Not Found response
            if (!$business) {
                return response()->json([
                    "message" => "You are not the owner of the business or the requested business does not exist."
                ], 404);
            }

            // Get the total number of jobs in the area
            $data["total_jobs"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
                ->where([
                    "users.city" => $business->city // Filter by city
                ])
                //  ->whereNotIn('job_bids.business_id', [$business->id]) // Filter out jobs that have already been applied
                ->where('pre_bookings.status', "pending") // Filter out jobs that have already been applied
                ->groupBy("pre_bookings.id") // Group by pre_bookings.id

                ->count(); // Count the number of jobs

            // Get the total number of weekly jobs in the area
            $data["weekly_jobs"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
                ->where([
                    "users.city" => $business->city // Filter by city
                ])
                //  ->whereNotIn('job_bids.business_id', [$business->id]) // Filter out jobs that have already been applied
                ->where('pre_bookings.status', "pending") // Filter out jobs that have already been applied
                ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]) // Filter by week
                ->groupBy("pre_bookings.id") // Group by pre_bookings.id

                ->count(); // Count the number of jobs

            // Get the total number of monthly jobs in the area
            $data["monthly_jobs"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
                ->where([
                    "users.city" => $business->city // Filter by city
                ])
                //  ->whereNotIn('job_bids.business_id', [$business->id]) // Filter out jobs that have already been applied
                ->where('pre_bookings.status', "pending") // Filter out jobs that have already been applied
                ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]) // Filter by month
                ->groupBy("pre_bookings.id") // Group by pre_bookings.id

                ->count(); // Count the number of jobs

            // Get the total number of jobs that this business owner have applied
            $data["applied_total_jobs"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
                ->leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
                ->where([
                    "users.city" => $business->city // Filter by city
                ])
                ->whereIn('job_bids.business_id', [$business->id]) // Filter by business_id
                ->where('pre_bookings.status', "pending") // Filter out jobs that have already been applied
                ->groupBy("pre_bookings.id") // Group by pre_bookings.id

                ->count(); // Count the number of jobs

            // Get the total number of weekly jobs that this business owner have applied
            $data["applied_weekly_jobs"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
                ->leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
                ->where([
                    "users.city" => $business->city // Filter by city
                ])
                ->whereIn('job_bids.business_id', [$business->id]) // Filter by business_id
                ->where('pre_bookings.status', "pending") // Filter out jobs that have already been applied
                ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]) // Filter by week
                ->groupBy("pre_bookings.id") // Group by pre_bookings.id

                ->count(); // Count the number of jobs

            // Get the total number of monthly jobs that this business owner have applied
            $data["applied_monthly_jobs"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
                ->leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
                ->where([
                    "users.city" => $business->city // Filter by city
                ])
                ->whereIn('job_bids.business_id', [$business->id]) // Filter by business_id
                ->where('pre_bookings.status', "pending") // Filter out jobs that have already been applied
                ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]) // Filter by month
                ->groupBy("pre_bookings.id") // Group by pre_bookings.id

                ->count(); // Count the number of jobs

            // Return the data as a JSON response with a 200 status
            return response()->json($data, 200);
        } catch (Exception $e) {
            // Return error if any
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/business-owner-dashboard/winned-jobs-application/{business_id}",
     *      operationId="getBusinessOwnerDashboardDataWinnedJobApplications",
     *      tags={"dashboard_management.business_admin"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="business_id",
     *         in="path",
     *         description="business_id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="Total Job Won( Total job User have selcted this business )",
     *      description="Total Job Won( Total job User have selcted this business )",
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
     * This function returns the number of jobs that the business owner has won.
     * A job is considered won if the business owner has been selected by the user.
     * The function will return a JSON response with the total number of jobs won, the number of jobs won this week and the number of jobs won this month.
     * If the business owner does not exist or the request business does not exist, the function will return a 404 error.
     * If there is an error with the request, the function will return a 500 error.
     * @param int $business_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function getBusinessOwnerDashboardDataWinnedJobApplications($business_id, Request $request)
    {
        try {
            // Store the user activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            // Get the business from the database
            $business = Business::where([
                "id" => $business_id,
                "owner_id" => $request->user()->id
            ])
                ->first();

            // If the business does not exist, return a 404 error
            if (!$business) {
                return response()->json([
                    "message" => "you are not the owner of the business or the request business does not exits"
                ], 404);
            }

            // Get the total number of jobs that this business owner has won
            $data["total"] = Student::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
                ->where([
                    "bookings.business_id" => $business->id
                ])

                ->where('pre_bookings.status', "booked")
                ->groupBy("pre_bookings.id")
                ->count();

            // Get the number of jobs that this business owner has won this week
            $data["weekly"] = Student::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
                ->where([
                    "bookings.business_id" => $business->id
                ])
                ->where('pre_bookings.status', "booked")
                ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->groupBy("pre_bookings.id")
                ->count();

            // Get the number of jobs that this business owner has won this month
            $data["monthly"] = Student::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
                ->where([
                    "bookings.business_id" => $business->id
                ])

                ->where('pre_bookings.status', "booked")
                ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->groupBy("pre_bookings.id")
                ->count();

            // Return the data as a JSON response with a 200 status
            return response()->json($data, 200);
        } catch (Exception $e) {
            // Return error if any
            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     * @OA\Get(
     *      path="/v1.0/business-owner-dashboard/completed-bookings/{business_id}",
     *      operationId="getBusinessOwnerDashboardDataCompletedBookings",
     *      tags={"dashboard_management.business_admin"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="business_id",
     *         in="path",
     *         description="business_id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="Total completed Bookings Total Bookings completed by this business owner",
     *      description="Total completed Bookings Total Bookings completed by this business owner",
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
     * This function returns the total number of bookings that the business owner has won in their lifetime, the number of bookings they have won this week and the number of bookings they have won this month.
     * The function will return a JSON response with the total number of bookings won, the number of bookings won this week and the number of bookings won this month.
     * If the business owner does not exist or the request business does not exist, the function will return a 404 error.
     * If there is an error with the request, the function will return a 500 error.
     * @param int $business_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBusinessOwnerDashboardDataCompletedBookings($business_id, Request $request)
    {
        try {
            // Store the user activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Get the business from the database
            $business = Business::where([
                "id" => $business_id,
                "owner_id" => $request->user()->id
            ])
                ->first();

            // If the business does not exist, return a 404 error
            if (!$business) {
                return response()->json([
                    "message" => "you are not the owner of the business or the request business does not exits"
                ], 404);
            }

            // Get the total number of bookings that this business owner has won
            $data["total"] = Student::where([
                "bookings.status" => "converted_to_job",
                "bookings.business_id" => $business->id

            ])
                ->count();

            // Get the number of bookings that this business owner has won this week
            $data["weekly"] = Student::where([
                "bookings.status" => "converted_to_job",
                "bookings.business_id" => $business->id

            ])
                ->whereBetween('bookings.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count();

            // Get the number of bookings that this business owner has won this month
            $data["monthly"] = Student::where([
                "bookings.status" => "converted_to_job",
                "bookings.business_id" => $business->id

            ])
                ->whereBetween('bookings.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->count();

            // Return the data as a JSON response with a 200 status
            return response()->json($data, 200);
        } catch (Exception $e) {
            // Return error if any
            return $this->sendError($e, 500, $request);
        }
    }




    /**
     *
     * @OA\Get(
     *      path="/v1.0/business-owner-dashboard/upcoming-jobs/{business_id}/{duration}",
     *      operationId="getBusinessOwnerDashboardDataUpcomingJobs",
     *      tags={"dashboard_management.business_admin"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="business_id",
     *         in="path",
     *         description="business_id",
     *         required=true,
     *  example="1"
     *      ),
     *   *              @OA\Parameter(
     *         name="duration",
     *         in="path",
     *         description="duration",
     *         required=true,
     *  example="7"
     *      ),
     *      summary="Total completed Bookings Total Bookings completed by this business owner",
     *      description="Total completed Bookings Total Bookings completed by this business owner",
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
     * Retrieves the count of upcoming jobs for a business owner's dashboard.
     *
     * Retrieves the count of upcoming jobs for a business owner's dashboard
     * within the given duration. The duration is the number of days from today
     * that the jobs should be retrieved for.
     *
     * @param int $business_id The id of the business the jobs should be retrieved for
     * @param int $duration The number of days from today that the jobs should be retrieved for
     * @param Request $request The request object
     *
     * @return \Illuminate\Http\JsonResponse A json response containing the count of upcoming jobs
     */
    public function getBusinessOwnerDashboardDataUpcomingJobs($business_id, $duration, Request $request)
    {
        try {
            // Store the user activity with a dummy activity and description
            // The activity is not actually stored, but the logging is done
            // to fulfill the requirements of the method
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Retrieve the business with the given id and the current user as the owner
            $business = Business::where([
                "id" => $business_id,
                "owner_id" => $request->user()->id
            ])
                ->first();

            // If the business is not found, return a 404 error
            if (!$business) {
                return response()->json([
                    "message" => "you are not the owner of the business or the request business does not exits"
                ], 404);
            }

            // Get the current date and time
            $startDate = now();

            // Add the given duration to the start date
            $endDate = $startDate->copy()->addDays($duration);

            // Retrieve the count of upcoming jobs for the business
            // The jobs should have the status of 'pending'
            // and the job start date should be within the given duration
            $data = Student::where([
                "jobs.status" => "pending",
                "jobs.business_id" => $business->id

            ])
                ->whereBetween('jobs.job_start_date', [$startDate, $endDate])

                // Count the number of jobs retrieved
                ->count();

            // Return the count of upcoming jobs as a json response with a 200 OK status
            return response()->json($data, 200);
        } catch (Exception $e) {
            // If an exception occurs, handle the error by logging and returning a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/business-owner-dashboard/expiring-affiliations/{business_id}/{duration}",
     *      operationId="getBusinessOwnerDashboardDataExpiringAffiliations",
     *      tags={"dashboard_management.business_admin"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="business_id",
     *         in="path",
     *         description="business_id",
     *         required=true,
     *  example="1"
     *      ),
     *   *              @OA\Parameter(
     *         name="duration",
     *         in="path",
     *         description="duration",
     *         required=true,
     *  example="7"
     *      ),
     *      summary="Total completed Bookings Total Bookings completed by this business owner",
     *      description="Total completed Bookings Total Bookings completed by this business owner",
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
     * Retrieves the count of expiring affiliations for a business owner's dashboard.
     *
     * Retrieves the count of expiring affiliations for a business owner's dashboard
     * within the given duration. The duration is the number of days from today
     * that the affiliations should be retrieved for.
     *
     * @param int $business_id The id of the business the affiliations should be retrieved for
     * @param int $duration The number of days from today that the affiliations should be retrieved for
     * @param Request $request The request object
     *
     * @return \Illuminate\Http\JsonResponse A json response containing the count of expiring affiliations
     */
    public function getBusinessOwnerDashboardDataExpiringAffiliations($business_id, $duration, Request $request)
    {
        try {
            // Log the user activity with a dummy activity and description
            // The activity is not actually stored, but the logging is done
            // to fulfill the requirements of the method
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Retrieve the business with the given id and the current user as the owner
            $business = Business::where([
                "id" => $business_id,
                "owner_id" => $request->user()->id
            ])
                ->first();

            // If the business is not found, return a 404 error
            if (!$business) {
                return response()->json([
                    "message" => "you are not the owner of the business or the request business does not exits"
                ], 404);
            }

            // Get the current date and time
            $startDate = now();

            // Add the given duration to the start date
            $endDate = $startDate->copy()->addDays($duration);

            // Retrieve the count of expiring affiliations for the business
            // The affiliations should have an end date that is within the given duration
            $data = Student::with("affiliation")
                ->where('business_affiliations.end_date', "<",  $endDate)
                ->count();

            // Return the count of expiring affiliations as a json response with a 200 OK status
            return response()->json($data, 200);
        } catch (Exception $e) {
            // If an exception occurs, handle the error by logging and returning a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }




    public function applied_jobs($business)
    {
        // Define the start and end dates for the current and previous month
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        // Define the start and end dates for the current and previous week
        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);

        // Calculate the total count of jobs applied by the business in the city
        $data["total_count"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
            ->leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "users.city" => $business->city // Filter by business city
            ])
            ->whereIn('job_bids.business_id', [$business->id]) // Filter by business ID
            ->where('pre_bookings.status', "pending") // Filter by pending status
            ->groupBy("pre_bookings.id") // Group by pre_booking ID
            ->count(); // Get the count of grouped results

        // Get job application data for this week
        $data["this_week_data"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
            ->leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "users.city" => $business->city // Filter by business city
            ])
            ->whereIn('job_bids.business_id', [$business->id]) // Filter by business ID
            ->where('pre_bookings.status', "pending") // Filter by pending status
            ->whereBetween('pre_bookings.created_at', [$startDateOfThisWeek, $endDateOfThisWeek]) // Filter by this week's date range
            ->groupBy("pre_bookings.id") // Group by pre_booking ID
            ->select("job_bids.id", "job_bids.created_at", "job_bids.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Get job application data for the previous week
        $data["previous_week_data"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
            ->leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "users.city" => $business->city // Filter by business city
            ])
            ->whereIn('job_bids.business_id', [$business->id]) // Filter by business ID
            ->where('pre_bookings.status', "pending") // Filter by pending status
            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek]) // Filter by previous week's date range
            ->groupBy("pre_bookings.id") // Group by pre_booking ID
            ->select("job_bids.id", "job_bids.created_at", "job_bids.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Get job application data for this month
        $data["this_month_data"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
            ->leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "users.city" => $business->city // Filter by business city
            ])
            ->whereIn('job_bids.business_id', [$business->id]) // Filter by business ID
            ->where('pre_bookings.status', "pending") // Filter by pending status
            ->whereBetween('pre_bookings.created_at', [$startDateOfThisMonth, $endDateOfThisMonth]) // Filter by this month's date range
            ->groupBy("pre_bookings.id") // Group by pre_booking ID
            ->select("job_bids.id", "job_bids.created_at", "job_bids.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Get job application data for the previous month
        $data["previous_month_data"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
            ->leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "users.city" => $business->city // Filter by business city
            ])
            ->whereIn('job_bids.business_id', [$business->id]) // Filter by business ID
            ->where('pre_bookings.status', "pending") // Filter by pending status
            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth]) // Filter by previous month's date range
            ->groupBy("pre_bookings.id") // Group by pre_booking ID
            ->select("job_bids.id", "job_bids.created_at", "job_bids.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Count the number of job applications for each time period
        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["previous_week_data_count"] = $data["previous_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["previous_month_data_count"] = $data["previous_month_data"]->count();

        // Return the data array
        return $data;
    }


    public function pre_bookings($business)
    {
        // Define the date ranges for this month, previous month, this week, and previous week
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);

        // Get the total number of pre-bookings for this business
        $data["total_count"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')

            // Filter by business city
            ->where([
                "users.city" => $business->city
            ])
            //  ->whereNotIn('job_bids.business_id', [$business->id])
            // Filter by pending status
            ->where('pre_bookings.status', "pending")
            // Count the number of pre-bookings
            ->count();

        // Get the pre-bookings for this week
        $data["this_week_data"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')

            // Filter by business city
            ->where([
                "users.city" => $business->city
            ])

            // Filter by pending status
            ->where('pre_bookings.status', "pending")
            // Filter by this week's date range
            ->whereBetween('pre_bookings.created_at', [$startDateOfThisWeek, $endDateOfThisWeek])
            // Select the ID, created_at, and updated_at columns
            ->select("pre_bookings.id", "pre_bookings.created_at", "pre_bookings.updated_at")
            // Retrieve the data
            ->get();

        // Get the pre-bookings for the previous week
        $data["previous_week_data"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
            // Filter by business city
            ->where([
                "users.city" => $business->city
            ])

            // Filter by pending status
            ->where('pre_bookings.status', "pending")
            // Filter by previous week's date range
            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek])
            // Select the ID, created_at, and updated_at columns
            ->select("pre_bookings.id", "pre_bookings.created_at", "pre_bookings.updated_at")
            // Retrieve the data
            ->get();


        // Get the pre-bookings for this month
        $data["this_month_data"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
            // Filter by business city
            ->where([
                "users.city" => $business->city
            ])

            // Filter by pending status
            ->where('pre_bookings.status', "pending")
            // Filter by this month's date range
            ->whereBetween('pre_bookings.created_at', [$startDateOfThisMonth, $endDateOfThisMonth])
            // Select the ID, created_at, and updated_at columns
            ->select("pre_bookings.id", "pre_bookings.created_at", "pre_bookings.updated_at")
            // Retrieve the data
            ->get();

        // Get the pre-bookings for the previous month
        $data["previous_month_data"] = Student::leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')
            // Filter by business city
            ->where([
                "users.city" => $business->city
            ])

            // Filter by pending status
            ->where('pre_bookings.status', "pending")
            // Filter by previous month's date range
            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth])
            // Select the ID, created_at, and updated_at columns
            ->select("pre_bookings.id", "pre_bookings.created_at", "pre_bookings.updated_at")
            // Retrieve the data
            ->get();


        // Count the number of pre-bookings for each time period
        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["previous_week_data_count"] = $data["previous_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["previous_month_data_count"] = $data["previous_month_data"]->count();

        // Return the data array
        return $data;
    }

    public function winned_jobs($business)
    {
        // Define the start and end dates for the current and previous month
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        // Define the start and end dates for the current and previous week
        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);

        // Calculate the total count of jobs won by the business
        $data["total_data_count"] = Student::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.business_id" => $business->id // Filter by business ID
            ])
            ->where('pre_bookings.status', "booked") // Filter by booked status
            ->groupBy("pre_bookings.id") // Group by pre_booking ID
            ->count(); // Get the count of grouped results

        // Get data of jobs won by the business this week
        $data["this_week_data"] = Student::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.business_id" => $business->id // Filter by business ID
            ])
            ->where('pre_bookings.status', "booked") // Filter by booked status
            ->whereBetween('pre_bookings.created_at', [$startDateOfThisWeek, $endDateOfThisWeek]) // Filter by this week's date range
            ->groupBy("pre_bookings.id") // Group by pre_booking ID
            ->select("bookings.id", "bookings.created_at", "bookings.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Get data of jobs won by the business the previous week
        $data["previous_week_data"] = Student::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.business_id" => $business->id // Filter by business ID
            ])
            ->where('pre_bookings.status', "booked") // Filter by booked status
            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek]) // Filter by previous week's date range
            ->groupBy("pre_bookings.id") // Group by pre_booking ID
            ->select("bookings.id", "bookings.created_at", "bookings.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Get data of jobs won by the business this month
        $data["this_month_data"] = Student::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.business_id" => $business->id // Filter by business ID
            ])
            ->where('pre_bookings.status', "booked") // Filter by booked status
            ->whereBetween('pre_bookings.created_at', [$startDateOfThisMonth, $endDateOfThisMonth]) // Filter by this month's date range
            ->groupBy("pre_bookings.id") // Group by pre_booking ID
            ->select("bookings.id", "bookings.created_at", "bookings.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Get data of jobs won by the business the previous month
        $data["previous_month_data"] = Student::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.business_id" => $business->id // Filter by business ID
            ])
            ->where('pre_bookings.status', "booked") // Filter by booked status
            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth]) // Filter by previous month's date range
            ->groupBy("pre_bookings.id") // Group by pre_booking ID
            ->select("bookings.id", "bookings.created_at", "bookings.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Count the number of jobs won for each time period
        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["previous_week_data_count"] = $data["previous_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["previous_month_data_count"] = $data["previous_month_data"]->count();

        // Return the data array
        return $data;
    }


    public function completed_bookings($business)
    {
        // Set the dates for this month and last month
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        // Set the dates for this week and last week
        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);

        // Retrieve the total number of completed bookings for the business
        $data["total_data_count"] = Student::where([
            "bookings.status" => "converted_to_job",
            "bookings.business_id" => $business->id

        ])
            ->count();

        // Retrieve the completed bookings from this week
        $data["this_week_data"] = Student::where([
            "bookings.status" => "converted_to_job",
            "bookings.business_id" => $business->id

        ])
            ->whereBetween('bookings.created_at', [$startDateOfThisWeek, $endDateOfThisWeek])
            ->select("bookings.id", "bookings.created_at", "bookings.updated_at")
            ->get();

        // Retrieve the completed bookings from last week
        $data["previous_week_data"] = Student::where([
            "bookings.status" => "converted_to_job",
            "bookings.business_id" => $business->id

        ])
            ->whereBetween('bookings.created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek])
            ->select("bookings.id", "bookings.created_at", "bookings.updated_at")
            ->get();

        // Retrieve the completed bookings from this month
        $data["this_month_data"] = Student::where([
            "bookings.status" => "converted_to_job",
            "bookings.business_id" => $business->id

        ])
            ->whereBetween('bookings.created_at', [$startDateOfThisMonth, $endDateOfThisMonth])
            ->select("bookings.id", "bookings.created_at", "bookings.updated_at")
            ->get();

        // Retrieve the completed bookings from last month
        $data["previous_month_data"] = Student::where([
            "bookings.status" => "converted_to_job",
            "bookings.business_id" => $business->id

        ])
            ->whereBetween('bookings.created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth])
            ->select("bookings.id", "bookings.created_at", "bookings.updated_at")
            ->get();

        // Count the number of completed bookings in each time period
        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["previous_week_data_count"] = $data["previous_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["previous_month_data_count"] = $data["previous_month_data"]->count();

        // Return the data array
        return $data;
    }

    public function upcoming_jobs($business)
    {
        // Initialize the current date and time
        $startDate = now();

        // Determine the end date of the current month
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        // Determine the start and end dates of the next month
        $startDateOfNextMonth = Carbon::now()->startOfMonth()->addMonth(1);
        $endDateOfNextMonth = Carbon::now()->endOfMonth()->addMonth(1);

        // Determine the end date of the current week
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        // Determine the start and end dates of the next week
        $startDateOfNextWeek = Carbon::now()->startOfWeek()->addWeek(1);
        $endDateOfNextWeek = Carbon::now()->endOfWeek()->addWeek(1);

        // Retrieve the total count of pending jobs for the given business
        $data["total_data_count"] = Student::where([
            "jobs.status" => "pending", // Filter by pending status
            "jobs.business_id" => $business->id // Filter by business ID
        ])->count(); // Get the count of matching records

        // Retrieve pending jobs for the current week
        $data["this_week_data"] = Student::where([
            "jobs.status" => "pending", // Filter by pending status
            "jobs.business_id" => $business->id // Filter by business ID
        ])->whereBetween('jobs.job_start_date', [$startDate, $endDateOfThisWeek]) // Filter by current week's date range
            ->select("jobs.id", "jobs.created_at", "jobs.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Retrieve pending jobs for the next week
        $data["next_week_data"] = Student::where([
            "jobs.status" => "pending", // Filter by pending status
            "jobs.business_id" => $business->id // Filter by business ID
        ])->whereBetween('jobs.job_start_date', [$startDateOfNextWeek, $endDateOfNextWeek]) // Filter by next week's date range
            ->select("jobs.id", "jobs.created_at", "jobs.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Retrieve pending jobs for the current month
        $data["this_month_data"] = Student::where([
            "jobs.status" => "pending", // Filter by pending status
            "jobs.business_id" => $business->id // Filter by business ID
        ])->whereBetween('jobs.job_start_date', [$startDate, $endDateOfThisMonth]) // Filter by current month's date range
            ->select("jobs.id", "jobs.created_at", "jobs.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Retrieve pending jobs for the next month
        $data["next_month_data"] = Student::where([
            "jobs.status" => "pending", // Filter by pending status
            "jobs.business_id" => $business->id // Filter by business ID
        ])->whereBetween('jobs.job_start_date', [$startDateOfNextMonth, $endDateOfNextMonth]) // Filter by next month's date range
            ->select("jobs.id", "jobs.created_at", "jobs.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Count the number of pending jobs for each time period
        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["next_week_data_count"] = $data["next_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["next_month_data_count"] = $data["next_month_data"]->count();

        // Return the data array containing job counts and details
        return $data;
    }
    public function affiliation_expirings($business)
    {
        // Initialize the current date and time
        $startDate = now();

        // Determine the end date of the current month
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        // Determine the start and end dates of the next month
        $startDateOfNextMonth = Carbon::now()->startOfMonth()->addMonth(1);
        $endDateOfNextMonth = Carbon::now()->endOfMonth()->addMonth(1);

        // Determine the end date of the current week
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        // Determine the start and end dates of the next week
        $startDateOfNextWeek = Carbon::now()->startOfWeek()->addWeek(1);
        $endDateOfNextWeek = Carbon::now()->endOfWeek()->addWeek(1);

        // Retrieve the total count of affiliations for the given business
        $data["total_data_count"] = Student::where([
            "business_affiliations.business_id" => $business->id // Filter by business ID
        ])->count(); // Get the count of matching records

        // Retrieve affiliations expiring this week
        $data["this_week_data"] = Student::where([
            "business_affiliations.business_id" => $business->id // Filter by business ID
        ])->whereBetween('business_affiliations.end_date', [$startDate, $endDateOfThisWeek]) // Filter by current week's date range
            ->select("business_affiliations.id", "business_affiliations.created_at", "business_affiliations.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Retrieve affiliations expiring next week
        $data["next_week_data"] = Student::where([
            "business_affiliations.business_id" => $business->id // Filter by business ID
        ])->whereBetween('business_affiliations.end_date', [$startDateOfNextWeek, $endDateOfNextWeek]) // Filter by next week's date range
            ->select("business_affiliations.id", "business_affiliations.created_at", "business_affiliations.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Retrieve affiliations expiring this month
        $data["this_month_data"] = Student::where([
            "business_affiliations.business_id" => $business->id // Filter by business ID
        ])->whereBetween('business_affiliations.end_date', [$startDate, $endDateOfThisMonth]) // Filter by current month's date range
            ->select("business_affiliations.id", "business_affiliations.created_at", "business_affiliations.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Retrieve affiliations expiring next month
        $data["next_month_data"] = Student::where([
            "business_affiliations.business_id" => $business->id // Filter by business ID
        ])->whereBetween('business_affiliations.end_date', [$startDateOfNextMonth, $endDateOfNextMonth]) // Filter by next month's date range
            ->select("business_affiliations.id", "business_affiliations.created_at", "business_affiliations.updated_at") // Select specific columns
            ->get(); // Retrieve the data

        // Count the number of affiliations expiring in each time period
        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["next_week_data_count"] = $data["next_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["next_month_data_count"] = $data["next_month_data"]->count();

        // Return the data array containing counts and details of expiring affiliations
        return $data;
    }

    public function employees(
        $today,
        $start_date_of_next_month,
        $end_date_of_next_month,
        $start_date_of_this_month,
        $end_date_of_this_month,
        $start_date_of_previous_month,
        $end_date_of_previous_month,
        $start_date_of_next_week,
        $end_date_of_next_week,
        $start_date_of_this_week,
        $end_date_of_this_week,
        $start_date_of_previous_week,
        $end_date_of_previous_week,
        $all_manager_department_ids
    ) {

        // Retrieve the count of employees across all of the departments managed by the user
        // by filtering the User model by the list of departments managed by the user
        $data_query  = User::whereHas("departments", function ($query) use ($all_manager_department_ids) {
            // Filter the departments by the list of department IDs managed by the user
            $query->whereIn("departments.id", $all_manager_department_ids);
        })
            ->whereNotIn('id', [auth()->user()->id]) // Exclude the current user from the results
            ->where('is_in_employee', 1) // Only include users with an employee account
            ->where('is_active', 1); // Only include active users

        // Retrieve the total count of employees managed by the user
        $data["total_data_count"] = $data_query->count();

        // Retrieve the count of employees created today
        $data["today_data_count"] = clone $data_query;
        $data["today_data_count"] = $data["today_data_count"]
            ->whereBetween('users.created_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->count();

        // Retrieve the count of employees created this week
        $data["this_week_data_count"] = clone $data_query;
        $data["this_week_data_count"] = $data["this_week_data_count"]
            ->whereBetween('created_at', [$start_date_of_this_week, ($end_date_of_this_week . ' 23:59:59')])
            ->count();

        // Retrieve the count of employees created last week
        $data["previous_week_data_count"] = clone $data_query;
        $data["previous_week_data_count"] = $data["previous_week_data_count"]
            ->whereBetween('created_at', [$start_date_of_previous_week, ($end_date_of_previous_week . ' 23:59:59')])
            ->count();

        // Retrieve the count of employees created this month
        $data["this_month_data_count"] = clone $data_query;
        $data["this_month_data_count"] = $data["this_month_data_count"]
            ->whereBetween('created_at', [$start_date_of_this_month, ($end_date_of_this_month . ' 23:59:59')])
            ->count();

        // Retrieve the count of employees created last month
        $data["previous_month_data_count"] = clone $data_query;
        $data["previous_month_data_count"] = $data["previous_month_data_count"]
            ->whereBetween('created_at', [$start_date_of_previous_month, ($end_date_of_previous_month . ' 23:59:59')])
            ->count();

        // Return the array of counts
        return $data;
    }

    // public function approved_leaves(
    //     $today,
    //     $start_date_of_this_month,
    //     $end_date_of_this_month,
    //     $start_date_of_previous_month,
    //     $end_date_of_previous_month,
    //     $start_date_of_this_week,
    //     $end_date_of_this_week,
    //     $start_date_of_previous_week,
    //     $end_date_of_previous_week,
    //     $all_manager_department_ids
    // )
    // {



    //     $data_query  = LeaveRecord::whereHas("leave.employee.departments", function($query) use($all_manager_department_ids) {
    //        $query->whereIn("departments.id",$all_manager_department_ids);
    //     })
    //     ->whereHas("leave", function($query) use($all_manager_department_ids) {
    //         $query->where([
    //             "leaves.business_id" => auth()->user()->business_id,
    //             "leaves.status" => "approved"
    //             ]);
    //      })
    //         ->whereNotIn('id', [auth()->user()->id])
    //         ->where('is_in_employee', 1)
    //         ->where('is_active', 1);

    //     $data["total_data_count"] = $data_query->count();
    //     $data["today_data_count"] = $data_query->whereBetween('date', [$today, ($today . ' 23:59:59')])->count();
    //     $data["this_week_data_count"] = $data_query->whereBetween('date', [$start_date_of_this_week, ($end_date_of_this_week . ' 23:59:59')])->count();
    //     $data["previous_week_data_count"] = $data_query->whereBetween('date', [$start_date_of_previous_week, ($end_date_of_previous_week . ' 23:59:59')])->count();
    //     $data["this_month_data_count"] = $data_query->whereBetween('date', [$start_date_of_this_month, ($end_date_of_this_month . ' 23:59:59')])->count();
    //     $data["previous_month_data_count"] = $data_query->whereBetween('date', [$start_date_of_previous_month, ($end_date_of_previous_month . ' 23:59:59')])->count();

    //     return $data;
    // }

    public function employee_on_holiday(
        $today,
        $start_date_of_next_month,
        $end_date_of_next_month,
        $start_date_of_this_month,
        $end_date_of_this_month,
        $start_date_of_previous_month,
        $end_date_of_previous_month,
        $start_date_of_next_week,
        $end_date_of_next_week,
        $start_date_of_this_week,
        $end_date_of_this_week,
        $start_date_of_previous_week,
        $end_date_of_previous_week,
        $all_manager_department_ids

    ) {
        $total_departments = Department::where([
            "business_id" => auth()->user()->business_id,
            "is_active" => 1
        ])->count();

        $data_query  = User::whereHas("departments", function ($query) use ($all_manager_department_ids) {
            $query->whereIn("departments.id", $all_manager_department_ids);
        })
            ->whereNotIn('id', [auth()->user()->id])
            ->where('is_in_employee', 1)
            ->where('is_active', 1)
            ->where("business_id", auth()->user()->id);


        // $data["total_data_count"] = $data_query->count();

        $data["today_data_count"] = clone $data_query;
        $data["today_data_count"] = $data["today_data_count"]

            ->where(function ($query) use ($today, $total_departments) {
                $query->where(function ($query) use ($today, $total_departments) {

                    $query->where(function ($query) use ($today, $total_departments) {
                        $query->whereHas('holidays', function ($query) use ($today) {
                            $query->where('holidays.start_date', "<=",  $today->copy()->startOfDay())
                                ->where('holidays.end_date', ">=",  $today->copy()->endOfDay());
                        })
                            ->orWhere(function ($query) use ($today, $total_departments) {
                                $query->whereHasRecursiveHolidays($today, $total_departments);
                            });

                        // ->whereHas('departments.holidays', function ($query) use ($today) {
                        //     $query->where('holidays.start_date', "<=",  $today->copy()->startOfDay())
                        //     ->where('holidays.end_date', ">=",  $today->copy()->endOfDay());
                        // });

                    })
                        ->where(function ($query) use ($today) {
                            $query->orWhereDoesntHave('holidays', function ($query) use ($today) {
                                $query->where('holidays.start_date', "<=",  $today->copy()->startOfDay())
                                    ->where('holidays.end_date', ">=",  $today->copy()->endOfDay())
                                    ->orWhere(function ($query) {
                                        $query->whereDoesntHave("users")
                                            ->whereDoesntHave("departments");
                                    });
                            });
                        });
                })
                    ->orWhere(
                        function ($query) use ($today) {
                            $query->orWhereDoesntHave('holidays', function ($query) use ($today) {
                                $query->where('holidays.start_date', "<=",  $today->copy()->startOfDay());
                                $query->where('holidays.end_date', ">=",  $today->copy()->endOfDay());
                                $query->doesntHave('users');
                            });
                        }
                    );
            })



            ->count();

        // $data["next_week_data_count"] = clone $data_query;
        // $data["next_week_data_count"] = $data["next_week_data_count"]

        // ->where(function($query) use ($start_date_of_next_week,$end_date_of_next_week) {
        //     $query->whereHas('departments.holidays', function ($query) use ($start_date_of_next_week,$end_date_of_next_week) {
        //         $query->where('holidays.start_date', "<=",  $start_date_of_next_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_next_week . ' 23:59:59');
        //     })->orWhereDoesntHave('departments.holidays', function ($query) use ($start_date_of_next_week,$end_date_of_next_week) {
        //         $query->where('holidays.start_date', "<=",  $start_date_of_next_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_next_week . ' 23:59:59');
        //         $query->doesntHave('departments');

        //     });
        // })
        // ->orWhere(function($query) use ($start_date_of_next_week,$end_date_of_next_week) {
        //     $query->whereHas('holidays', function ($query) use ($start_date_of_next_week,$end_date_of_next_week) {
        //         $query->where('holidays.start_date', "<=",  $start_date_of_next_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_next_week . ' 23:59:59');
        //     })->orWhereDoesntHave('holidays', function ($query) use ($start_date_of_next_week,$end_date_of_next_week) {
        //         $query->where('holidays.start_date', "<=",  $start_date_of_next_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_next_week . ' 23:59:59');
        //         $query->doesntHave('users');

        //     });
        // })

        // ->count();

        // $data["this_week_data_count"] = clone $data_query;
        // $data["this_week_data_count"] = $data["this_week_data_count"]

        // ->where(function($query) use ( $start_date_of_this_week,$end_date_of_this_week) {
        //     $query->whereHas('departments.holidays', function ($query) use ( $start_date_of_this_week,$end_date_of_this_week) {
        //         $query->where('holidays.start_date', "<=",  $start_date_of_this_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_this_week . ' 23:59:59');
        //     })->orWhereDoesntHave('departments.holidays', function ($query) use ( $start_date_of_this_week,$end_date_of_this_week) {
        //         $query->where('holidays.start_date', "<=",  $start_date_of_this_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_this_week . ' 23:59:59');
        //         $query->doesntHave('departments');
        //     });
        // })

        // ->orWhere(function($query) use ( $start_date_of_this_week,$end_date_of_this_week) {
        //     $query->whereHas('holidays', function ($query) use ( $start_date_of_this_week,$end_date_of_this_week) {
        //         $query->where('holidays.start_date', "<=",  $start_date_of_this_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_this_week . ' 23:59:59');
        //     })->orWhereDoesntHave('holidays', function ($query) use ( $start_date_of_this_week,$end_date_of_this_week) {

        //         $query->where('holidays.start_date', "<=",  $start_date_of_this_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_this_week . ' 23:59:59');
        //         $query->doesntHave('users');


        //     });
        // })



        // ->count();

        // $data["previous_week_data_count"] = clone $data_query;
        // $data["previous_week_data_count"] = $data["previous_week_data_count"]
        // ->where(function($query) use ($start_date_of_previous_week,$end_date_of_previous_week) {
        //     $query->whereHas('departments.holidays', function ($query) use ($start_date_of_previous_week,$end_date_of_previous_week) {
        //         $query->where('holidays.start_date', "<=",  $start_date_of_previous_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_previous_week . ' 23:59:59');
        //     })->orWhereDoesntHave('departments.holidays', function ($query) use ($start_date_of_previous_week,$end_date_of_previous_week) {

        //         $query->where('holidays.start_date', "<=",  $start_date_of_previous_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_previous_week . ' 23:59:59');
        //         $query->doesntHave('departments');

        //     });
        // })
        // ->orWhere(function($query) use ($start_date_of_previous_week,$end_date_of_previous_week) {
        //     $query->whereHas('holidays', function ($query) use ($start_date_of_previous_week,$end_date_of_previous_week) {
        //         $query->where('holidays.start_date', "<=",  $start_date_of_previous_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_previous_week . ' 23:59:59');
        //     })->orWhereDoesntHave('holidays', function ($query) use ($start_date_of_previous_week,$end_date_of_previous_week) {

        //         $query->where('holidays.start_date', "<=",  $start_date_of_previous_week);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_previous_week . ' 23:59:59');
        //         $query->doesntHave('users');

        //     });
        // })



        // ->count();

        // $data["next_month_data_count"] = clone $data_query;
        // $data["next_month_data_count"] = $data["next_month_data_count"]
        // ->where(function($query) use ($start_date_of_next_month,$end_date_of_next_month) {
        //     $query->whereHas('departments.holidays', function ($query) use ( $start_date_of_next_month,$end_date_of_next_month) {
        //         $query->where('holidays.start_date', "<=",  $start_date_of_next_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_next_month . ' 23:59:59');
        //     })->orWhereDoesntHave('departments.holidays', function ($query) use ($start_date_of_next_month,$end_date_of_next_month) {

        //          $query->where('holidays.start_date', "<=",  $start_date_of_next_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_next_month . ' 23:59:59');
        //         $query->doesntHave('departments');


        //     });
        // })
        // ->orWhere(function($query) use ($start_date_of_next_month,$end_date_of_next_month) {
        //     $query->whereHas('holidays', function ($query) use ( $start_date_of_next_month,$end_date_of_next_month) {
        //         $query->where('holidays.start_date', "<=",  $start_date_of_next_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_next_month . ' 23:59:59');
        //     })->orWhereDoesntHave('holidays', function ($query) use ($start_date_of_next_month,$end_date_of_next_month) {

        //          $query->where('holidays.start_date', "<=",  $start_date_of_next_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_next_month . ' 23:59:59');
        //         $query->doesntHave('users');


        //     });
        // })


        // ->count();

        // $data["this_month_data_count"] = clone $data_query;
        // $data["this_month_data_count"] = $data["this_month_data_count"]
        // ->where(function($query) use ( $start_date_of_this_month,$end_date_of_this_month) {
        //     $query->whereHas('departments.holidays', function ($query) use ( $start_date_of_this_month,$end_date_of_this_month) {


        //         $query->where('holidays.start_date', "<=",  $start_date_of_this_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_this_month . ' 23:59:59');


        //     })->orWhereDoesntHave('departments.holidays', function ($query) use ( $start_date_of_this_month,$end_date_of_this_month) {

        //         $query->where('holidays.start_date', "<=",  $start_date_of_this_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_this_month . ' 23:59:59');
        //         $query->doesntHave('departments');


        //     });

        // })

        // ->orWhere(function($query) use ( $start_date_of_this_month,$end_date_of_this_month) {
        //     $query->whereHas('holidays', function ($query) use ( $start_date_of_this_month,$end_date_of_this_month) {


        //         $query->where('holidays.start_date', "<=",  $start_date_of_this_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_this_month . ' 23:59:59');


        //     })->orWhereDoesntHave('holidays', function ($query) use ( $start_date_of_this_month,$end_date_of_this_month) {

        //         $query->where('holidays.start_date', "<=",  $start_date_of_this_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_this_month . ' 23:59:59');
        //         $query->doesntHave('users');


        //     });

        // })

        // ->count();


        // $data["previous_month_data_count"] = clone $data_query;
        // $data["previous_month_data_count"] = $data["previous_month_data_count"]

        // ->where(function($query) use ($start_date_of_previous_month,$end_date_of_previous_month) {
        //     $query ->whereHas('departments.holidays', function ($query) use ($start_date_of_previous_month,$end_date_of_previous_month) {

        //         $query->where('holidays.start_date', "<=",  $start_date_of_previous_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_previous_month . ' 23:59:59');


        //     })->orWhereDoesntHave('departments.holidays', function ($query) use ($start_date_of_previous_month,$end_date_of_previous_month) {

        //         $query->where('holidays.start_date', "<=",  $start_date_of_previous_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_previous_month . ' 23:59:59');
        //         $query->doesntHave('departments');

        //     });



        // })

        // ->orWhere(function($query) use ($start_date_of_previous_month,$end_date_of_previous_month) {
        //     $query ->whereHas('holidays', function ($query) use ($start_date_of_previous_month,$end_date_of_previous_month) {

        //         $query->where('holidays.start_date', "<=",  $start_date_of_previous_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_previous_month . ' 23:59:59');




        //     })->orWhereDoesntHave('holidays', function ($query) use ($start_date_of_previous_month,$end_date_of_previous_month) {

        //         $query->where('holidays.start_date', "<=",  $start_date_of_previous_month);
        //         $query->where('holidays.end_date', ">=",  $end_date_of_previous_month . ' 23:59:59');
        //         $query->doesntHave('users');

        //     });



        // })




        // ->count();

        return $data;
    }




    public function leaves(
        $today,
        $start_date_of_next_month,
        $end_date_of_next_month,
        $start_date_of_this_month,
        $end_date_of_this_month,
        $start_date_of_previous_month,
        $end_date_of_previous_month,
        $start_date_of_next_week,
        $end_date_of_next_week,
        $start_date_of_this_week,
        $end_date_of_this_week,
        $start_date_of_previous_week,
        $end_date_of_previous_week,
        $all_manager_department_ids,
        $status
    ) {

        $data_query  = LeaveRecord::whereHas("leave.employee.departments", function ($query) use ($all_manager_department_ids) {
            $query->whereIn("departments.id", $all_manager_department_ids);
        })
            ->whereHas("leave", function ($query) use ($status) {
                $query->where([
                    "leaves.business_id" => auth()->user()->business_id,
                    "leaves.status" => $status
                ]);
            });

        $data["total_data_count"] = $data_query->count();

        $data["today_data_count"] = clone $data_query;
        $data["today_data_count"] = $data["today_data_count"]->whereBetween('date', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->count();

        $data["next_week_data_count"] = clone $data_query;
        $data["next_week_data_count"] = $data["next_week_data_count"]->whereBetween('date', [$start_date_of_next_week, ($end_date_of_next_week . ' 23:59:59')])->count();

        $data["this_week_data_count"] = clone $data_query;
        $data["this_week_data_count"] = $data["this_week_data_count"]->whereBetween('date', [$start_date_of_this_week, ($end_date_of_this_week . ' 23:59:59')])->count();

        $data["previous_week_data_count"] = clone $data_query;
        $data["previous_week_data_count"] = $data["previous_week_data_count"]->whereBetween('date', [$start_date_of_previous_week, ($end_date_of_previous_week . ' 23:59:59')])->count();

        $data["next_month_data_count"] = clone $data_query;
        $data["next_month_data_count"] = $data["next_month_data_count"]->whereBetween('date', [$start_date_of_next_month, ($end_date_of_next_month . ' 23:59:59')])->count();

        $data["this_month_data_count"] = clone $data_query;
        $data["this_month_data_count"] = $data["this_month_data_count"]->whereBetween('date', [$start_date_of_this_month, ($end_date_of_this_month . ' 23:59:59')])->count();

        $data["previous_month_data_count"] = clone $data_query;
        $data["previous_month_data_count"] = $data["previous_month_data_count"]->whereBetween('date', [$start_date_of_previous_month, ($end_date_of_previous_month . ' 23:59:59')])->count();

        return $data;
    }

    public function open_roles(
        $today,
        $start_date_of_next_month,
        $end_date_of_next_month,
        $start_date_of_this_month,
        $end_date_of_this_month,
        $start_date_of_previous_month,
        $end_date_of_previous_month,
        $start_date_of_next_week,
        $end_date_of_next_week,
        $start_date_of_this_week,
        $end_date_of_this_week,
        $start_date_of_previous_week,
        $end_date_of_previous_week,
        $all_manager_department_ids
    ) {

        $data_query  = JobListing::where("application_deadline", ">=", today())
            ->where("business_id", auth()->user()->business_id);

        $data["total_data_count"] = $data_query->count();

        $data["today_data_count"] = clone $data_query;
        $data["today_data_count"] = $data["today_data_count"]->whereBetween('application_deadline', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->count();

        $data["next_week_data_count"] = clone $data_query;
        $data["next_week_data_count"] = $data["next_week_data_count"]->whereBetween('application_deadline', [$start_date_of_next_week, ($end_date_of_next_week . ' 23:59:59')])->count();

        $data["this_week_data_count"] = clone $data_query;
        $data["this_week_data_count"] = $data["this_week_data_count"]->whereBetween('application_deadline', [$start_date_of_this_week, ($end_date_of_this_week . ' 23:59:59')])->count();



        $data["next_month_data_count"] = clone $data_query;
        $data["next_month_data_count"] = $data["next_month_data_count"]->whereBetween('application_deadline', [$start_date_of_next_month, ($end_date_of_next_month . ' 23:59:59')])->count();

        $data["this_month_data_count"] = clone $data_query;
        $data["this_month_data_count"] = $data["this_month_data_count"]->whereBetween('application_deadline', [$start_date_of_this_month, ($end_date_of_this_month . ' 23:59:59')])->count();



        return $data;
    }


    /**
     * Get the number of upcoming passport expiries for the user.
     *
     * @param Carbon $today
     * @param Carbon $start_date_of_next_month
     * @param Carbon $end_date_of_next_month
     * @param Carbon $start_date_of_this_month
     * @param Carbon $end_date_of_this_month
     * @param Carbon $start_date_of_previous_month
     * @param Carbon $end_date_of_previous_month
     * @param Carbon $start_date_of_next_week
     * @param Carbon $end_date_of_next_week
     * @param Carbon $start_date_of_this_week
     * @param Carbon $end_date_of_this_week
     * @param Carbon $start_date_of_previous_week
     * @param Carbon $end_date_of_previous_week
     * @param array $all_manager_department_ids
     * @return array
     */
    public function upcoming_passport_expiries(
        $today,
        $start_date_of_next_month,
        $end_date_of_next_month,
        $start_date_of_this_month,
        $end_date_of_this_month,
        $start_date_of_previous_month,
        $end_date_of_previous_month,
        $start_date_of_next_week,
        $end_date_of_next_week,
        $start_date_of_this_week,
        $end_date_of_this_week,
        $start_date_of_previous_week,
        $end_date_of_previous_week,
        $all_manager_department_ids
    ) {

        // Get the total number of upcoming passport expiries
        $data_query  = EmployeePassportDetail::whereHas("employee.departments", function ($query) use ($all_manager_department_ids) {
            // Filter the departments by the list of department IDs managed by the user
            $query->whereIn("departments.id", $all_manager_department_ids);
        })
            ->where("passport_expiry_date", ">=", today())
            ->where("business_id", auth()->user()->business_id);

        // Get the total number of upcoming passport expiries
        $data["total_data_count"] = $data_query->count();

        // Get the number of passport expiries for today
        $data["today_data_count"] = clone $data_query;
        $data["today_data_count"] = $data["today_data_count"]->whereBetween('passport_expiry_date', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->count();

        // Get the number of passport expiries for the next week
        $data["next_week_data_count"] = clone $data_query;
        $data["next_week_data_count"] = $data["next_week_data_count"]->whereBetween('passport_expiry_date', [$start_date_of_next_week, ($end_date_of_next_week . ' 23:59:59')])->count();

        // Get the number of passport expiries for the current week
        $data["this_week_data_count"] = clone $data_query;
        $data["this_week_data_count"] = $data["this_week_data_count"]->whereBetween('passport_expiry_date', [$start_date_of_this_week, ($end_date_of_this_week . ' 23:59:59')])->count();

        // Get the number of passport expiries for the next month
        $data["next_month_data_count"] = clone $data_query;
        $data["next_month_data_count"] = $data["next_month_data_count"]->whereBetween('passport_expiry_date', [$start_date_of_next_month, ($end_date_of_next_month . ' 23:59:59')])->count();

        // Get the number of passport expiries for the current month
        $data["this_month_data_count"] = clone $data_query;
        $data["this_month_data_count"] = $data["this_month_data_count"]->whereBetween('passport_expiry_date', [$start_date_of_this_month, ($end_date_of_this_month . ' 23:59:59')])->count();

        // Get the number of passport expiries for the previous month
        $data["previous_month_data_count"] = clone $data_query;
        $data["previous_month_data_count"] = $data["previous_month_data_count"]->whereBetween('passport_expiry_date', [$start_date_of_previous_month, ($end_date_of_previous_month . ' 23:59:59')])->count();

        $expires_in_days = [15, 30, 60];
        foreach ($expires_in_days as $expires_in_day) {
            // Set the query date to the current date plus the number of days
            $query_day = Carbon::now()->addDays($expires_in_day);

            // Get the number of passport expiries in the next $expires_in_day days
            $data[("expires_in_" . $expires_in_day . "_days")] = clone $data_query;
            $data[("expires_in_" . $expires_in_day . "_days")] = $data[("expires_in_" . $expires_in_day . "_days")]->whereBetween('passport_expiry_date', [$today, ($query_day->endOfDay() . ' 23:59:59')])->count();
        }

        // Return the data
        return $data;
    }

    /**
     * This method returns the number of upcoming visa expiries for the user.
     * This data is used to populate the dashboard.
     *
     * @param Carbon $today
     * @param Carbon $start_date_of_next_month
     * @param Carbon $end_date_of_next_month
     * @param Carbon $start_date_of_this_month
     * @param Carbon $end_date_of_this_month
     * @param Carbon $start_date_of_previous_month
     * @param Carbon $end_date_of_previous_month
     * @param Carbon $start_date_of_next_week
     * @param Carbon $end_date_of_next_week
     * @param Carbon $start_date_of_this_week
     * @param Carbon $end_date_of_this_week
     * @param Carbon $start_date_of_previous_week
     * @param Carbon $end_date_of_previous_week
     * @param array $all_manager_department_ids
     * @return array
     */
    public function upcoming_visa_expiries(
        $today,
        $start_date_of_next_month,
        $end_date_of_next_month,
        $start_date_of_this_month,
        $end_date_of_this_month,
        $start_date_of_previous_month,
        $end_date_of_previous_month,
        $start_date_of_next_week,
        $end_date_of_next_week,
        $start_date_of_this_week,
        $end_date_of_this_week,
        $start_date_of_previous_week,
        $end_date_of_previous_week,
        $all_manager_department_ids
    ) {

        // Get the total number of upcoming visa expiries
        $data_query  = EmployeeVisaDetail::whereHas("employee.departments", function ($query) use ($all_manager_department_ids) {
            // Filter the departments by the list of department IDs managed by the user
            $query->whereIn("departments.id", $all_manager_department_ids);
        })
            ->where("visa_expiry_date", ">=", today())
            ->where("business_id", auth()->user()->business_id);

        // Get the total number of upcoming visa expiries
        $data["total_data_count"] = $data_query->count();

        // Get the number of visa expiries for today
        $data["today_data_count"] = clone $data_query;
        $data["today_data_count"] = $data["today_data_count"]->whereBetween('visa_expiry_date', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->count();

        // Get the number of visa expiries for the next week
        $data["next_week_data_count"] = clone $data_query;
        $data["next_week_data_count"] = $data["next_week_data_count"]->whereBetween('visa_expiry_date', [$start_date_of_next_week, ($end_date_of_next_week . ' 23:59:59')])->count();

        // Get the number of visa expiries for the current week
        $data["this_week_data_count"] = clone $data_query;
        $data["this_week_data_count"] = $data["this_week_data_count"]->whereBetween('visa_expiry_date', [$start_date_of_this_week, ($end_date_of_this_week . ' 23:59:59')])->count();

        // Get the number of visa expiries for the next month
        $data["next_month_data_count"] = clone $data_query;
        $data["next_month_data_count"] = $data["next_month_data_count"]->whereBetween('visa_expiry_date', [$start_date_of_next_month, ($end_date_of_next_month . ' 23:59:59')])->count();

        // Get the number of visa expiries for the current month
        $data["this_month_data_count"] = clone $data_query;
        $data["this_month_data_count"] = $data["this_month_data_count"]->whereBetween('visa_expiry_date', [$start_date_of_this_month, ($end_date_of_this_month . ' 23:59:59')])->count();

        // Get the number of visa expiries for the previous month
        $data["previous_month_data_count"] = clone $data_query;
        $data["previous_month_data_count"] = $data["previous_month_data_count"]->whereBetween('visa_expiry_date', [$start_date_of_previous_month, ($end_date_of_previous_month . ' 23:59:59')])->count();

        // Get the number of visa expiries in the next 15, 30, 60 days
        $expires_in_days = [15, 30, 60];
        foreach ($expires_in_days as $expires_in_day) {
            // Set the query date to the current date plus the number of days
            $query_day = Carbon::now()->addDays($expires_in_day);

            // Get the number of visa expiries in the next $expires_in_day days
            $data[("expires_in_" . $expires_in_day . "_days")] = clone $data_query;
            $data[("expires_in_" . $expires_in_day . "_days")] = $data[("expires_in_" . $expires_in_day . "_days")]->whereBetween('visa_expiry_date', [$today, ($query_day->endOfDay() . ' 23:59:59')])->count();
        }

        // Return the data
        return $data;
    }
    public function upcoming_sponsorship_expiries(
        $today,
        $start_date_of_next_month,
        $end_date_of_next_month,
        $start_date_of_this_month,
        $end_date_of_this_month,
        $start_date_of_previous_month,
        $end_date_of_previous_month,
        $start_date_of_next_week,
        $end_date_of_next_week,
        $start_date_of_this_week,
        $end_date_of_this_week,
        $start_date_of_previous_week,
        $end_date_of_previous_week,
        $all_manager_department_ids
    ) {

        $data_query  = EmployeeSponsorship::whereHas("employee.departments", function ($query) use ($all_manager_department_ids) {
            $query->whereIn("departments.id", $all_manager_department_ids);
        })

            ->where("expiry_date", ">=", today())
            ->where("business_id", auth()->user()->business_id);

        $data["total_data_count"] = $data_query->count();

        $data["today_data_count"] = clone $data_query;
        $data["today_data_count"] = $data["today_data_count"]->whereBetween('expiry_date', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->count();

        $data["next_week_data_count"] = clone $data_query;
        $data["next_week_data_count"] = $data["next_week_data_count"]->whereBetween('expiry_date', [$start_date_of_next_week, ($end_date_of_next_week . ' 23:59:59')])->count();

        $data["this_week_data_count"] = clone $data_query;
        $data["this_week_data_count"] = $data["this_week_data_count"]->whereBetween('expiry_date', [$start_date_of_this_week, ($end_date_of_this_week . ' 23:59:59')])->count();



        $data["next_month_data_count"] = clone $data_query;
        $data["next_month_data_count"] = $data["next_month_data_count"]->whereBetween('expiry_date', [$start_date_of_next_month, ($end_date_of_next_month . ' 23:59:59')])->count();

        $data["this_month_data_count"] = clone $data_query;
        $data["this_month_data_count"] = $data["this_month_data_count"]->whereBetween('expiry_date', [$start_date_of_this_month, ($end_date_of_this_month . ' 23:59:59')])->count();


        $expires_in_days = [15, 30, 60];
        foreach ($expires_in_days as $expires_in_day) {
            $query_day = Carbon::now()->addDays($expires_in_day);
            $data[("expires_in_" . $expires_in_day . "_days")] = clone $data_query;
            $data[("expires_in_" . $expires_in_day . "_days")] = $data[("expires_in_" . $expires_in_day . "_days")]->whereBetween('expiry_date', [$today, ($query_day->endOfDay() . ' 23:59:59')])->count();
        }

        return $data;
    }
    public function sponsorships(
        $today,
        $start_date_of_next_month,
        $end_date_of_next_month,
        $start_date_of_this_month,
        $end_date_of_this_month,
        $start_date_of_previous_month,
        $end_date_of_previous_month,
        $start_date_of_next_week,
        $end_date_of_next_week,
        $start_date_of_this_week,
        $end_date_of_this_week,
        $start_date_of_previous_week,
        $end_date_of_previous_week,
        $all_manager_department_ids,
        $current_certificate_status
    ) {

        $data_query  = EmployeeSponsorship::whereHas("employee.departments", function ($query) use ($all_manager_department_ids) {
            $query->whereIn("departments.id", $all_manager_department_ids);
        })
            ->where([
                "current_certificate_status" => $current_certificate_status,
                "business_id" => auth()->user()->business_id
            ]);

        $data["total_data_count"] = $data_query->count();

        $data["today_data_count"] = clone $data_query;
        $data["today_data_count"] = $data["today_data_count"]->whereBetween('expiry_date', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->count();

        $data["next_week_data_count"] = clone $data_query;
        $data["next_week_data_count"] = $data["next_week_data_count"]->whereBetween('expiry_date', [$start_date_of_next_week, ($end_date_of_next_week . ' 23:59:59')])->count();

        $data["this_week_data_count"] = clone $data_query;
        $data["this_week_data_count"] = $data["this_week_data_count"]->whereBetween('expiry_date', [$start_date_of_this_week, ($end_date_of_this_week . ' 23:59:59')])->count();

        $data["previous_week_data_count"] = clone $data_query;
        $data["previous_week_data_count"] = $data["previous_week_data_count"]->whereBetween('expiry_date', [$start_date_of_previous_week, ($end_date_of_previous_week . ' 23:59:59')])->count();

        $data["next_month_data_count"] = clone $data_query;
        $data["next_month_data_count"] = $data["next_month_data_count"]->whereBetween('expiry_date', [$start_date_of_next_month, ($end_date_of_next_month . ' 23:59:59')])->count();

        $data["this_month_data_count"] = clone $data_query;
        $data["this_month_data_count"] = $data["this_month_data_count"]->whereBetween('expiry_date', [$start_date_of_this_month, ($end_date_of_this_month . ' 23:59:59')])->count();

        $data["previous_month_data_count"] = clone $data_query;
        $data["previous_month_data_count"] = $data["previous_month_data_count"]->whereBetween('expiry_date', [$start_date_of_previous_month, ($end_date_of_previous_month . ' 23:59:59')])->count();

        return $data;
    }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/business-user-dashboard",
     *      operationId="getBusinessUserDashboardData",
     *      tags={"dashboard_management.business_user"},
     *       security={
     *           {"bearerAuth": {}}
     *       },


     *      summary="get all dashboard data combined",
     *      description="get all dashboard data combined",
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

    public function getBusinessUserDashboardData(Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $business_id = auth()->user()->business_id;
            if (!$business_id) {
                return response()->json([
                    "message" => "You are not a business user"
                ], 401);
            }
            $today = today();

            $start_date_of_next_month = Carbon::now()->startOfMonth()->addMonth(1);
            $end_date_of_next_month = Carbon::now()->endOfMonth()->addMonth(1);
            $start_date_of_this_month = Carbon::now()->startOfMonth();
            $end_date_of_this_month = Carbon::now()->endOfMonth();
            $start_date_of_previous_month = Carbon::now()->startOfMonth()->subMonth(1);
            $end_date_of_previous_month = Carbon::now()->endOfMonth()->subMonth(1);

            $start_date_of_next_week = Carbon::now()->startOfWeek()->addWeek(1);
            $end_date_of_next_week = Carbon::now()->endOfWeek()->addWeek(1);
            $start_date_of_this_week = Carbon::now()->startOfWeek();
            $end_date_of_this_week = Carbon::now()->endOfWeek();
            $start_date_of_previous_week = Carbon::now()->startOfWeek()->subWeek(1);
            $end_date_of_previous_week = Carbon::now()->endOfWeek()->subWeek(1);











            // $business = Business::where([
            //     "id" => $business_id,
            //     "owner_id" => $request->user()->id
            // ])
            //     ->first();

            // if (!$business) {
            //     return response()->json([
            //         "message" => "you are not the owner of the business or the request business does not exits"
            //     ], 404);
            // }

            $dashboard_widgets =  DashboardWidget::where([
                "user_id" => auth()->user()->id
            ])
                ->get()
                ->keyBy('widget_name');

            // $data["dashboard_widgets"] = $dashboard_widgets;


            $all_manager_department_ids = [];
            $manager_departments = Department::where("manager_id", $request->user()->id)->get();
            foreach ($manager_departments as $manager_department) {
                $all_manager_department_ids[] = $manager_department->id;
                $all_manager_department_ids = array_merge($all_manager_department_ids, $manager_department->getAllDescendantIds());
            }
            $data["employees"] = $this->employees(
                $today,
                $start_date_of_next_month,
                $end_date_of_next_month,
                $start_date_of_this_month,
                $end_date_of_this_month,
                $start_date_of_previous_month,
                $end_date_of_previous_month,
                $start_date_of_next_week,
                $end_date_of_next_week,
                $start_date_of_this_week,
                $end_date_of_this_week,
                $start_date_of_previous_week,
                $end_date_of_previous_week,
                $all_manager_department_ids
            );

            $widget = $dashboard_widgets->get("employees");

            $data["employees"]["id"] = 1;
            if ($widget) {
                $data["employees"]["widget_id"] = $widget->id;
                $data["employees"]["widget_order"] = $widget->widget_order;
            } else {
                $data["employees"]["widget_id"] = 0;
                $data["employees"]["widget_order"] = 0;
            }

            $data["employees"]["widget_name"] = "employees";

            //     $data["approved_leaves"] = $this->approved_leaves(
            //         $today,
            //         $start_date_of_this_month,
            //         $end_date_of_this_month,
            //         $start_date_of_previous_month,
            //         $end_date_of_previous_month,
            //         $start_date_of_this_week,
            //         $end_date_of_this_week,
            //         $start_date_of_previous_week,
            //         $end_date_of_previous_week,
            //         $all_manager_department_ids
            // );

            $data["employee_on_holiday"] = $this->employee_on_holiday(
                $today,
                $start_date_of_next_month,
                $end_date_of_next_month,
                $start_date_of_this_month,
                $end_date_of_this_month,
                $start_date_of_previous_month,
                $end_date_of_previous_month,
                $start_date_of_next_week,
                $end_date_of_next_week,
                $start_date_of_this_week,
                $end_date_of_this_week,
                $start_date_of_previous_week,
                $end_date_of_previous_week,
                $all_manager_department_ids,

            );
            $widget = $dashboard_widgets->get("employee_on_holiday");


            $data["employee_on_holiday"]["id"] = 2;
            if ($widget) {
                $data["employee_on_holiday"]["widget_id"] = $widget->id;
                $data["employee_on_holiday"]["widget_order"] = $widget->widget_order;
            } else {
                $data["employee_on_holiday"]["widget_id"] = 0;
                $data["employee_on_holiday"]["widget_order"] = 0;
            }

            $data["employee_on_holiday"]["widget_name"] = "employee_on_holiday";


            $leave_statuses = ['pending_approval', 'progress', 'approved', 'rejected'];
            foreach ($leave_statuses as $index => $leave_status) {
                $data[($leave_status . "_leaves")] = $this->leaves(
                    $today,
                    $start_date_of_next_month,
                    $end_date_of_next_month,
                    $start_date_of_this_month,
                    $end_date_of_this_month,
                    $start_date_of_previous_month,
                    $end_date_of_previous_month,
                    $start_date_of_next_week,
                    $end_date_of_next_week,
                    $start_date_of_this_week,
                    $end_date_of_this_week,
                    $start_date_of_previous_week,
                    $end_date_of_previous_week,
                    $all_manager_department_ids,
                    $leave_status
                );
                $widget = $dashboard_widgets->get(($leave_status . "_leaves"));



                $data[($leave_status . "_leaves")]["id"] = 3 + $index;
                if ($widget) {
                    $data[($leave_status . "_leaves")]["widget_id"] = $widget->id;
                    $data[($leave_status . "_leaves")]["widget_order"] = $widget->widget_order;
                } else {
                    $data[($leave_status . "_leaves")]["widget_id"] = 0;
                    $data[($leave_status . "_leaves")]["widget_order"] = 0;
                }


                $data[($leave_status . "_leaves")]["widget_name"] = ($leave_status . "_leaves");
            }



            $data["open_roles"] = $this->open_roles(
                $today,
                $start_date_of_next_month,
                $end_date_of_next_month,
                $start_date_of_this_month,
                $end_date_of_this_month,
                $start_date_of_previous_month,
                $end_date_of_previous_month,
                $start_date_of_next_week,
                $end_date_of_next_week,
                $start_date_of_this_week,
                $end_date_of_this_week,
                $start_date_of_previous_week,
                $end_date_of_previous_week,
                $all_manager_department_ids
            );
            $widget = $dashboard_widgets->get("open_roles");


            $data["open_roles"]["id"] = 4 + $index;
            if ($widget) {
                $data["open_roles"]["widget_id"] = $widget->id;
                $data["open_roles"]["widget_order"] = $widget->widget_order;
            } else {
                $data["open_roles"]["widget_id"] = 0;
                $data["open_roles"]["widget_order"] = 0;
            }


            $data["open_roles"]["widget_name"] = "open_roles";


            $data["upcoming_passport_expiries"] = $this->upcoming_passport_expiries(
                $today,
                $start_date_of_next_month,
                $end_date_of_next_month,
                $start_date_of_this_month,
                $end_date_of_this_month,
                $start_date_of_previous_month,
                $end_date_of_previous_month,
                $start_date_of_next_week,
                $end_date_of_next_week,
                $start_date_of_this_week,
                $end_date_of_this_week,
                $start_date_of_previous_week,
                $end_date_of_previous_week,
                $all_manager_department_ids
            );
            $widget = $dashboard_widgets->get("upcoming_passport_expiries");


            $data["upcoming_passport_expiries"]["id"] = 5 + $index;
            if ($widget) {
                $data["upcoming_passport_expiries"]["widget_id"] = $widget->id;
                $data["upcoming_passport_expiries"]["widget_order"] = $widget->widget_order;
            } else {
                $data["upcoming_passport_expiries"]["widget_id"] = 0;
                $data["upcoming_passport_expiries"]["widget_order"] = 0;
            }





            $data["upcoming_passport_expiries"]["widget_name"] = "upcoming_passport_expiries";


            $data["upcoming_visa_expiries"] = $this->upcoming_visa_expiries(
                $today,
                $start_date_of_next_month,
                $end_date_of_next_month,
                $start_date_of_this_month,
                $end_date_of_this_month,
                $start_date_of_previous_month,
                $end_date_of_previous_month,
                $start_date_of_next_week,
                $end_date_of_next_week,
                $start_date_of_this_week,
                $end_date_of_this_week,
                $start_date_of_previous_week,
                $end_date_of_previous_week,
                $all_manager_department_ids
            );
            $widget = $dashboard_widgets->get("upcoming_visa_expiries");


            $data["upcoming_visa_expiries"]["id"] = 6 + $index;
            if ($widget) {
                $data["upcoming_visa_expiries"]["widget_id"] = $widget->id;
                $data["upcoming_visa_expiries"]["widget_order"] = $widget->widget_order;
            } else {
                $data["upcoming_visa_expiries"]["widget_id"] = 0;
                $data["upcoming_visa_expiries"]["widget_order"] = 0;
            }


            $data["upcoming_visa_expiries"]["widget_name"] = "upcoming_visa_expiries";





            $data["upcoming_sponsorship_expiries"] = $this->upcoming_sponsorship_expiries(
                $today,
                $start_date_of_next_month,
                $end_date_of_next_month,
                $start_date_of_this_month,
                $end_date_of_this_month,
                $start_date_of_previous_month,
                $end_date_of_previous_month,
                $start_date_of_next_week,
                $end_date_of_next_week,
                $start_date_of_this_week,
                $end_date_of_this_week,
                $start_date_of_previous_week,
                $end_date_of_previous_week,
                $all_manager_department_ids
            );
            $widget = $dashboard_widgets->get("upcoming_sponsorship_expiries");



            $data["upcoming_sponsorship_expiries"]["id"] = 7  + $index;
            if ($widget) {
                $data["upcoming_sponsorship_expiries"]["widget_id"] = $widget->id;
                $data["upcoming_sponsorship_expiries"]["widget_order"] = $widget->widget_order;
            } else {
                $data["upcoming_sponsorship_expiries"]["widget_id"] = 0;
                $data["upcoming_sponsorship_expiries"]["widget_order"] = 0;
            }



            $data["upcoming_sponsorship_expiries"]["widget_name"] = "upcoming_sponsorship_expiries";



            $sponsorship_statuses = ['unassigned', 'assigned', 'visa_applied', 'visa_rejected', 'visa_grantes', 'withdrawal'];
            foreach ($sponsorship_statuses as $index2 => $sponsorship_status) {
                $data[($sponsorship_status . "_sponsorships")] = $this->sponsorships(
                    $today,
                    $start_date_of_next_month,
                    $end_date_of_next_month,
                    $start_date_of_this_month,
                    $end_date_of_this_month,
                    $start_date_of_previous_month,
                    $end_date_of_previous_month,
                    $start_date_of_next_week,
                    $end_date_of_next_week,
                    $start_date_of_this_week,
                    $end_date_of_this_week,
                    $start_date_of_previous_week,
                    $end_date_of_previous_week,
                    $all_manager_department_ids,
                    $sponsorship_status
                );
                $widget = $dashboard_widgets->get(($sponsorship_status . "_sponsorships"));


                $data[($sponsorship_status . "_sponsorships")]["id"] = 8 + $index + $index2;
                if ($widget) {
                    $data[($sponsorship_status . "_sponsorships")]["widget_id"] = $widget->id;
                    $data[($sponsorship_status . "_sponsorships")]["widget_order"] = $widget->widget_order;
                } else {
                    $data[($sponsorship_status . "_sponsorships")]["widget_id"] = 0;
                    $data[($sponsorship_status . "_sponsorships")]["widget_order"] = 0;
                }


                $data[($sponsorship_status . "_sponsorships")]["widget_name"] = ($sponsorship_status . "_sponsorships");
            }



            return response()->json($data, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    public function total_students(
        $today,
        $start_date_of_next_month,
        $end_date_of_next_month,
        $start_date_of_this_month,
        $end_date_of_this_month,
        $start_date_of_previous_month,
        $end_date_of_previous_month,
        $start_date_of_next_week,
        $end_date_of_next_week,
        $start_date_of_this_week,
        $end_date_of_this_week,
        $start_date_of_previous_week,
        $end_date_of_previous_week,
        $business_setting,
        $is_online_student = 0
    ) {

        $data_query = Student::where("business_id", auth()->user()->business_id)
            ->when($is_online_student, function ($query) use ($business_setting) {
                $query->where(function ($query) use ($business_setting) {
                    // Check if student status ID is NULL
                    $query->whereNull("students.student_status_id")
                        // If online student status ID is set in business settings, add that to the query
                        ->when(!empty($business_setting) && !empty($business_setting->online_student_status_id), function ($query) use ($business_setting) {
                            $query->orWhere("students.student_status_id", $business_setting->online_student_status_id);
                        });
                });
            },  function ($query) use ($business_setting) {
                // When offline registration is requested, check if 'student_status_id' is NOT NULL
                $query
                    ->whereNotNull('students.student_status_id')
                    ->when(!empty($business_setting) && !empty($business_setting->online_student_status_id), function ($query) use ($business_setting) {
                        $query->whereNotIn('students.student_status_id', [$business_setting->online_student_status_id]);
                    })
                ;
            });

        $data["total_data_count"] = $data_query->count();


        $data["today_data_count"] = clone $data_query;
        $data["today_data_count"] = $data["today_data_count"]->whereBetween('created_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])->count();

        $data["next_week_data_count"] = clone $data_query;
        $data["next_week_data_count"] = $data["next_week_data_count"]->whereBetween('created_at', [$start_date_of_next_week, ($end_date_of_next_week . ' 23:59:59')])->count();

        $data["this_week_data_count"] = clone $data_query;
        $data["this_week_data_count"] = $data["this_week_data_count"]->whereBetween('created_at', [$start_date_of_this_week, ($end_date_of_this_week . ' 23:59:59')])->count();



        $data["next_month_data_count"] = clone $data_query;
        $data["next_month_data_count"] = $data["next_month_data_count"]->whereBetween('created_at', [$start_date_of_next_month, ($end_date_of_next_month . ' 23:59:59')])->count();

        $data["this_month_data_count"] = clone $data_query;
        $data["this_month_data_count"] = $data["this_month_data_count"]->whereBetween('created_at', [$start_date_of_this_month, ($end_date_of_this_month . ' 23:59:59')])->count();

        return $data;
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/business-admin-dashboard",
     *      operationId="getBusinessAdminDashboardData",
     *      tags={"dashboard_management.business_admin"},
     *       security={
     *           {"bearerAuth": {}}
     *       },


     *      summary="get all dashboard data combined",
     *      description="get all dashboard data combined",
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

    public function getBusinessAdminDashboardData(Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $business_id = auth()->user()->business_id;
            if (!$business_id) {
                return response()->json([
                    "message" => "You are not a business user"
                ], 401);
            }

            $business_setting = BusinessSetting::where([
                "business_id" => auth()->user()->business_id
            ])
                ->first();

            $today = today();

            $start_date_of_next_month = Carbon::now()->startOfMonth()->addMonth(1);
            $end_date_of_next_month = Carbon::now()->endOfMonth()->addMonth(1);
            $start_date_of_this_month = Carbon::now()->startOfMonth();
            $end_date_of_this_month = Carbon::now()->endOfMonth();
            $start_date_of_previous_month = Carbon::now()->startOfMonth()->subMonth(1);
            $end_date_of_previous_month = Carbon::now()->endOfMonth()->subMonth(1);

            $start_date_of_next_week = Carbon::now()->startOfWeek()->addWeek(1);
            $end_date_of_next_week = Carbon::now()->endOfWeek()->addWeek(1);
            $start_date_of_this_week = Carbon::now()->startOfWeek();
            $end_date_of_this_week = Carbon::now()->endOfWeek();
            $start_date_of_previous_week = Carbon::now()->startOfWeek()->subWeek(1);
            $end_date_of_previous_week = Carbon::now()->endOfWeek()->subWeek(1);

            $data = [];

            $data["total_students"] = $this->total_students(
                $today,
                $start_date_of_next_month,
                $end_date_of_next_month,
                $start_date_of_this_month,
                $end_date_of_this_month,
                $start_date_of_previous_month,
                $end_date_of_previous_month,
                $start_date_of_next_week,
                $end_date_of_next_week,
                $start_date_of_this_week,
                $end_date_of_this_week,
                $start_date_of_previous_week,
                $end_date_of_previous_week,
                $business_setting

            );

            $data["online_students"] = $this->total_students(
                $today,
                $start_date_of_next_month,
                $end_date_of_next_month,
                $start_date_of_this_month,
                $end_date_of_this_month,
                $start_date_of_previous_month,
                $end_date_of_previous_month,
                $start_date_of_next_week,
                $end_date_of_next_week,
                $start_date_of_this_week,
                $end_date_of_this_week,
                $start_date_of_previous_week,
                $end_date_of_previous_week,
                $business_setting,
                1

            );


            // Total counts
            $data["total_awarding_bodies"]["total_data_count"] = AwardingBody::where("business_id", auth()->user()->business_id)->count();

            $data["total_courses"] = CourseTitle::where("business_id", auth()->user()->business_id)
                ->count();




            $expiryIntervals = [30, 60, 90];
            $previousDays = 0;

            foreach ($expiryIntervals as $days) {
                $data["total_awarding_bodies"]["awarding_body_expiry_in_{$days}_days"] = AwardingBody::where("business_id", auth()->user()->business_id)
                    ->whereDate("accreditation_start_date", ">", Carbon::now()->addDays($previousDays))
                    ->whereDate("accreditation_start_date", "<=", Carbon::now()->addDays($days))
                    ->count();

                $previousDays = $days;
            }



            return response()->json($data, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }






    /**
     *
     * @OA\Post(
     *      path="/v1.0/dashboard-widgets",
     *      operationId="createDashboardWidget",
     *      tags={"unused_apis"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store dashboard widgets",
     *      description="This method is to store dashboard widgets",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *
     *
     *     @OA\Property(property="widgets", type="string", format="array", example={
     *    {"id":1,
     *    "widget_name":"passport",
     *    "widget_order":1}
     * }),
     *
     *
     *
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

    public function createDashboardWidget(WidgetCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {

                $request_data = $request->validated();

                foreach ($request_data["widgets"] as $widget) {
                    $widget["user_id"] = auth()->user()->id;

                    DashboardWidget::updateOrCreate(
                        [
                            "widget_name" => $widget["widget_name"],
                            "user_id" => $widget["user_id"],
                        ],
                        $widget
                    );
                }

                return response(["ok" => true], 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/dashboard-widgets/{ids}",
     *      operationId="deleteDashboardWidgetsByIds",
     *      tags={"unused_apis"},
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
     *      summary="This method is to delete widget by id",
     *      description="This method is to delete widget by id",
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

    public function deleteDashboardWidgetsByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $idsArray = explode(',', $ids);
            $existingIds = DashboardWidget::where([
                "user_id" => auth()->user()->id
            ])
                ->whereIn('id', $idsArray)
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            $nonExistingIds = array_diff($idsArray, $existingIds);

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
            DashboardWidget::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }




















    public function businesses($created_by_filter = 0)
    {
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);



        $total_data_count_query = new Student();
        if ($created_by_filter) {
            $total_data_count_query =  $total_data_count_query->where([
                "created_by" => auth()->user()->id
            ]);
        }

        $data["total_data_count"] = $total_data_count_query->count();



        $this_week_data_query = Business::whereBetween('created_at', [$startDateOfThisWeek, $endDateOfThisWeek]);

        if ($created_by_filter) {
            $this_week_data_query =  $this_week_data_query->where([
                "created_by" => auth()->user()->id
            ]);
        }
        $data["this_week_data"] = $this_week_data_query->select("id", "created_at", "updated_at")->get();




        $previous_week_data_query = Business::whereBetween('created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek]);

        if ($created_by_filter) {
            $previous_week_data_query =  $previous_week_data_query->where([
                "created_by" => auth()->user()->id
            ]);
        }

        $data["previous_week_data"] = $total_data_count_query->select("id", "created_at", "updated_at")->get();




        $this_month_data_query = Business::whereBetween('created_at', [$startDateOfThisMonth, $endDateOfThisMonth]);

        if ($created_by_filter) {
            $this_month_data_query =  $this_month_data_query->where([
                "created_by" => auth()->user()->id
            ]);
        }
        $data["this_month_data"] = $this_month_data_query->select("id", "created_at", "updated_at")->get();




        $previous_month_data_query = Business::whereBetween('created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth]);

        if ($created_by_filter) {
            $previous_month_data_query =  $previous_month_data_query->where([
                "created_by" => auth()->user()->id
            ]);
        }
        $data["previous_month_data"] = $previous_month_data_query->select("id", "created_at", "updated_at")->get();



        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["previous_week_data_count"] = $data["previous_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["previous_month_data_count"] = $data["previous_month_data"]->count();
        return $data;
    }
    public function fuel_stations($created_by_filter = 0)
    {
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);


        $total_data_count_query = new Student();
        if ($created_by_filter) {
            $total_data_count_query =  $total_data_count_query->where([
                "created_by" => auth()->user()->id
            ]);
        }
        $data["total_data_count"] = $total_data_count_query->count();


        $this_week_data_query = Student::whereBetween('created_at', [$startDateOfThisWeek, $endDateOfThisWeek]);
        if ($created_by_filter) {
            $this_week_data_query =  $this_week_data_query->where([
                "created_by" => auth()->user()->id
            ]);
        }
        $data["this_week_data"] = $this_week_data_query->select("id", "created_at", "updated_at")
            ->get();


        $previous_week_data_query = Student::whereBetween('created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek]);
        if ($created_by_filter) {
            $previous_week_data_query =  $previous_week_data_query->where([
                "created_by" => auth()->user()->id
            ]);
        }
        $data["previous_week_data"] = $previous_week_data_query->select("id", "created_at", "updated_at")
            ->get();


        $this_month_data_query =  Student::whereBetween('created_at', [$startDateOfThisMonth, $endDateOfThisMonth]);
        if ($created_by_filter) {
            $this_month_data_query =  $this_month_data_query->where([
                "created_by" => auth()->user()->id
            ]);
        }
        $data["this_month_data"] = $this_month_data_query->select("id", "created_at", "updated_at")
            ->get();

        $previous_month_data_query =  Student::whereBetween('created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth]);
        if ($created_by_filter) {
            $previous_month_data_query =  $previous_month_data_query->where([
                "created_by" => auth()->user()->id
            ]);
        }
        $data["previous_month_data"] = $previous_month_data_query->select("id", "created_at", "updated_at")
            ->get();




        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["previous_week_data_count"] = $data["previous_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["previous_month_data_count"] = $data["previous_month_data"]->count();
        return $data;
    }

    public function customers()
    {
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);



        $data["total_data_count"] = User::with("roles")->whereHas("roles", function ($q) {
            $q->whereIn("name", ["customer"]);
        })->count();


        $data["this_week_data"] = User::with("roles")->whereHas("roles", function ($q) {
            $q->whereIn("name", ["customer"]);
        })->whereBetween('created_at', [$startDateOfThisWeek, $endDateOfThisWeek])
            ->select("id", "created_at", "updated_at")
            ->get();

        $data["previous_week_data"] = User::with("roles")->whereHas("roles", function ($q) {
            $q->whereIn("name", ["customer"]);
        })->whereBetween('created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek])
            ->select("id", "created_at", "updated_at")
            ->get();



        $data["this_month_data"] = User::with("roles")->whereHas("roles", function ($q) {
            $q->whereIn("name", ["customer"]);
        })->whereBetween('created_at', [$startDateOfThisMonth, $endDateOfThisMonth])
            ->select("id", "created_at", "updated_at")
            ->get();
        $data["previous_month_data"] = User::with("roles")->whereHas("roles", function ($q) {
            $q->whereIn("name", ["customer"]);
        })->whereBetween('created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth])
            ->select("id", "created_at", "updated_at")
            ->get();

        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["previous_week_data_count"] = $data["previous_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["previous_month_data_count"] = $data["previous_month_data"]->count();
        return $data;
    }
    public function overall_customer_jobs()
    {
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);



        $data["total_data_count"] = Student::count();


        $data["this_week_data"] = Student::whereBetween('created_at', [$startDateOfThisWeek, $endDateOfThisWeek])
            ->select("id", "created_at", "updated_at")
            ->get();

        $data["previous_week_data"] = Student::whereBetween('created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek])
            ->select("id", "created_at", "updated_at")
            ->get();



        $data["this_month_data"] = Student::whereBetween('created_at', [$startDateOfThisMonth, $endDateOfThisMonth])
            ->select("id", "created_at", "updated_at")
            ->get();

        $data["previous_month_data"] = Student::whereBetween('created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth])
            ->select("id", "created_at", "updated_at")
            ->get();

        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["previous_week_data_count"] = $data["previous_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["previous_month_data_count"] = $data["previous_month_data"]->count();
        return $data;
    }

    public function overall_bookings($created_by_filter = 0)
    {
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);


        $total_data_count_query =  Student::leftJoin('businesses', 'businesses.id', '=', 'bookings.business_id');
        if ($created_by_filter) {
            $total_data_count_query =  $total_data_count_query->where([
                "businesses.created_by" => auth()->user()->id
            ]);
        }
        $data["total_data_count"] = $total_data_count_query->count();



        $this_week_data_query =  Student::leftJoin('businesses', 'businesses.id', '=', 'bookings.business_id')
            ->whereBetween('bookings.created_at', [$startDateOfThisWeek, $endDateOfThisWeek]);
        if ($created_by_filter) {
            $this_week_data_query =  $this_week_data_query->where([
                "businesses.created_by" => auth()->user()->id
            ]);
        }
        $data["this_week_data"] = $this_week_data_query->select("bookings.id", "bookings.created_at", "bookings.updated_at")
            ->get();




        $previous_week_data_query =  Student::leftJoin('businesses', 'businesses.id', '=', 'bookings.business_id')
            ->whereBetween('bookings.created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek]);
        if ($created_by_filter) {
            $previous_week_data_query =  $previous_week_data_query->where([
                "businesses.created_by" => auth()->user()->id
            ]);
        }
        $data["previous_week_data"] = $previous_week_data_query->select("bookings.id", "bookings.created_at", "bookings.updated_at")
            ->get();






        $this_month_data_query =  Student::leftJoin('businesses', 'businesses.id', '=', 'bookings.business_id')
            ->whereBetween('bookings.created_at', [$startDateOfThisMonth, $endDateOfThisMonth]);
        if ($created_by_filter) {
            $this_month_data_query =  $this_month_data_query->where([
                "businesses.created_by" => auth()->user()->id
            ]);
        }
        $data["this_month_data"] = $this_month_data_query->select("bookings.id", "bookings.created_at", "bookings.updated_at")
            ->get();


        $previous_month_data_query =  Student::leftJoin('businesses', 'businesses.id', '=', 'bookings.business_id')
            ->whereBetween('bookings.created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth]);
        if ($created_by_filter) {
            $previous_month_data_query =  $previous_month_data_query->where([
                "businesses.created_by" => auth()->user()->id
            ]);
        }
        $data["previous_month_data"] = $previous_month_data_query->select("bookings.id", "bookings.created_at", "bookings.updated_at")
            ->get();


        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["previous_week_data_count"] = $data["previous_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["previous_month_data_count"] = $data["previous_month_data"]->count();
        return $data;
    }

    public function overall_jobs($created_by_filter = 0)
    {
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);


        $total_data_count_query =  Student::leftJoin('businesses', 'businesses.id', '=', 'jobs.business_id');
        if ($created_by_filter) {
            $total_data_count_query =  $total_data_count_query->where([
                "businesses.created_by" => auth()->user()->id
            ]);
        }
        $data["total_data_count"] = $total_data_count_query->count();





        $this_week_data_query =  Student::leftJoin('businesses', 'businesses.id', '=', 'jobs.business_id')
            ->whereBetween('jobs.created_at', [$startDateOfThisWeek, $endDateOfThisWeek]);
        if ($created_by_filter) {
            $this_week_data_query =  $this_week_data_query->where([
                "businesses.created_by" => auth()->user()->id
            ]);
        }
        $data["this_week_data"] = $this_week_data_query
            ->select("jobs.id", "jobs.created_at", "jobs.updated_at")
            ->get();




        $previous_week_data_query =  Student::leftJoin('businesses', 'businesses.id', '=', 'jobs.business_id')
            ->whereBetween('jobs.created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek]);
        if ($created_by_filter) {
            $previous_week_data_query =  $previous_week_data_query->where([
                "businesses.created_by" => auth()->user()->id
            ]);
        }
        $data["previous_week_data"] = $previous_week_data_query
            ->select("jobs.id", "jobs.created_at", "jobs.updated_at")
            ->get();





        $this_month_data_query =  Student::leftJoin('businesses', 'businesses.id', '=', 'jobs.business_id')
            ->whereBetween('jobs.created_at', [$startDateOfThisMonth, $endDateOfThisMonth]);
        if ($created_by_filter) {
            $this_month_data_query =  $this_month_data_query->where([
                "businesses.created_by" => auth()->user()->id
            ]);
        }
        $data["this_month_data"] = $this_month_data_query
            ->select("jobs.id", "jobs.created_at", "jobs.updated_at")
            ->get();



        $previous_month_data_query =  Student::leftJoin('businesses', 'businesses.id', '=', 'jobs.business_id')
            ->whereBetween('jobs.created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth]);
        if ($created_by_filter) {
            $previous_month_data_query =  $previous_month_data_query->where([
                "businesses.created_by" => auth()->user()->id
            ]);
        }
        $data["previous_month_data"] = $previous_month_data_query
            ->select("jobs.id", "jobs.created_at", "jobs.updated_at")
            ->get();



        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["previous_week_data_count"] = $data["previous_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["previous_month_data_count"] = $data["previous_month_data"]->count();
        return $data;
    }



    public function overall_services()
    {
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);



        $data["total_data_count"] = Student::count();


        $data["this_week_data"] = Student::whereBetween('created_at', [$startDateOfThisWeek, $endDateOfThisWeek])
            ->select("id", "created_at", "updated_at")
            ->get();

        $data["previous_week_data"] = Student::whereBetween('created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek])
            ->select("id", "created_at", "updated_at")
            ->get();



        $data["this_month_data"] = Student::whereBetween('created_at', [$startDateOfThisMonth, $endDateOfThisMonth])
            ->select("id", "created_at", "updated_at")
            ->get();
        $data["previous_month_data"] = Student::whereBetween('created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth])
            ->select("id", "created_at", "updated_at")
            ->get();

        $data["this_week_data_count"] = $data["this_week_data"]->count();
        $data["previous_week_data_count"] = $data["previous_week_data"]->count();
        $data["this_month_data_count"] = $data["this_month_data"]->count();
        $data["previous_month_data_count"] = $data["previous_month_data"]->count();
        return $data;
    }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/superadmin-dashboard",
     *      operationId="getSuperAdminDashboardData",
     *      tags={"dashboard_management.superadmin"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="get all dashboard data combined",
     *      description="get all dashboard data combined",
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

    public function getSuperAdminDashboardData(Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasRole('superadmin')) {
                return response()->json([
                    "message" => "You are not a superadmin"
                ], 401);
            }

            $data = [];

            $data["total_businesses"] = Business::count();
            $data["active_businesses"] = Business::where("businesses.is_active", 1)
                ->count();

            //    $data["inactive_businesses"] = Business::
            //    where("businesses.is_active",0)
            //  ->count();

            $data["inactive_businesses"] = $data["total_businesses"] - $data["active_businesses"];


            // For this week (from Sunday to Saturday)
            $data["this_week_businesses"] = Business::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count();

            // For last week
            $data["last_week_businesses"] = Business::whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
                ->count();

            // For this month
            $data["this_month_businesses"] = Business::whereMonth('created_at', Carbon::now()->month)
                ->count();

            // For last month
            $data["last_month_businesses"] = Business::whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->count();



            return response()->json($data, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/data-collector-dashboard",
     *      operationId="getDataCollectorDashboardData",
     *      tags={"dashboard_management.data_collector"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="get all dashboard data combined",
     *      description="get all dashboard data combined",
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

    public function getDataCollectorDashboardData(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasRole('data_collector')) {
                return response()->json([
                    "message" => "You are not a superadmin"
                ], 401);
            }

            $data["businesses"] = $this->businesses(1);

            $data["fuel_stations"] = $this->fuel_stations(1);

            $data["overall_bookings"] = $this->overall_bookings(1);

            $data["overall_jobs"] = $this->overall_jobs(1);

            //  $data["customers"] = $this->customers();

            //  $data["overall_customer_jobs"] = $this->overall_customer_jobs();



            //  $data["overall_services"] = $this->overall_services();






            return response()->json($data, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }
}
