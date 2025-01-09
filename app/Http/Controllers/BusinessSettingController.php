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

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasRole('business_admin')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();
                $request_data["business_id"] = auth()->user()->business_id;


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


                return response($business_setting, 201);
            });
        } catch (Exception $e) {
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

    public function getBusinessSetting(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasRole('business_admin')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }




            $business_setting = BusinessSetting::
            where('business_settings.business_id', auth()->user()->business_id)
                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                        $query->where("business_settings.name", "like", "%" . $term . "%");
                    });
                })

                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('business_settings.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('business_settings.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("business_settings.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("business_settings.id", "DESC");
                })
                ->when(!empty($request->per_page), function ($query) use ($request) {
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    return $query->get();
                });;



            return response()->json($business_setting, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }




















}
