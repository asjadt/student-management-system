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

    /**
     * Store a newly created business image in storage.
     *
     * @param  \App\Http\Requests\ImageUploadRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function createBusinessImage(ImageUploadRequest $request)
    {
        // Try to store business image
        try {
            // Store the activity
            $this->storeActivity($request, "Upload Business Image", "Uploading a new business image");

            // Validate the request data
            $request_data = $request->validated();

            // Get the storage location and file name
            $location =  config("setup-config.business_gallery_location");

            // Generate a new file name
            $new_file_name = time() . '_' . str_replace(' ', '_', $request_data["image"]->getClientOriginalName());

            // Move the file to the correct location
            $request_data["image"]->move(public_path($location), $new_file_name);

            // Return the image name, location and full path
            return response()->json([
                "image" => $new_file_name,
                "location" => $location,
                "full_location" => "/" . $location . "/" . $new_file_name
            ], 200);
        } catch (Exception $e) {
            // Log any errors
            error_log($e->getMessage());
            // Return the error
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


                $existingLogoPath = public_path($this->getUrlLink($business, "logo", config("setup-config.business_gallery_location"))["logo"], $business->name);



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

    /**
     * Store multiple business images.
     *
     * @param  \App\Http\Requests\MultipleImageUploadRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function createBusinessImageMultiple(MultipleImageUploadRequest $request)
    {
        try {
            // Log the activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Validate the request data
            $request_data = $request->validated();

            // Get the storage location and file name
            $location =  config("setup-config.business_gallery_location");

            // Initialize an array to store the full paths of uploaded images
            $images = [];

            // Loop over the images
            if (!empty($request_data["images"])) {
                foreach ($request_data["images"] as $image) {
                    // Generate a new file name
                    $new_file_name = time() . '_' . str_replace(' ', '_', $image->getClientOriginalName());

                    // Move the file to the correct location
                    $image->move(public_path($location), $new_file_name);

                    // Add the full path of the image to the array
                    array_push($images, ("/" . $location . "/" . $new_file_name));
                }
            }

            // Return the array of full paths of uploaded images
            return response()->json(["images" => $images], 201);
        } catch (Exception $e) {
            // Log any errors
            error_log($e->getMessage());
            // Return the error
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
            // Log the activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Create a transaction to ensure all or nothing
            return  DB::transaction(function () use (&$request) {

                // Check if the user has permission to create a business
                if (!$request->user()->hasPermissionTo('business_create')) {
                    // If not, return a 401 error
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Validate the request data
                $request_data = $request->validated();


                // Find the user with the given id
                $user = User::where([
                    "id" =>  $request_data['business']['owner_id']
                ])
                    ->first();

                // If the user does not exist, return a 422 error
                if (!$user) {
                    $error =  [
                        "message" => "The given data was invalid.",
                        "errors" => ["owner_id" => ["No User Found"]]
                    ];
                    throw new Exception(json_encode($error), 422);
                }

                // Check if the user is a business admin
                if (!$user->hasRole('business_admin')) {
                    // If not, return a 422 error
                    $error =  [
                        "message" => "The given data was invalid.",
                        "errors" => ["owner_id" => ["The user is not a businesses Owner"]]
                    ];
                    throw new Exception(json_encode($error), 422);
                }

                // Set the status of the business to 'pending'
                $request_data['business']['status'] = "pending";

                // Set the created_by field of the business to the current user's id
                $request_data['business']['created_by'] = $request->user()->id;

                // Set the is_active field of the business to true
                $request_data['business']['is_active'] = true;

                // Create the business
                $business =  Business::create($request_data['business']);


                // Create the business settings
                BusinessSetting::create([
                    'business_id' => $business->id,
                    'online_student_status_id' => NULL,
                    'student_data_fields' => config("setup-config.student_data_fields"),
                    'student_verification_fields' => config("setup-config.student_verification_fields")
                ]);

                // Return the created business
                return response([

                    "business" => $business
                ], 201);
            });
        } catch (Exception $e) {

            // Log the error
            error_log($e->getMessage());

            // Return the error
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
        // Attempt to generate a database for a business
        try {
            // Log the activity for auditing purposes
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has permission to create a business
            if (!$request->user()->hasPermissionTo('business_create')) {
                // If the user does not have permission, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Validate the incoming request data
            $request->validate([
                // business_id is required, must be an integer, and must exist in the businesses table
                'business_id' => 'required|integer|exists:businesses,id',
            ]);

            // Log a message for debugging purposes
            Log::info("test1..");

            // Check if the SELF_DB environment variable is set to true
            if (env("SELF_DB") == true) {
                // Log a message for debugging purposes
                Log::info("test");

                // Execute the Artisan command to generate a database using the provided business_id
                Artisan::call(('generate:database ' . $request->business_id));
            }

            // Return a success response indicating that the operation was successful
            return response([
                "ok" => true
            ], 201);
        } catch (Exception $e) {
            // If an exception occurs, handle it by sending an error response
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
        /**
         * This function will check if there is a conflict
         * between the new business time and the existing
         * work shifts of the business.
         *
         * It will return an array of work shift ids that
         * are in conflict with the new business time.
         *
         * If there is no conflict, it will return an empty array.
         *
         * This function is used in the business creation
         * and business update process.
         */

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return  DB::transaction(function () use (&$request) {

                // Check if the authenticated user has permission to create a business
                // if(!$request->user()->hasPermissionTo('business_create')){
                //     return response()->json([
                //        "message" => "You can not perform this action"
                //     ],401);
                // }
                $request_data = $request->validated();

                // Initialize an array to store the ids of the conflicted work shifts
                $conflicted_work_shift_ids = collect();

                // Loop over the days of the week
                $timesArray = collect($request_data["times"])->unique("day");
                foreach ($timesArray as $business_time) {
                    // Get the work shift ids that are in conflict with the new business time
                    $work_shift_ids = WorkShift::where([
                        "business_id" => auth()->user()->business_id
                    ])
                        ->whereHas('details', function ($query) use ($business_time) {
                            // Match the day of the week
                            $query->where('work_shift_details.day', ($business_time["day"]));

                            // Match the start and end times
                            $query->where(function ($query) use ($business_time) {
                                $query->whereTime('work_shift_details.start_at', '<=', ($business_time["start_at"]))
                                    ->orWhereTime('work_shift_details.end_at', '>=', ($business_time["end_at"]));
                            });

                            // Match the is_weekend flag
                            $query->when(!empty($business_time["is_weekend"]), function ($query) {
                                $query->where('work_shift_details.is_weekend', 1);
                            });
                        })
                        ->pluck("id");

                    // Add the ids to the array
                    $conflicted_work_shift_ids = $conflicted_work_shift_ids->merge($work_shift_ids);
                }
                // Return the array of conflicted work shift ids
                $conflicted_work_shift_ids = $conflicted_work_shift_ids->unique()->values()->all();
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
            // Store this activity in the activity log
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            // Check if the user has the permission to create a business
            if (!$request->user()->hasPermissionTo('business_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Validate the request data
            $request_data = $request->validated();

            // user info starts ##############

            // Hash the password and add it to the request data
            $password = $request_data['user']['password'];
            $request_data['user']['password'] = Hash::make($password);

            // If the user is not a super admin, generate a random password
            //    if(!$request->user()->hasRole('superadmin') || empty($request_data['user']['password'])) {
            //     $password = Str::random(10);
            //     $request_data['user']['password'] = Hash::make($password);
            //     }

            // Add a remember token to the request data
            $request_data['user']['remember_token'] = Str::random(10);
            $request_data['user']['is_active'] = true;
            $request_data['user']['created_by'] = $request->user()->id;

            // Copy the business address to the user address
            $request_data['user']['address_line_1'] = $request_data['business']['address_line_1'];
            $request_data['user']['address_line_2'] = (!empty($request_data['business']['address_line_2']) ? $request_data['business']['address_line_2'] : "");
            $request_data['user']['country'] = $request_data['business']['country'];
            $request_data['user']['city'] = $request_data['business']['city'];
            $request_data['user']['postcode'] = $request_data['business']['postcode'] ?? null;
            $request_data['user']['lat'] = $request_data['business']['lat'];
            $request_data['user']['long'] = $request_data['business']['long'];

            // Create a new user
            $user =  User::create($request_data['user']);

            // Assign the role of business admin to the user
            $user->assignRole('business_admin');
            // end user info ##############


            //  business info ##############

            // Set the status of the business to pending
            $request_data['business']['status'] = "pending";

            // Set the owner of the business to the user that just created it
            $request_data['business']['owner_id'] = $user->id;

            // Set the created by to the user that sent the request
            $request_data['business']['created_by'] = $request->user()->id;

            // Set the business to active
            $request_data['business']['is_active'] = true;

            // Create a new business
            $business =  Business::create($request_data['business']);


            // Set the email verified at to now
            $user->email_verified_at = now();
            // Set the business id to the user
            $user->business_id = $business->id;

            // Generate a random token and set it as the reset password token
            $token = Str::random(30);
            $user->resetPasswordToken = $token;
            // Set the reset password expires to now minus one day
            $user->resetPasswordExpires = Carbon::now()->subDays(-1);

            // Save the user
            $user->save();

            // Delete all the business times of the business
            BusinessTime::where([
                "business_id" => $business->id
            ])
                ->delete();

            // Loop through the times array and create a new business time for each day
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

            // Get all the default roles that are not assigned to any business
            $defaultRoles = Role::where([
                "business_id" => NULL,
                "is_default" => 1,
                "is_default_for_business" => 1,
                "guard_name" => "api",
            ])->get();

            // Loop through the default roles and create a new role for each one
            foreach ($defaultRoles as $defaultRole) {
                $insertableData = [
                    'name'  => ($defaultRole->name . "#" . $business->id),
                    "is_default" => 1,
                    "business_id" => $business->id,
                    "is_default_for_business" => 0,
                    "guard_name" => "api",
                ];
                $role  = Role::create($insertableData);

                // Get all the permissions that are assigned to the default role
                $permissions = $defaultRole->permissions;

                // Loop through the permissions and assign them to the new role
                foreach ($permissions as $permission) {
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }

            // Create a new business setting
            BusinessSetting::create([
                'business_id' => $business->id,
                'online_student_status_id' => NULL,
                'student_data_fields' => config("setup-config.student_data_fields"),
                'student_verification_fields' => config("setup-config.student_verification_fields")
            ]);

            // end business info ##############

            //  if($request_data['user']['send_password']) {
            // If the user wants to send the password, send an email to the user with the password
            if (env("SEND_EMAIL") == true) {
                Mail::to($request_data['user']['email'])->send(new SendPassword($user, $password));
            }
            // If the user wants to generate a new database for the business, generate it
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
        // This method is for updating a user and a business.
        // It takes a request object as a parameter which has the updated data for the user and the business.
        // First, it checks if the user has the permission to update the business.
        // If the user doesn't have the permission, it returns a 401 response with a message saying that the user can't perform this action.
        // If the user has the permission, it starts a database transaction.
        // Inside the transaction, it first checks if the user exists in the database.
        // If the user doesn't exist, it returns a 404 response with a message saying that no user found with this id.
        // If the user exists, it updates the user's data in the database.
        // If the password is changed, it hashes the new password and updates it in the database.
        // If the password is not changed, it just updates the other fields.
        // After updating the user, it checks if the business exists in the database.
        // If the business doesn't exist, it returns a 404 response with a message saying that no business found with this id.
        // If the business exists, it updates the business's data in the database.
        // If the business's name is changed, it renames the folder associated with the business.
        // After updating the business, it checks if there are any new times to be added to the business.
        // If there are new times, it deletes all the existing times for the business and adds the new times.
        // Finally, it returns a 201 response with the updated user and business data.

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return  DB::transaction(function () use (&$request) {
                // Check if the user has the permission to update the business
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
                // Check if the user exists in the database
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

                // Update the user's data in the database
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

                // Check if the business exists in the database
                $business = Business::where(["id" => $request_data['business']["id"]])
                    ->first();

                if (!$business) {
                    return response()->json([
                        "message" => "Business not found"
                    ], 404);
                }

                // Update the business's data in the database
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
                    // "logo",
                    "image",
                    "status",
                    "background_image",
                    "currency",
                    "letter_template_header",
                    "letter_template_footer"
                ])->toArray());

                $business->save();

                // Check if there are any new times to be added to the business
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

    /**
     * Toggle the active status of a business
     *
     * @param GetIdRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActiveBusiness(GetIdRequest $request)
    {
        try {
            // Log the request with a dummy activity and description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the permission to update businesses
            if (!$request->user()->hasPermissionTo('business_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Validate the request data
            $request_data = $request->validated();

            // Build the query to retrieve the business
            $businessQuery  = Business::where(["id" => $request_data["id"]]);

            // Limit the query to the current user's business if the user is not a superadmin
            if (!auth()->user()->hasRole('superadmin')) {
                $businessQuery = $businessQuery->where(function ($query) {
                    // Include businesses that are owned by the current user
                    // or created by the current user
                    // or have the current user as the owner
                    return   $query->where('id', auth()->user()->business_id)
                        ->orWhere('created_by', auth()->user()->id)
                        ->orWhere('owner_id', auth()->user()->id);
                });
            }

            // Retrieve the business
            $business =  $businessQuery->first();

            // If no business is found, return a 404 error
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

            // Toggle the active status of the business
            $business->update([
                'is_active' => !$business->is_active
            ]);

            // Return a success response
            return response()->json(['message' => 'business status updated successfully'], 200);
        } catch (Exception $e) {
            // Log the error
            error_log($e->getMessage());

            // Return a 500 error response
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
            // Log the activity of the request
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            // Start a database transaction
            return  DB::transaction(function () use (&$request) {
                // Check if the user has permission to update the business
                if (!$request->user()->hasPermissionTo('business_update')) {
                    // If the user does not have permission, return a 401 error
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Check if the user is the owner of the business
                if (!$this->businessOwnerCheck($request["business"]["id"])) {
                    // If the user is not the owner of the business, return a 401 error
                    return response()->json([
                        "message" => "you are not the owner of the business or the requested business does not exist."
                    ], 401);
                }

                // Validate the request data
                $request_data = $request->validated();

                // Update the business information
                $business  =  tap(Business::where([
                    "id" => $request_data['business']["id"]
                ]))->update(
                    // Only update the fields that are present in the request data
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
                // If the business is not found, return a 404 error
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

                // Return the updated business
                return response([
                    "business" => $business
                ], 201);
            });
        } catch (Exception $e) {
            // Log the error
            error_log($e->getMessage());

            // Return a 500 error response
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     * Filter the business list query
     *
     * @param  Builder $query
     * @return Builder
     */
    public function query_filters($query)
    {
        // Restrict the query to the businesses that the user has access to.
        // If the user is a super admin, they have access to all businesses.
        // Otherwise, they only have access to the businesses that they created
        // or the business that they are a part of.
        return $query->when(!request()->user()->hasRole('superadmin'), function ($query) {
            return $query->where(function ($query) {
                // The user is not a super admin, so they only have access to
                // the businesses that they created or the business that they
                // are a part of.
                return $query->where('id', auth()->user()->business_id)
                    ->orWhere('created_by', auth()->user()->id)
                    ->orWhere('owner_id', auth()->user()->id);
            });
        })
            // If the user has provided a search key, filter the query to only
            // include businesses that match the search key.
            ->when(!empty(request()->search_key), function ($query) {
                $term = request()->search_key;
                return $query->where(function ($query) use ($term) {
                    // The user has provided a search key, so filter the query to
                    // only include businesses that match the search key.
                    $query->where("name", "like", "%" . $term . "%")
                        ->orWhere("phone", "like", "%" . $term . "%")
                        ->orWhere("email", "like", "%" . $term . "%")
                        ->orWhere("city", "like", "%" . $term . "%")
                        ->orWhere("postcode", "like", "%" . $term . "%");
                });
            })
            // If the user has provided a start date, filter the query to only
            // include businesses that were created after the start date.
            ->when(!empty(request()->start_date), function ($query) {
                return $query->where('created_at', ">=", request()->start_date);
            })
            // If the user has provided an end date, filter the query to only
            // include businesses that were created before the end date.
            ->when(!empty(request()->end_date), function ($query) {
                return $query->where('created_at', "<=", (request()->end_date . ' 23:59:59'));
            })
            // If the user has provided a start latitude, filter the query to only
            // include businesses that have a latitude greater than or equal to
            // the start latitude.
            ->when(!empty(request()->start_lat), function ($query) {
                return $query->where('lat', ">=", request()->start_lat);
            })
            // If the user has provided an end latitude, filter the query to only
            // include businesses that have a latitude less than or equal to
            // the end latitude.
            ->when(!empty(request()->end_lat), function ($query) {
                return $query->where('lat', "<=", request()->end_lat);
            })
            // If the user has provided a start longitude, filter the query to only
            // include businesses that have a longitude greater than or equal to
            // the start longitude.
            ->when(!empty(request()->start_long), function ($query) {
                return $query->where('long', ">=", request()->start_long);
            })
            // If the user has provided an end longitude, filter the query to only
            // include businesses that have a longitude less than or equal to
            // the end longitude.
            ->when(!empty(request()->end_long), function ($query) {
                return $query->where('long', "<=", request()->end_long);
            })
            // If the user has provided an address, filter the query to only
            // include businesses that have a matching address.
            ->when(!empty(request()->address), function ($query) {
                $term = request()->address;
                return $query->where(function ($query) use ($term) {
                    // The user has provided an address, so filter the query to
                    // only include businesses that have a matching address.
                    $query->where("country", "like", "%" . $term . "%")
                        ->orWhere("city", "like", "%" . $term . "%");
                });
            })
            // If the user has provided a country code, filter the query to only
            // include businesses that have a matching country code.
            ->when(!empty(request()->country_code), function ($query) {
                return $query->orWhere("country", "like", "%" . request()->country_code . "%");
            })
            // If the user has provided a city, filter the query to only
            // include businesses that have a matching city.
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
            // Store the user activity with dummy data for tracking purposes
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the 'business_view' permission
            if (!$request->user()->hasPermissionTo('business_view')) {
                // If the user does not have permission, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Initialize a query on the Business model to include related 'owner' data
            $query = Business::with("owner");

            // Apply additional filters to the query based on the request parameters
            $query = $this->query_filters($query);

            // Retrieve the data from the database, ordering by 'id'
            $businesses = $this->retrieveData($query, "id", "businesses");

            // Return the retrieved business data as a JSON response with a 200 OK status
            return response()->json($businesses, 200);
        } catch (Exception $e) {
            // If an exception occurs, handle the error by logging and returning a 500 Internal Server Error response
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
            // Store the user activity with dummy data for tracking purposes
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the 'business_view' permission
            if (!$request->user()->hasPermissionTo('business_view')) {
                // If the user does not have permission, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Initialize a query on the Business model to include related 'owner' data
            // The owner is the user who created the business
            $query = Business::with(
                [
                    "owner" => function ($query) {
                        // Select the user's id, first name, last name, middle name, phone, and image
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

            // Apply additional filters to the query based on the request parameters
            $query = $this->query_filters($query);

            // Select the business fields
            $query = $query->select(
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

            // Retrieve the data from the database, ordering by 'id'
            $businesses = $this->retrieveData($query, "id", "businesses");

            // Return the retrieved business data as a JSON response with a 200 OK status
            return response()->json($businesses, 200);
        } catch (Exception $e) {

            // If an exception occurs, handle the error by logging and returning a 500 Internal Server Error response
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
            // Log the user activity with a dummy activity and description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has 'business_view' permission
            if (!$request->user()->hasPermissionTo('business_view')) {
                // If the user does not have permission, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Verify if the user is the owner of the business or if the business exists
            if (!$this->businessOwnerCheck($id)) {
                // If not the owner or business doesn't exist, return a 401 Unauthorized response
                return response()->json([
                    "message" => "you are not the owner of the business or the requested business does not exist."
                ], 401);
            }

            // Retrieve the business with its owner and times relationships
            $business = Business::with("owner", "times")
                ->where(["id" => $id])
                ->first();

            // If a business is found, generate a URL link for the business logo
            if (!empty($business)) {
                $business = $this->getUrlLink($business, "logo", config("setup-config.business_gallery_location"), $business->name);
            }

            // Return the business data as a JSON response with a 200 OK status
            return response()->json($business, 200);
        } catch (Exception $e) {
            // If an exception occurs, handle the error by logging and returning a 500 Internal Server Error response
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
                ->select("id", "name", "logo", "web_page", "email", "color_theme_name")
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
            // Log the user activity with a dummy activity and description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Attempt to find the business by the provided URL
            $business = Business::where([
                "url" => $url // Filter businesses where the 'url' matches the provided URL
            ])
                ->select("id", "name", "logo") // Select only the 'id', 'name', and 'logo' fields from the result
                ->first(); // Retrieve the first match or null if no match is found

            // Return the business data as a JSON response with a 200 OK status
            return response()->json($business, 200);
        } catch (Exception $e) {
            // Handle any exceptions by logging the error and returning a 500 Internal Server Error response
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
            // Log the activity with a dummy description for tracking purposes
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has permission to delete a business
            if (!$request->user()->hasPermissionTo('business_delete')) {
                // Return a 401 Unauthorized response if the user lacks permission
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Query for the business, applying restrictions for non-superadmin users
            $business = Business::when(!$request->user()->hasRole('superadmin'), function ($query) {
                // Filter to include businesses owned, created, or associated with the current user
                return $query->where(function ($query) {
                    return $query->where('id', auth()->user()->business_id)
                        ->orWhere('created_by', auth()->user()->id)
                        ->orWhere('owner_id', auth()->user()->id);
                });
            })
                ->where([
                    "id" => $ids // Only consider the business with the given ID
                ])
                ->first(); // Retrieve the first matching business

            // If the business doesn't exist, log an error and return a 404 response
            if (!$business) {
                $this->storeError("Business not found", 404, "Front-end error", "Front-end error");
                return response()->json([
                    "message" => "The specified business does not exist."
                ], 404);
            }

            // Prepare to delete the associated folder by constructing its path
            $folderName = str_replace(' ', '_', $business->name);
            $folderPath = public_path($folderName);

            // Check if the folder exists and attempt to delete it
            if (File::exists($folderPath)) {
                if (File::deleteDirectory($folderPath)) {
                    // Log a success message if the folder is deleted
                    Log::info("Folder {$folderName} successfully deleted.");
                } else {
                    // Log a warning if the folder could not be deleted
                    Log::warning("Failed to delete folder {$folderName}.");
                }
            }

            // Permanently delete the business record from the database
            $business->forceDelete();

            // Delete all users associated with this business ID
            User::where('business_id', $ids)->delete();

            // Return a success response, including the IDs of deleted records
            return response()->json(["message" => "data deleted successfully", "deleted_ids" => $ids], 200);
        } catch (Exception $e) {
            // Handle any exceptions by sending a 500 Internal Server Error response
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
            // Log the user activity with a dummy activity and description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the 'business_admin' role
            if (!$request->user()->hasRole('business_admin')) {
                // If the user does not have the 'business_admin' role, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Initialize a query on the Business model to filter businesses by the current user's ID
            $businessesQuery = Business::where([
                "owner_id" => $request->user()->id // Only include businesses where the 'owner_id' matches the current user's ID
            ]);

            // Execute the query, ordering the results by 'id' in descending order
            $businesses = $businessesQuery->orderByDesc("id")->get();

            // Return the retrieved business data as a JSON response with a 200 OK status
            return response()->json($businesses, 200);
        } catch (Exception $e) {
            // If an exception occurs, handle the error by logging and returning a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }

}
