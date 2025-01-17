<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRegisterBusinessRequest;
use App\Http\Requests\BusinessCreateRequest;

use App\Http\Requests\BusinessUpdateRequest;
use App\Http\Requests\BusinessUpdateSeparateRequest;
use App\Http\Requests\CheckScheduleConflictRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\MultipleImageUploadRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\SendPassword;

use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\BusinessTime;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class BusinessController extends Controller
{
    use ErrorUtil, BusinessUtil, UserActivityUtil, BasicUtil;
    /**
     *
     * @OA\Post(
     *      path="/v1.0/business-image",
     *      operationId="createBusinessImage",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store business image ",
     *      description="This method is to store business image",
     *
     *  @OA\RequestBody(
     *   * @OA\MediaType(
     *     mediaType="multipart/form-data",
     *     @OA\Schema(
     *         required={"image"},
     *         @OA\Property(
     *             description="image to upload",
     *             property="image",
     *             type="file",
     *             collectionFormat="multi",
     *         )
     *     )
     * )



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

    public function createBusinessImage(ImageUploadRequest $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            // if(!$request->user()->hasPermissionTo('business_create')){
            //      return response()->json([
            //         "message" => "You can not perform this action"
            //      ],401);
            // }

            $request_data = $request->validated();

            $location =  config("setup-config.business_gallery_location");

            $new_file_name = time() . '_' . str_replace(' ', '_', $request_data["image"]->getClientOriginalName());

            $request_data["image"]->move(public_path($location), $new_file_name);


            return response()->json(["image" => $new_file_name, "location" => $location, "full_location" => ("/" . $location . "/" . $new_file_name)], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/v1.0/business-logo",
     *      operationId="createBusinessLogo",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store business image ",
     *      description="This method is to store business image",
     *
     *  @OA\RequestBody(
     *   * @OA\MediaType(
     *     mediaType="multipart/form-data",
     *     @OA\Schema(
     *         required={"image"},
     *         @OA\Property(
     *             description="image to upload",
     *             property="image",
     *             type="file",
     *             collectionFormat="multi",
     *         )
     *     )
     * )



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

    public function createBusinessLogo(ImageUploadRequest $request)
    {
        try {
            // Log the activity
            $this->storeActivity($request, "Upload Business Logo", "Uploading a new business logo");

            // Validate the request data
            $request_data = $request->validated();


            // Get the authenticated user's business
            $business = auth()->user()->business;

            if (!$business) {
                return response()->json([
                    "message" => "Business not found"
                ], 404);
            }

            // Define the storage location and file name
            $location = str_replace(' ', '_', $business->name) . "/" . config("setup-config.business_gallery_location");


            $new_file_name = time() . '_' . str_replace(' ', '_', $request_data["image"]->getClientOriginalName());

            $request_data["image"]->move(public_path($location), $new_file_name);



            $new_logo_path = ("/" . $location . "/" . $new_file_name);



            // Compare existing and new logo paths and delete the old logo if needed
            if (!empty($business->logo) && $business->logo !== $new_file_name) {


                    $existingLogoPath = public_path($this->getUrlLink($business,"logo",config("setup-config.business_gallery_location"))["logo"],$business->name);



                if (File::exists($existingLogoPath)) {
                    File::delete($existingLogoPath);
                }



            }


            // Update the business logo
            $business->logo = $new_file_name;
            $business->save();

            // Return a success response
            return response()->json([
                "status" => "success",
                "data" => [
                    "image" => $new_file_name,
                    "location" => $location,
                    "full_location" => $new_logo_path


                ]
            ], 200);
        } catch (Exception $e) {
            // Log the error
            error_log($e->getMessage());

            // Return an error response
            return response()->json([
                "message" => "An error occurred while uploading the business logo",
                "error" => $e->getMessage()
            ], 500);
        }
    }




    /**
     *
     * @OA\Post(
     *      path="/v1.0/business-image-multiple",
     *      operationId="createBusinessImageMultiple",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="This method is to store business gallery",
     *      description="This method is to store business gallery",
     *
     *  @OA\RequestBody(
     *   * @OA\MediaType(
     *     mediaType="multipart/form-data",
     *     @OA\Schema(
     *         required={"images[]"},
     *         @OA\Property(
     *             description="array of images to upload",
     *             property="images[]",
     *             type="array",
     *             @OA\Items(
     *                 type="file"
     *             ),
     *             collectionFormat="multi",
     *         )
     *     )
     * )



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

    public function createBusinessImageMultiple(MultipleImageUploadRequest $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $request_data = $request->validated();

            $location =  config("setup-config.business_gallery_location");

            $images = [];
            if (!empty($request_data["images"])) {
                foreach ($request_data["images"] as $image) {
                    $new_file_name = time() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
                    $image->move(public_path($location), $new_file_name);

                    array_push($images, ("/" . $location . "/" . $new_file_name));
                }
            }


            return response()->json(["images" => $images], 201);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Post(
     *      path="/v1.0/businesses",
     *      operationId="createBusiness",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store business",
     *      description="This method is to store  business",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","business"},

     *
     *  @OA\Property(property="business", type="string", format="array",example={
     *  "owner_id":"1",
     * "name":"ABCD businesses",
     * "about":"Best businesses in Dhaka",
     * "color_theme_name":"#dddddd",
     * "web_page":"https://www.facebook.com/",
     *  "phone":"01771034383",
     *  "email":"rifatalashwad@gmail.com",
     *  "phone":"01771034383",
     *  "additional_information":"No Additional Information",
     *  "address_line_1":"Dhaka",
     *  "address_line_2":"Dinajpur",
     *    * *  "lat":"23.704263332849386",
     *    * *  "long":"90.44707059805279",
     *
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  * "currency":"BDT",
     *  "postcode":"Dinajpur",
     *
     *  "logo":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",

     *  *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *  "images":{"/a","/b","/c"}
     *
     * }),
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
    public function createBusiness(BusinessCreateRequest $request)
    {
        // this is business create by super admin
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return  DB::transaction(function () use (&$request) {

                if (!$request->user()->hasPermissionTo('business_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $request_data = $request->validated();



                $user = User::where([
                    "id" =>  $request_data['business']['owner_id']
                ])
                    ->first();

                if (!$user) {
                    $error =  [
                        "message" => "The given data was invalid.",
                        "errors" => ["owner_id" => ["No User Found"]]
                    ];
                    throw new Exception(json_encode($error), 422);
                }

                if (!$user->hasRole('business_admin')) {
                    $error =  [
                        "message" => "The given data was invalid.",
                        "errors" => ["owner_id" => ["The user is not a businesses Owner"]]
                    ];
                    throw new Exception(json_encode($error), 422);
                }



                $request_data['business']['status'] = "pending";

                $request_data['business']['created_by'] = $request->user()->id;
                $request_data['business']['is_active'] = true;
                $business =  Business::create($request_data['business']);


                BusinessSetting::create([
                    'business_id' => $business->id,
                    'online_student_status_id' => NULL,
                    'student_data_fields' => config("setup-config.student_data_fields"),
                    'student_verification_fields' => config("setup-config.student_verification_fields")
                ]);












                return response([

                    "business" => $business
                ], 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/v1.0/businesses/generate-database",
     *      operationId="generateDatabaseForBusiness",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store business",
     *      description="This method is to store  business",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","business"},

     *
     *  @OA\Property(property="business_id", type="string", format="array",example="1"),
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
    public function generateDatabaseForBusiness(Request $request)
    {
        // this is business create by super admin
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('business_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $request->validate([
                'business_id' => 'required|integer|exists:businesses,id', // Ensure business_id is required, is an integer, and exists in the businesses table
            ]);


            Log::info("test1..");
            if (env("SELF_DB") == true) {
                Log::info("test");
                Artisan::call(('generate:database ' . $request->business_id));
            }


            return response([
                "ok" => true
            ], 201);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     * @OA\Post(
     *      path="/v1.0/auth/check-schedule-conflict",
     *      operationId="checkScheduleConflict",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store user with business",
     *      description="This method is to store user with business",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","business"},
     *
     *      @OA\Property(property="times", type="string", format="array",example={
     *
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
    public function checkScheduleConflict(CheckScheduleConflictRequest $request)
    {


        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return  DB::transaction(function () use (&$request) {

                //     if(!$request->user()->hasPermissionTo('business_create')){
                //         return response()->json([
                //            "message" => "You can not perform this action"
                //         ],401);
                //    }
                $request_data = $request->validated();

                $conflicted_work_shift_ids = collect();

                $timesArray = collect($request_data["times"])->unique("day");
                foreach ($timesArray as $business_time) {
                    $work_shift_ids = WorkShift::where([
                        "business_id" => auth()->user()->business_id
                    ])
                        ->whereHas('details', function ($query) use ($business_time) {
                            $query->where('work_shift_details.day', ($business_time["day"]))
                                ->when(!empty($time["is_weekend"]), function ($query) {
                                    $query->where('work_shift_details.is_weekend', 1);
                                })
                                ->where(function ($query) use ($business_time) {
                                    $query->whereTime('work_shift_details.start_at', '<=', ($business_time["start_at"]))
                                        ->orWhereTime('work_shift_details.end_at', '>=', ($business_time["end_at"]));
                                });
                        })
                        ->pluck("id");
                    $conflicted_work_shift_ids = $conflicted_work_shift_ids->merge($work_shift_ids);
                }
                $conflicted_work_shift_ids = $conflicted_work_shift_ids->unique()->values()->all();

                $conflicted_work_shifts =   WorkShift::whereIn("id", $conflicted_work_shift_ids)->get();

                return response([
                    "conflicted_work_shifts" => $conflicted_work_shifts
                ], 200);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }







    /**
     *
     * @OA\Post(
     *      path="/v1.0/auth/register-with-business",
     *      operationId="registerUserWithBusiness",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store user with business",
     *      description="This method is to store user with business",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","business"},
     *             @OA\Property(property="user", type="string", format="array",example={
     * "first_Name":"Rifat",
     * "last_Name":"Al-Ashwad",
     * "middle_Name":"Al-Ashwad",
     * "email":"rifatalashwad@gmail.com",
     *  "password":"12345678",
     *  "password_confirmation":"12345678",
     *  "phone":"01771034383",
     *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     * "send_password":1,
     * "gender":"male"
     *
     *
     * }),
     *
     *   *      *    @OA\Property(property="times", type="string", format="array",example={
     *
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
     *
     *  @OA\Property(property="business", type="string", format="array",example={
     * "name":"ABCD businesses",
     * "about":"Best businesses in Dhaka",
     *
     * "color_theme_name":"#dddddd",
     * "web_page":"https://www.facebook.com/",
     *  "phone":"01771034383",
     *  "email":"rifatalashwad@gmail.com",
     *  "phone":"01771034383",
     *  "additional_information":"No Additional Information",
     *  "address_line_1":"Dhaka",
     *  "address_line_2":"Dinajpur",
     *    * *  "lat":"23.704263332849386",
     *    * *  "long":"90.44707059805279",
     *
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  * "currency":"BDT",
     *  "postcode":"Dinajpur",
     *
     *  "logo":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",

     *  *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *  "images":{"/a","/b","/c"}
     *
     * }),
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
    public function registerUserWithBusiness(AuthRegisterBusinessRequest $request)
    {

        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            if (!$request->user()->hasPermissionTo('business_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $request_data = $request->validated();



            // user info starts ##############

            $password = $request_data['user']['password'];
            $request_data['user']['password'] = Hash::make($password);


            //    if(!$request->user()->hasRole('superadmin') || empty($request_data['user']['password'])) {
            //     $password = Str::random(10);
            //     $request_data['user']['password'] = Hash::make($password);
            //     }




            $request_data['user']['remember_token'] = Str::random(10);
            $request_data['user']['is_active'] = true;
            $request_data['user']['created_by'] = $request->user()->id;

            $request_data['user']['address_line_1'] = $request_data['business']['address_line_1'];
            $request_data['user']['address_line_2'] = (!empty($request_data['business']['address_line_2']) ? $request_data['business']['address_line_2'] : "");
            $request_data['user']['country'] = $request_data['business']['country'];
            $request_data['user']['city'] = $request_data['business']['city'];
            $request_data['user']['postcode'] = $request_data['business']['postcode'] ?? null;
            $request_data['user']['lat'] = $request_data['business']['lat'];
            $request_data['user']['long'] = $request_data['business']['long'];

            $user =  User::create($request_data['user']);

            $user->assignRole('business_admin');
            // end user info ##############


            //  business info ##############

            $request_data['business']['status'] = "pending";
            $request_data['business']['owner_id'] = $user->id;
            $request_data['business']['created_by'] = $request->user()->id;
            $request_data['business']['is_active'] = true;
            $business =  Business::create($request_data['business']);





            $user->email_verified_at = now();
            $user->business_id = $business->id;



            $token = Str::random(30);
            $user->resetPasswordToken = $token;
            $user->resetPasswordExpires = Carbon::now()->subDays(-1);

            $user->save();



            BusinessTime::where([
                "business_id" => $business->id
            ])
                ->delete();
            $timesArray = collect($request_data["times"])->unique("day");
            foreach ($timesArray as $business_time) {
                BusinessTime::create([
                    "business_id" => $business->id,
                    "day" => $business_time["day"],
                    "start_at" => $business_time["start_at"],
                    "end_at" => $business_time["end_at"],
                    "is_weekend" => $business_time["is_weekend"],
                ]);
            }


            $defaultRoles = Role::where([
                "business_id" => NULL,
                "is_default" => 1,
                "is_default_for_business" => 1,
                "guard_name" => "api",
            ])->get();

            foreach ($defaultRoles as $defaultRole) {
                $insertableData = [
                    'name'  => ($defaultRole->name . "#" . $business->id),
                    "is_default" => 1,
                    "business_id" => $business->id,
                    "is_default_for_business" => 0,
                    "guard_name" => "api",
                ];
                $role  = Role::create($insertableData);

                $permissions = $defaultRole->permissions;
                foreach ($permissions as $permission) {
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }


            BusinessSetting::create([
                'business_id' => $business->id,
                'online_student_status_id' => NULL,
                'student_data_fields' => config("setup-config.student_data_fields"),
                'student_verification_fields' => config("setup-config.student_verification_fields")
            ]);





            // end business info ##############


            //  if($request_data['user']['send_password']) {
            if (env("SEND_EMAIL") == true) {
                Mail::to($request_data['user']['email'])->send(new SendPassword($user, $password));
            }
            if (env("SELF_DB") == true) {
                Artisan::call(('generate:database ' . $business->id));
            }
            Log::info("Business created");

            // }


            return response()->json([
                "user" => $user,
                "business" => $business
            ], 201);
        } catch (Exception $e) {


            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     * @OA\Put(
     *      path="/v1.0/businesses",
     *      operationId="updateBusiness",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update user with business",
     *      description="This method is to update user with business",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","business"},
     *             @OA\Property(property="user", type="string", format="array",example={
     *  * "id":1,
     * "first_Name":"Rifat",
     *  * "middle_Name":"Al-Ashwad",
     *
     * "last_Name":"Al-Ashwad",
     * "email":"rifatalashwad@gmail.com",
     *  "password":"12345678",
     *  "password_confirmation":"12345678",
     *  "phone":"01771034383",
     *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     * "gender":"male"
     *
     *
     * }),
     *
     *  @OA\Property(property="business", type="string", format="array",example={
     *   *  * "id":1,
     * "name":"ABCD businesses",
     * "about":"Best businesses in Dhaka",
     * "web_page":"https://www.facebook.com/",
     *
     *  "color_theme_name":"#dddddd",
     *
     *  "phone":"01771034383",
     *  "email":"rifatalashwad@gmail.com",
     *  "phone":"01771034383",
     *  "additional_information":"No Additional Information",
     *  "address_line_1":"Dhaka",
     *  "address_line_2":"Dinajpur",
     *    * *  "lat":"23.704263332849386",
     *    * *  "long":"90.44707059805279",
     *
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  "postcode":"Dinajpur",
     *
     *  "logo":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *      *  *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *  "images":{"/a","/b","/c"},
     *  "currency":"BDT",
     *  "letter_template_header":"letter_template_header",
     *  "letter_template_footer":"letter_template_footer"
     * }),

     *       ),
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
    public function updateBusiness(BusinessUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return  DB::transaction(function () use (&$request) {
                if (!$request->user()->hasPermissionTo('business_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                if (!$this->businessOwnerCheck($request["business"]["id"])) {
                    return response()->json([
                        "message" => "you are not the owner of the business or the requested business does not exist."
                    ], 401);
                }

                $request_data = $request->validated();
                //    user email check
                $userPrev = User::where([
                    "id" => $request_data["user"]["id"]
                ]);
                if (!$request->user()->hasRole('superadmin')) {
                    $userPrev  = $userPrev->where(function ($query) {
                        return  $query->where('created_by', auth()->user()->id)
                            ->orWhere('id', auth()->user()->id);
                    });
                }
                $userPrev = $userPrev->first();
                if (!$userPrev) {
                    $this->storeError(
                        "no data found",
                        404,
                        "front end error",
                        "front end error"
                    );
                    return response()->json([
                        "message" => "no user found with this id"
                    ], 404);
                }




                //  $businessPrev = Business::where([
                //     "id" => $request_data["business"]["id"]
                //  ]);

                // $businessPrev = $businessPrev->first();
                // if(!$businessPrev) {
                //     return response()->json([
                //        "message" => "no business found with this id"
                //     ],404);
                //   }

                if (!empty($request_data['user']['password'])) {
                    $request_data['user']['password'] = Hash::make($request_data['user']['password']);
                } else {
                    unset($request_data['user']['password']);
                }
                $request_data['user']['is_active'] = true;
                $request_data['user']['remember_token'] = Str::random(10);
                $request_data['user']['address_line_1'] = $request_data['business']['address_line_1'] ?? null;
                $request_data['user']['address_line_2'] = $request_data['business']['address_line_2'] ?? null;

                $request_data['user']['country'] = $request_data['business']['country'] ?? null;
                $request_data['user']['city'] = $request_data['business']['city'] ?? null;
                $request_data['user']['postcode'] = $request_data['business']['postcode'] ?? null;
                $request_data['user']['lat'] = $request_data['business']['lat'] ?? null;
                $request_data['user']['long'] = $request_data['business']['long'] ?? null;
                $user  =  tap(User::where([
                    "id" => $request_data['user']["id"]
                ]))->update(
                    collect($request_data['user'])->only([
                        'first_Name',
                        'middle_Name',
                        'last_Name',
                        'phone',
                        'image',
                        'address_line_1',
                        'address_line_2',
                        'country',
                        'city',
                        'postcode',
                        'email',
                        'password',
                        "lat",
                        "long",
                        "gender"
                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();
                if (!$user) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }

                // $user->syncRoles(["business_admin"]);



                //  business info ##############
                // $request_data['business']['status'] = "pending";



                $business = Business::where(["id" => $request_data['business']["id"]])
                    ->first();

                if (!$business) {
                    return response()->json([
                        "message" => "Business not found"
                    ], 404);
                }

                if ($business->name != $request_data['business']["name"]) {
                    $this->renameOrCreateFolder(str_replace(' ', '_', $business->name), str_replace(' ', '_', $request_data['business']["name"]));
                }



                $business->fill(collect($request_data['business'])->only([
                    "url",
                    "name",
                    "about",
                    "web_page",
                    "color_theme_name",
                    "phone",
                    "email",
                    "additional_information",
                    "address_line_1",
                    "address_line_2",
                    "lat",
                    "long",
                    "country",
                    "city",
                    "postcode",
                    "logo",
                    "image",
                    "status",
                    "background_image",
                    "currency",
                    "letter_template_header",
                    "letter_template_footer"
                ])->toArray());

                $business->save();



                // end business info ##############

                if (!empty($request_data["times"])) {

                    $timesArray = collect($request_data["times"])->unique("day");
                    BusinessTime::where([
                        "business_id" => $business->id
                    ])
                        ->delete();

                    $timesArray = collect($request_data["times"])->unique("day");
                    foreach ($timesArray as $business_time) {
                        BusinessTime::create([
                            "business_id" => $business->id,
                            "day" => $business_time["day"],
                            "start_at" => $business_time["start_at"],
                            "end_at" => $business_time["end_at"],
                            "is_weekend" => $business_time["is_weekend"],
                        ]);
                    }
                }




                return response([
                    "user" => $user,
                    "business" => $business
                ], 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     * @OA\Put(
     *      path="/v1.0/businesses/toggle-active",
     *      operationId="toggleActiveBusiness",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to toggle business",
     *      description="This method is to toggle business",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","first_Name","last_Name","email","password","password_confirmation","phone","address_line_1","address_line_2","country","city","postcode","role"},
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

    public function toggleActiveBusiness(GetIdRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $request_data = $request->validated();

            $businessQuery  = Business::where(["id" => $request_data["id"]]);
            if (!auth()->user()->hasRole('superadmin')) {
                $businessQuery = $businessQuery->where(function ($query) {
                    return   $query->where('id', auth()->user()->business_id)
                        ->orWhere('created_by', auth()->user()->id)
                        ->orWhere('owner_id', auth()->user()->id);
                });
            }

            $business =  $businessQuery->first();


            if (!$business) {
                $this->storeError(
                    "no data found",
                    404,
                    "front end error",
                    "front end error"
                );
                return response()->json([
                    "message" => "no business found"
                ], 404);
            }


            $business->update([
                'is_active' => !$business->is_active
            ]);

            return response()->json(['message' => 'business status updated successfully'], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }





    /**
     *
     * @OA\Put(
     *      path="/v1.0/businesses/separate",
     *      operationId="updateBusinessSeparate",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update business",
     *      description="This method is to update business",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"business"},

     *
     *  @OA\Property(property="business", type="string", format="array",example={
     *   *  * "id":1,
     * "name":"ABCD businesses",
     * "about":"Best businesses in Dhaka",
     * "web_page":"https://www.facebook.com/",
     *   "color_theme_name":"#ddd",
     *
     *  "phone":"01771034383",
     *  "email":"rifatalashwad@gmail.com",
     *  "phone":"01771034383",
     *  "additional_information":"No Additional Information",
     *  "address_line_1":"Dhaka",
     *  "address_line_2":"Dinajpur",
     *    * *  "lat":"23.704263332849386",
     *    * *  "long":"90.44707059805279",
     *
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  "postcode":"Dinajpur",
     *
     *  "logo":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *      *  *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *  "images":{"/a","/b","/c"},
     * *  "currency":"BDT"
     *
     * }),
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
    public function updateBusinessSeparate(BusinessUpdateSeparateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return  DB::transaction(function () use (&$request) {
                if (!$request->user()->hasPermissionTo('business_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                if (!$this->businessOwnerCheck($request["business"]["id"])) {
                    return response()->json([
                        "message" => "you are not the owner of the business or the requested business does not exist."
                    ], 401);
                }

                $request_data = $request->validated();


                //  business info ##############
                // $request_data['business']['status'] = "pending";

                $business  =  tap(Business::where([
                    "id" => $request_data['business']["id"]
                ]))->update(
                    collect($request_data['business'])->only([
                        "name",
                        "about",
                        "web_page",
                        "color_theme_name",
                        "phone",
                        "email",
                        "additional_information",
                        "address_line_1",
                        "address_line_2",
                        "lat",
                        "long",
                        "country",
                        "city",
                        "postcode",
                        "logo",
                        "image",
                        "background_image",
                        "status",
                        // "is_active",



                        "currency",

                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();
                if (!$business) {
                    $this->storeError(
                        "no data found",
                        404,
                        "front end error",
                        "front end error"
                    );
                    return response()->json([
                        "massage" => "no business found"
                    ], 404);
                }








                return response([
                    "business" => $business
                ], 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    public function query_filters($query)
    {
        return   $query->when(!request()->user()->hasRole('superadmin'), function ($query) {
            return   $query->where(function ($query) {
                return   $query->where('id', auth()->user()->business_id)
                    ->orWhere('created_by', auth()->user()->id)
                    ->orWhere('owner_id', auth()->user()->id);
            });
        })
            ->when(!empty(request()->search_key), function ($query) {
                $term = request()->search_key;
                return $query->where(function ($query) use ($term) {
                    $query->where("name", "like", "%" . $term . "%")
                        ->orWhere("phone", "like", "%" . $term . "%")
                        ->orWhere("email", "like", "%" . $term . "%")
                        ->orWhere("city", "like", "%" . $term . "%")
                        ->orWhere("postcode", "like", "%" . $term . "%");
                });
            })
            ->when(!empty(request()->start_date), function ($query) {
                return $query->where('created_at', ">=", request()->start_date);
            })
            ->when(!empty(request()->end_date), function ($query) {
                return $query->where('created_at', "<=", (request()->end_date . ' 23:59:59'));
            })
            ->when(!empty(request()->start_lat), function ($query) {
                return $query->where('lat', ">=", request()->start_lat);
            })
            ->when(!empty(request()->end_lat), function ($query) {
                return $query->where('lat', "<=", request()->end_lat);
            })
            ->when(!empty(request()->start_long), function ($query) {
                return $query->where('long', ">=", request()->start_long);
            })
            ->when(!empty(request()->end_long), function ($query) {
                return $query->where('long', "<=", request()->end_long);
            })
            ->when(!empty(request()->address), function ($query) {
                $term = request()->address;
                return $query->where(function ($query) use ($term) {
                    $query->where("country", "like", "%" . $term . "%")
                        ->orWhere("city", "like", "%" . $term . "%");
                });
            })
            ->when(!empty(request()->country_code), function ($query) {
                return $query->orWhere("country", "like", "%" . request()->country_code . "%");
            })
            ->when(!empty(request()->city), function ($query) {
                return $query->orWhere("city", "like", "%" . request()->city . "%");
            });
    }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/businesses",
     *      operationId="getBusinesses",
     *      tags={"business_management"},
     * *  @OA\Parameter(
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
     * name="country_code",
     * in="query",
     * description="country_code",
     * required=true,
     * example="country_code"
     * ),
     * *  @OA\Parameter(
     * name="address",
     * in="query",
     * description="address",
     * required=true,
     * example="address"
     * ),
     * *  @OA\Parameter(
     * name="city",
     * in="query",
     * description="city",
     * required=true,
     * example="city"
     * ),
     * *  @OA\Parameter(
     * name="start_lat",
     * in="query",
     * description="start_lat",
     * required=true,
     * example="3"
     * ),
     * *  @OA\Parameter(
     * name="end_lat",
     * in="query",
     * description="end_lat",
     * required=true,
     * example="2"
     * ),
     * *  @OA\Parameter(
     * name="start_long",
     * in="query",
     * description="start_long",
     * required=true,
     * example="1"
     * ),
     * *  @OA\Parameter(
     * name="end_long",
     * in="query",
     * description="end_long",
     * required=true,
     * example="4"
     * ),
     * *  @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="per_page",
     * required=true,
     * example="10"
     * ),
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *
     *      summary="This method is to get businesses",
     *      description="This method is to get businesses",
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

    public function getBusinesses(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }


            $query = Business::with("owner");
            $query = $this->query_filters($query);
            $businesses = $this->retrieveData($query, "id","businesses");


            return response()->json($businesses, 200);
            return response()->json($businesses, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v2.0/businesses",
     *      operationId="getBusinessesV2",
     *      tags={"business_management"},
     * *  @OA\Parameter(
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
     * name="country_code",
     * in="query",
     * description="country_code",
     * required=true,
     * example="country_code"
     * ),
     * *  @OA\Parameter(
     * name="address",
     * in="query",
     * description="address",
     * required=true,
     * example="address"
     * ),
     * *  @OA\Parameter(
     * name="city",
     * in="query",
     * description="city",
     * required=true,
     * example="city"
     * ),
     * *  @OA\Parameter(
     * name="start_lat",
     * in="query",
     * description="start_lat",
     * required=true,
     * example="3"
     * ),
     * *  @OA\Parameter(
     * name="end_lat",
     * in="query",
     * description="end_lat",
     * required=true,
     * example="2"
     * ),
     * *  @OA\Parameter(
     * name="start_long",
     * in="query",
     * description="start_long",
     * required=true,
     * example="1"
     * ),
     * *  @OA\Parameter(
     * name="end_long",
     * in="query",
     * description="end_long",
     * required=true,
     * example="4"
     * ),
     * *  @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="per_page",
     * required=true,
     * example="10"
     * ),
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *
     *      summary="This method is to get businesses",
     *      description="This method is to get businesses",
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

    public function getBusinessesV2(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $query = Business::with(

                [
                    "owner" => function ($query) {
                        $query->select(
                            "users.id",
                            'users.first_Name',
                            'users.last_Name',
                            'users.middle_Name',
                            'users.phone',
                            'users.image',
                        );
                    }

                ]

            );
            $query = $this->query_filters($query)
                ->select(
                    "businesses.id",
                    "businesses.name",
                    "businesses.url",
                    "businesses.web_page",
                    "businesses.color_theme_name",

                    "businesses.phone",
                    "businesses.email",
                    "businesses.address_line_1",
                    "businesses.lat",
                    "businesses.long",
                    "businesses.country",
                    "businesses.city",
                    "businesses.postcode",
                    "businesses.status",
                    "businesses.is_active",
                    "businesses.owner_id",
                    "businesses.created_by"
                );
            $businesses = $this->retrieveData($query, "id","businesses");

            return response()->json($businesses, 200);
            return response()->json($businesses, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }





    /**
     *
     * @OA\Get(
     *      path="/v1.0/businesses/{id}",
     *      operationId="getBusinessById",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="This method is to get business by id",
     *      description="This method is to get business by id",
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

    public function getBusinessById($id, Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            if (!$this->businessOwnerCheck($id)) {
                return response()->json([
                    "message" => "you are not the owner of the business or the requested business does not exist."
                ], 401);
            }

            $business = Business::with(
                "owner",
                "times"
                // "default_work_shift.details"

            )->where([
                "id" => $id
            ])
                ->first();

            if(!empty($business)){
$business = $this->getUrlLink($business,"logo",config("setup-config.business_gallery_location"),$business->name);
            }


            return response()->json($business, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/client/businesses/{id}",
     *      operationId="getBusinessByIdClient",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="This method is to get business by id",
     *      description="This method is to get business by id",
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

    public function getBusinessByIdClient($id, Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $business = Business::where([
                "id" => $id
            ])
                ->select("id", "name", "logo", "web_page", "email","color_theme_name")
                ->first();




            return response()->json($business, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/client/businesses-get-by-url/{url}",
     *      operationId="getByUrlClient",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="url",
     *         in="path",
     *         description="",
     *         required=true,
     *  example=""
     *      ),
     *      summary="This method is to get business by id",
     *      description="This method is to get business by id",
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

    public function getByUrlClient($url, Request $request)
    {

        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");



            $business = Business::where([
                "url" => $url
            ])
                ->select("id", "name", "logo")
                ->first();

            return response()->json($business, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Delete(
     *      path="/v1.0/businesses/{ids}",
     *      operationId="deleteBusinessesByIds",
     *      tags={"business_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="ids",
     *         in="path",
     *         description="ids",
     *         required=true,
     *  example="6,7,8"
     *      ),
     *      summary="This method is to delete business by id",
     *      description="This method is to delete business by id",
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

    public function deleteBusinessesByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $business = Business::when(!$request->user()->hasRole('superadmin'), function ($query) {
                return $query->where(function ($query) {
                    return $query->where('id', auth()->user()->business_id)
                        ->orWhere('created_by', auth()->user()->id)
                        ->orWhere('owner_id', auth()->user()->id);
                });
            })
                ->where([
                    "id" => $ids
                ])
                ->first();

            if (!$business) {
                $this->storeError("Business not found", 404, "Front-end error", "Front-end error");
                return response()->json([
                    "message" => "The specified business does not exist."
                ], 404);
            }


            $folderName = str_replace(' ', '_', $business->name);
            $folderPath = public_path($folderName);

            // Delete associated folder if it exists
            if (File::exists($folderPath)) {
                if (File::deleteDirectory($folderPath)) {
                    // Log or provide a success message for folder deletion
                    Log::info("Folder {$folderName} successfully deleted.");
                } else {
                    // Handle the case where the folder couldn't be deleted
                    Log::warning("Failed to delete folder {$folderName}.");
                }
            }


            $business->delete();

            User::where('business_id', $ids)->delete();

            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $ids], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }








    /**
     *
     * @OA\Get(
     *      path="/v1.0/businesses/by-business-owner/all",
     *      operationId="getAllBusinessesByBusinessOwner",
     *      tags={"business_management"},

     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="This method is to get businesses",
     *      description="This method is to get businesses",
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

    public function getAllBusinessesByBusinessOwner(Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasRole('business_admin')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $businessesQuery = Business::where([
                "owner_id" => $request->user()->id
            ]);



            $businesses = $businessesQuery->orderByDesc("id")->get();
            return response()->json($businesses, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
