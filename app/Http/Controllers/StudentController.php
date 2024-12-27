<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentCreateRequest;
use App\Http\Requests\StudentUpdateRequest;
use App\Http\Requests\MultipleFileUploadRequest;
use App\Http\Requests\MultipleStudentFileUploadRequest;
use App\Http\Requests\StudentCreateRequestClient;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\Student;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    use ErrorUtil, UserActivityUtil, BusinessUtil, BasicUtil;

    /**
        *
     * @OA\Post(
     *      path="/v1.0/students/multiple-file-upload",
     *      operationId="createStudentFileMultiple",
     *      tags={"students"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="This method is to store multiple student files",
     *      description="This method is to store multiple student files",
     *
   *  @OA\RequestBody(
        *   * @OA\MediaType(
*     mediaType="multipart/form-data",
*     @OA\Schema(
*         required={"files[]"},
*         @OA\Property(
*             description="array of files to upload",
*             property="files[]",
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

     public function createStudentFileMultiple(MultipleStudentFileUploadRequest $request)
     {
         try{
             $this->storeActivity($request, "DUMMY activity","DUMMY description");

             $request_data = $request->validated();

             $location =  config("setup-config.temporary_files_location");

             $files = [];
             if (!empty($request_data["files"])) {
                 foreach ($request_data["files"] as $file) {
                     $new_file_name = time() . '_' . $file->getClientOriginalName();
                     $new_file_name = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                     $file->move(public_path($location), $new_file_name);
                     array_push($files, ("/" . $location . "/" . $new_file_name));
                 }
             }

             return response()->json(["files" => $files], 201);


         } catch(Exception $e){
             error_log($e->getMessage());
         return $this->sendError($e,500,$request);
         }
     }

   /**
     *
     * @OA\Post(
     *      path="/v1.0/students",
     *      operationId="createStudent",
     *      tags={"students"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store student",
     *      description="This method is to store student",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     * *     @OA\Property(property="title", type="string", format="string", example="title"),
*     @OA\Property(property="first_name", type="string", format="string", example="John"),
 *     @OA\Property(property="middle_name", type="string", format="string", example=""),
 *     @OA\Property(property="last_name", type="string", format="string", example="Doe"),
 *     @OA\Property(property="nationality", type="string", format="string", example="Country"),
 * *     @OA\Property(property="course_fee", type="string", format="string", example="Country"),
 * *     @OA\Property(property="fee_paid", type="string", format="string", example="Country"),
 *     @OA\Property(property="passport_number", type="string", format="string", example="ABC123"),
 *     @OA\Property(property="student_id", type="string", format="string", example="School123"),
 *     @OA\Property(property="date_of_birth", type="string", format="date", example="2000-01-01"),
 *     @OA\Property(property="course_start_date", type="string", format="date", example="2024-01-31"),
 *     @OA\Property(property="letter_issue_date", type="string", format="date", example="2024-02-01"),
 *     @OA\Property(property="student_status_id", type="number", format="number", example=1),
 *  *     @OA\Property(property="course_title_id", type="number", format="number", example=1),
 *     @OA\Property(property="attachments", type="string", format="array", example={"a.png","b.jpeg"}),

             * *     @OA\Property(property="course_", type="string", format="email", example="course_duration", description="course_duration"),
             *  * *     @OA\Property(property="course_detail", type="string", format="email", example="course_detail", description="course_duration"),
             *
 * *     @OA\Property(property="email", type="string", format="email", example="student@example.com", description="Email address of the student"),
 *     @OA\Property(property="contact_number", type="string", format="string", example="+1234567890", description="Contact number of the student"),
 *     @OA\Property(property="sex", type="string", format="string", example="Male", description="Sex of the student"),
 *     @OA\Property(property="address", type="string", format="string", example="123 Main St, Apartment 4B", description="Address of the student"),
 *     @OA\Property(property="country", type="string", format="string", example="United States", description="Country of the student's address"),
 *     @OA\Property(property="city", type="string", format="string", example="New York", description="City of the student's address"),
 *     @OA\Property(property="postcode", type="string", format="string", example="10001", description="Postal code of the student's address"),
 *     @OA\Property(property="lat", type="string", format="string", example="40.712776", description="Latitude of the student's address"),
 *     @OA\Property(property="long", type="string", format="string", example="-74.005974", description="Longitude of the student's address"),
 * @OA\Property(
 *     property="emergency_contact_details",
 *     type="string",
 * example="John Doe, Father, +1234567890",
 *     description="Emergency contact details of the student"
 * ),
  * @OA\Property(
 *     property="previous_education_history",
 *     type="string",
 *     example={"institution": "High School", "year": "2019", "grade": "A"},
 *     description="Previous education history of the student"
 * ),
 *     @OA\Property(property="passport_issue_date", type="string", format="date", example="2020-01-01", description="Passport issue date of the student"),
 *     @OA\Property(property="passport_expiry_date", type="string", format="date", example="2030-01-01", description="Passport expiry date of the student"),
 *     @OA\Property(property="place_of_issue", type="string", format="string", example="New York, USA", description="Place where the student's passport was issued")
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

     public function createStudent(StudentCreateRequest $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");
             return DB::transaction(function () use ($request) {
                 if (!$request->user()->hasPermissionTo('student_create')) {
                     return response()->json([
                         "message" => "You can not perform this action"
                     ], 401);
                 }

                 $request_data = $request->validated();


                 $request_data["business_id"] = $request->user()->business_id;
                 $request_data["is_active"] = true;
                 $request_data["created_by"] = $request->user()->id;




                 $student =  Student::create($request_data);

                 $request_data["previous_education_history"] = json_decode($request_data["previous_education_history"],true);

                 if (isset($request_data["previous_education_history"]["student_docs"])) {
                    $request_data["previous_education_history"]["student_docs"] = $this->storeUploadedFiles(
                        $request_data["previous_education_history"]["student_docs"],
                        "file_name",
                        "student_docs",
                        NULL,
                        $student->id
                    );
                } else {
                    $request_data["previous_education_history"]["student_docs"] = [];
                }







                 return response($student, 201);
             });
         } catch (Exception $e) {
            try {
                $this->moveUploadedFilesBack($request_data["previous_education_history"]["student_docs"], "", "student_docs");
            } catch (Exception $innerException) {
                error_log("Failed to move leave files back: " . $innerException->getMessage());
            }
             return $this->sendError($e, 500, $request);
         }
     }
    /**
     *
     * @OA\Post(
     *      path="/v1.0/client/students",
     *      operationId="createStudentClient",
     *      tags={"students"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store student",
     *      description="This method is to store student",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     * *     @OA\Property(property="title", type="string", format="string", example="title"),
*     @OA\Property(property="first_name", type="string", format="string", example="John"),
 *     @OA\Property(property="middle_name", type="string", format="string", example=""),
 *     @OA\Property(property="last_name", type="string", format="string", example="Doe"),
 *     @OA\Property(property="nationality", type="string", format="string", example="Country"),
 * *     @OA\Property(property="course_fee", type="string", format="string", example="Country"),
 * *     @OA\Property(property="fee_paid", type="string", format="string", example="Country"),
 *     @OA\Property(property="passport_number", type="string", format="string", example="ABC123"),
 *     @OA\Property(property="student_id", type="string", format="string", example="School123"),
 *     @OA\Property(property="date_of_birth", type="string", format="date", example="2000-01-01"),
 *     @OA\Property(property="course_start_date", type="string", format="date", example="2024-01-31"),
 *     @OA\Property(property="letter_issue_date", type="string", format="date", example="2024-02-01"),
 *     @OA\Property(property="student_status_id", type="number", format="number", example=1),
 *  *     @OA\Property(property="course_title_id", type="number", format="number", example=1),
 *     @OA\Property(property="attachments", type="string", format="array", example={"a.png","b.jpeg"}),

             * *     @OA\Property(property="course_", type="string", format="email", example="course_duration", description="course_duration"),
             *  * *     @OA\Property(property="course_detail", type="string", format="email", example="course_detail", description="course_duration"),
             *
 * *     @OA\Property(property="email", type="string", format="email", example="student@example.com", description="Email address of the student"),
 *     @OA\Property(property="contact_number", type="string", format="string", example="+1234567890", description="Contact number of the student"),
 *     @OA\Property(property="sex", type="string", format="string", example="Male", description="Sex of the student"),
 *     @OA\Property(property="address", type="string", format="string", example="123 Main St, Apartment 4B", description="Address of the student"),
 *     @OA\Property(property="country", type="string", format="string", example="United States", description="Country of the student's address"),
 *     @OA\Property(property="city", type="string", format="string", example="New York", description="City of the student's address"),
 *     @OA\Property(property="postcode", type="string", format="string", example="10001", description="Postal code of the student's address"),
 *     @OA\Property(property="lat", type="string", format="string", example="40.712776", description="Latitude of the student's address"),
 *     @OA\Property(property="long", type="string", format="string", example="-74.005974", description="Longitude of the student's address"),
 *     @OA\Property(property="emergency_contact_details", type="object", example={"name": "John Doe", "relation": "Father", "contact": "+1234567890"}, description="Emergency contact details of the student"),
 *     @OA\Property(property="previous_education_history", type="array", @OA\Items(type="object", example={"institution": "High School", "year": "2019", "grade": "A"}), description="Previous education history of the student"),
 *     @OA\Property(property="passport_issue_date", type="string", format="date", example="2020-01-01", description="Passport issue date of the student"),
 *     @OA\Property(property="passport_expiry_date", type="string", format="date", example="2030-01-01", description="Passport expiry date of the student"),
 *     @OA\Property(property="place_of_issue", type="string", format="string", example="New York, USA", description="Place where the student's passport was issued")
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

    public function createStudentClient(StudentCreateRequestClient $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {


                $request_data = $request->validated();


                $request_data["is_active"] = true;
                $request_data["course_fee"] = 0;
                $request_data["fee_paid"] = 0;
                $request_data["course_start_date"] = "1970-01-01";



                $request_data["student_id"] = $this->generateUniqueId(Business::class, $request_data["business_id"], Student::class, 'student_id');

                $business_setting = BusinessSetting::where([
                    "business_id" => $request_data["business_id"]
                ])
                ->first();

                if(!empty($business_setting) && !empty($business_setting->online_student_status_id)) {
                    $request_data["student_status_id"] = $business_setting->online_student_status_id;
                } else {
                    $request_data["student_status_id"] = NULL;
                }

                $student =  Student::create($request_data);

                $business = $student->business;

                $request_data["previous_education_history"] = json_decode($request_data["previous_education_history"],true);

                if (isset($request_data["previous_education_history"]["student_docs"])) {
                    $request_data["previous_education_history"]["student_docs"] = $this->storeUploadedFiles(
                        $request_data["previous_education_history"]["student_docs"],
                        "file_name",
                        "student_docs",
                        NULL,
                        $student->id
                    );
                } else {
                    $request_data["previous_education_history"]["student_docs"] = [];
                }



                $student->previous_education_history = $request_data["previous_education_history"];
                $student->save();


                $response = [
                  "id" => $student->id,
                  "student_id" => $student->student_id,
                  "business_name" => $business->name,
                "student_full_name" => trim(($student->title ?? '') . ' ' . ($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),

                  "business_email" => $business->email,


                ];

                return response($response, 201);
            });
        } catch (Exception $e) {




            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     * @OA\Put(
     *      path="/v1.0/students",
     *      operationId="updateStudent",
     *      tags={"students"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update student ",
     *      description="This method is to update student",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
*      @OA\Property(property="id", type="number", format="number", example="Updated Christmas"),
*     @OA\Property(property="title", type="string", format="string", example="title"),
*     @OA\Property(property="first_name", type="string", format="string", example="John"),

 *     @OA\Property(property="middle_name", type="string", format="string", example=""),
 *     @OA\Property(property="last_name", type="string", format="string", example="Doe"),
 *     @OA\Property(property="nationality", type="string", format="string", example="Country"),
 *  * *     @OA\Property(property="course_fee", type="string", format="string", example="Country"),
 * *     @OA\Property(property="fee_paid", type="string", format="string", example="Country"),

 *     @OA\Property(property="passport_number", type="string", format="string", example="ABC123"),
 *     @OA\Property(property="student_id", type="string", format="string", example="School123"),
 *     @OA\Property(property="date_of_birth", type="string", format="date", example="2000-01-01"),
 *     @OA\Property(property="course_start_date", type="string", format="date", example="2024-01-31"),
 *     @OA\Property(property="letter_issue_date", type="string", format="date", example="2024-02-01"),
 *     @OA\Property(property="student_status_id", type="number", format="number", example=1),
 *  *  *     @OA\Property(property="course_title_id", type="number", format="number", example=1),
 *   @OA\Property(property="attachments", type="string", format="array", example={"/abcd.jpg","/efgh.jpg"}),
 *       * *     @OA\Property(property="course_duration", type="string", format="email", example="course_duration", description="course_duration"),
             *  * *     @OA\Property(property="course_detail", type="string", format="email", example="course_detail", description="course_duration"),
             *
 * *     @OA\Property(property="email", type="string", format="email", example="student@example.com", description="Email address of the student"),
 *     @OA\Property(property="contact_number", type="string", format="string", example="+1234567890", description="Contact number of the student"),
 *     @OA\Property(property="sex", type="string", format="string", example="Male", description="Sex of the student"),
 *     @OA\Property(property="address", type="string", format="string", example="123 Main St, Apartment 4B", description="Address of the student"),
 *     @OA\Property(property="country", type="string", format="string", example="United States", description="Country of the student's address"),
 *     @OA\Property(property="city", type="string", format="string", example="New York", description="City of the student's address"),
 *     @OA\Property(property="postcode", type="string", format="string", example="10001", description="Postal code of the student's address"),
 *     @OA\Property(property="lat", type="string", format="string", example="40.712776", description="Latitude of the student's address"),
 *     @OA\Property(property="long", type="string", format="string", example="-74.005974", description="Longitude of the student's address"),
 *     @OA\Property(property="emergency_contact_details", type="object", example={"name": "John Doe", "relation": "Father", "contact": "+1234567890"}, description="Emergency contact details of the student"),
 *     @OA\Property(property="previous_education_history", type="array", @OA\Items(type="object", example={"institution": "High School", "year": "2019", "grade": "A"}), description="Previous education history of the student"),
 *     @OA\Property(property="passport_issue_date", type="string", format="date", example="2020-01-01", description="Passport issue date of the student"),
 *     @OA\Property(property="passport_expiry_date", type="string", format="date", example="2030-01-01", description="Passport expiry date of the student"),
 *     @OA\Property(property="place_of_issue", type="string", format="string", example="New York, USA", description="Place where the student's passport was issued")

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

    public function updateStudent(StudentUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('student_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $business_id =  $request->user()->business_id;
                $request_data = $request->validated();




                $student_query_params = [
                    "id" => $request_data["id"],
                    "business_id" => $business_id
                ];
                // $student_prev = Student::where($student_query_params)
                //     ->first();
                // if (!$student_prev) {
                //     return response()->json([
                //         "message" => "no student found"
                //     ], 404);
                // }



                $student  =  tap(Student::where($student_query_params))->update(
                    collect($request_data)->only([
        'first_name',
        "title",
        'middle_name',
        'last_name',
        'nationality',
        "course_fee",
        "fee_paid",
        'passport_number',
        'student_id',
        'date_of_birth',
        'course_start_date',
        'course_end_date',
        'level',
        'letter_issue_date',
        'student_status_id',
        "course_title_id",
        'attachments',
        'course_duration',
        'course_detail',
        'email',
        'contact_number',
        'sex',
        'address',
        'country',
        'city',
        'postcode',
        'lat',
        'long',
        'emergency_contact_details',
        // 'previous_education_history',
        'passport_issue_date',
        'passport_expiry_date',
        'place_of_issue',

                        // "is_active",
                        // "business_id",
                        // "created_by"

                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();
                if (!$student) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }
                $request_data["previous_education_history"] = json_decode($request_data["previous_education_history"],true);


                if (isset($request_data["previous_education_history"]["student_docs"])) {
                    $request_data["previous_education_history"]["student_docs"] = $this->storeUploadedFiles(
                        $request_data["previous_education_history"]["student_docs"],
                        "file_name",
                        "student_docs",
                        NULL,
                        $student->id
                    );
                    $newDocs = $request_data["previous_education_history"]["student_docs"];

                    $existing_previous_education_history = $student->previous_education_history;

                    // Compare and delete old files if necessary
                    $existingDocs = $existing_previous_education_history["student_docs"] ?? [];

                    foreach ($existingDocs as $existingDoc) {
                        $found=false;
                        foreach ($newDocs as $newDoc) {
                            if ($existingDoc["id"] == $newDoc["id"]) {
                                $found=true;

                                if($existingDoc["file_name"] !== $newDoc["file_name"]) {
                                    $filePath = public_path(("/" . str_replace(' ', '_', $student->business->name) . "/" . base64_encode($student->id) . "/student_docs/".  $existingDoc["file_name"]));

                                    if (File::exists($filePath)) {
                                        File::delete($filePath);
                                    }
                                }
     break; // No need to check further once found
                            }
                        }

                        if(!$found) {
                            $filePath = public_path(("/" . str_replace(' ', '_', $student->business->name) . "/" . base64_encode($student->id) . "/student_docs/".  $existingDoc["file_name"]));
                            if (File::exists($filePath)) {
                                File::delete($filePath);
                            }
                        }
                    }

                } else {
                    $request_data["previous_education_history"]["student_docs"] = [];
                }





                $student->previous_education_history = $request_data["previous_education_history"];

                $student->save();

                return response($student, 201);
            });
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

 /**
     *
     * @OA\Get(
     *      path="/v1.0/students/validate/school-id/{student_id}",
     *      operationId="validateStudentId",
     *      tags={"students"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *              @OA\Parameter(
     *         name="student_id",
     *         in="path",
     *         description="student_id",
     *         required=true,
     *  example="1"
     *      ),

     *      summary="This method is to validate student id",
     *      description="This method is to validate student id",
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
    public function validateStudentId($student_id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $student_id_exists =  Student::where(
                [
                    'student_id' => $student_id,
                    "business_id" => $request->user()->business_id
                ]
            )->exists();

            return response()->json(["student_id_exists" => $student_id_exists], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    public function query_filters_v2($query)
    {
        $business_id =  auth()->user()->business_id;

            $business_setting = BusinessSetting::where([
                "business_id" => auth()->user()->business_id
            ])
            ->first();
        return   $query->where(
            [
                "students.business_id" => $business_id
            ]
        )
        ->when(!empty(request()->id), function ($query)  {
            return $query->where('students.id',request()->id);
        })

        ->when(!empty(request()->nationality), function ($query)  {
            return $query->where('students.nationality', request()->nationality);
        })
        ->when(!empty(request()->letter_issue_start_date), function ($query)  {
            return $query->where('students.letter_issue_date', '>=', request()->letter_issue_start_date);
        })
        ->when(!empty(request()->letter_issue_end_date), function ($query) {
            return $query->where('students.letter_issue_date', '<=', request()->letter_issue_end_date . ' 23:59:59');
        })
        ->when(!empty(request()->fee_paid_min), function ($query)  {
            return $query->where('students.fee_paid', '>=', request()->fee_paid_min);
        })
        ->when(!empty(request()->fee_paid_max), function ($query)  {
            return $query->where('students.fee_paid', '<=', request()->fee_paid_max);
        })

        ->when(!empty(request()->course_start_date_start_date), function ($query)  {
            return $query->where('students.course_start_date', '>=', request()->course_start_date_start_date);
        })
        ->when(!empty(request()->course_start_date_end_date), function ($query)  {
            return $query->where('students.course_start_date', '<=', request()->course_start_date_end_date . ' 23:59:59');
        })
        ->when(!empty(request()->course_end_date_start_date), function ($query)  {
            return $query->where('students.course_end_date', '>=', request()->course_end_date_start_date);
        })
        ->when(!empty(request()->course_end_date_end_date), function ($query)  {
            return $query->where('students.course_end_date', '<=', request()->course_end_date_end_date . ' 23:59:59');
        })

        ->when(!empty(request()->title), function ($query)  {
            return $query->where('students.title',request()->title);
        })
        ->when(!empty(request()->first_name), function ($query)  {
            return $query->where('students.first_name',request()->first_name);
        })
        ->when(!empty(request()->middle_name), function ($query)  {
            return $query->where('students.middle_name',request()->middle_name);
        })
        ->when(!empty(request()->last_name), function ($query)  {
            return $query->where('students.last_name',request()->last_name);
        })
        ->when(!empty(request()->name), function ($query) {
            return $query->where(function ($query) {
                $terms = explode(' ', request()->name); // Split the input into individual words
                foreach ($terms as $term) {
                    $query
                    ->orWhere('students.title', 'like', '%' . $term . '%')
                    ->orWhere('students.first_name', 'like', '%' . $term . '%')
                          ->orWhere('students.middle_name', 'like', '%' . $term . '%')
                          ->orWhere('students.last_name', 'like', '%' . $term . '%');
                }
            });
        })
            ->when(!empty(request()->search_key), function ($query)  {
                return $query->where(function ($query)  {
                    $term = request()->search_key;
                    $query->where("students.title", "like", "%" . $term . "%")
                        ->orWhere("students.first_name", "like", "%" . $term . "%")
                        ->orWhere("students.middle_name", "like", "%" . $term . "%")
                        ->orWhere("students.last_name", "like", "%" . $term . "%")
                        ->orWhere("students.nationality", "like", "%" . $term . "%")
                        ->orWhere("students.passport_number", "like", "%" . $term . "%")
                        ->orWhere("students.student_id", "like", "%" . $term . "%")
                        ->orWhere("students.date_of_birth", "like", "%" . $term . "%");
                });
            })
            //    ->when(!empty(request()->product_category_id), function ($query) use (request()) {
            //        return $query->where('product_category_id', request()->product_category_id);
            //    })
            ->when(!empty(request()->start_date), function ($query)  {
                return $query->where('students.created_at', ">=", request()->start_date);
            })
            ->when(!empty(request()->end_date), function ($query)  {
                return $query->where('students.created_at', "<=", (request()->end_date . ' 23:59:59'));
            })
            ->when(!empty(request()->student_status_id), function ($query)  {
                return $query->where('students.student_status_id',request()->student_status_id);
            })
            ->when(
                request()->boolean("is_online_registered"),
                function ($query) use ($business_setting) {
                    // When online registration is requested, check if 'student_status_id' is NULL
                    $query->where(function($query) use ($business_setting) {
                        $query->whereNull('students.student_status_id')
                            // Apply online status condition if business setting exists
                            ->when(!empty($business_setting) && !empty($business_setting->online_student_status_id), function($query) use ($business_setting) {
                                $query->orWhere('students.student_status_id', $business_setting->online_student_status_id);
                            });
                    });
                },
                function ($query) use($business_setting) {
                    // When offline registration is requested, check if 'student_status_id' is NOT NULL
                    $query
                    ->whereNotNull('students.student_status_id')
                    ->when(!empty($business_setting) && !empty($business_setting->online_student_status_id), function($query) use ($business_setting) {
                        $query->whereNotIn('students.student_status_id', [$business_setting->online_student_status_id]);
                    })
                    ;
                }
            )

            ->when(!empty(request()->course_title_id), function ($query) {
                return $query->where('students.course_title_id',request()->course_title_id);
            })
            ->when(!empty(request()->date_of_birth), function ($query)  {
                return $query->where('students.date_of_birth',request()->date_of_birth);
            })
            ->when(!empty(request()->student_id), function ($query)  {
                return $query->whereRaw('BINARY students.student_id = ?', [request()->student_id]);
            });
    }
   /**
     *
     * @OA\Get(
     *      path="/v1.0/students",
     *      operationId="getStudents",
     *      tags={"students"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
* @OA\Parameter(
 *     name="id",
 *     in="query",
 *     description="Filter by student ID",
 *     required=false,
 *     example="123"
 * ),
 * @OA\Parameter(
 *     name="nationality",
 *     in="query",
 *     description="Filter by student's nationality",
 *     required=false,
 *     example="Bangladeshi"
 * ),
 * @OA\Parameter(
 *     name="letter_issue_start_date",
 *     in="query",
 *     description="Filter by letter issue start date (YYYY-MM-DD)",
 *     required=false,
 *     example="2024-01-01"
 * ),
 * @OA\Parameter(
 *     name="letter_issue_end_date",
 *     in="query",
 *     description="Filter by letter issue end date (YYYY-MM-DD)",
 *     required=false,
 *     example="2024-12-31"
 * ),
 * @OA\Parameter(
 *     name="fee_paid_min",
 *     in="query",
 *     description="Minimum fee paid",
 *     required=false,
 *     example="1000"
 * ),
 * @OA\Parameter(
 *     name="fee_paid_max",
 *     in="query",
 *     description="Maximum fee paid",
 *     required=false,
 *     example="5000"
 * ),
 * @OA\Parameter(
 *     name="course_start_date_start_date",
 *     in="query",
 *     description="Filter by course start date (start range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-01-01"
 * ),
 * @OA\Parameter(
 *     name="course_start_date_end_date",
 *     in="query",
 *     description="Filter by course start date (end range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-12-31"
 * ),
 * @OA\Parameter(
 *     name="course_end_date_start_date",
 *     in="query",
 *     description="Filter by course end date (start range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-01-01"
 * ),
 * @OA\Parameter(
 *     name="course_end_date_end_date",
 *     in="query",
 *     description="Filter by course end date (end range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-12-31"
 * ),
 *
 *  * @OA\Parameter(
 *     name="title",
 *     in="query",
 *     description="Filter by student's title",
 *     required=false,
 *     example="John"
 * ),
 * @OA\Parameter(
 *     name="first_name",
 *     in="query",
 *     description="Filter by student's first name",
 *     required=false,
 *     example="John"
 * ),
 * @OA\Parameter(
 *     name="middle_name",
 *     in="query",
 *     description="Filter by student's middle name",
 *     required=false,
 *     example="Paul"
 * ),
 * @OA\Parameter(
 *     name="last_name",
 *     in="query",
 *     description="Filter by student's last name",
 *     required=false,
 *     example="Doe"
 * ),
 * @OA\Parameter(
 *     name="name",
 *     in="query",
 *     description="Filter by student's name (loose search)",
 *     required=false,
 *     example="John Paul"
 * ),
 * @OA\Parameter(
 *     name="search_key",
 *     in="query",
 *     description="Global search across multiple fields",
 *     required=false,
 *     example="passport123"
 * ),
 * @OA\Parameter(
 *     name="start_date",
 *     in="query",
 *     description="Filter by creation date (start range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-01-01"
 * ),
 * @OA\Parameter(
 *     name="end_date",
 *     in="query",
 *     description="Filter by creation date (end range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-12-31"
 * ),
 * @OA\Parameter(
 *     name="student_status_id",
 *     in="query",
 *     description="Filter by student status ID",
 *     required=false,
 *     example="5"
 * ),
 * @OA\Parameter(
 *     name="is_online_registered",
 *     in="query",
 *     description="Filter by online or offline registration",
 *     required=false,
 *     example="true"
 * ),
 * @OA\Parameter(
 *     name="course_title_id",
 *     in="query",
 *     description="Filter by course title ID",
 *     required=false,
 *     example="10"
 * ),
 * @OA\Parameter(
 *     name="date_of_birth",
 *     in="query",
 *     description="Filter by date of birth (YYYY-MM-DD)",
 *     required=false,
 *     example="2000-01-01"
 * ),
 * @OA\Parameter(
 *     name="student_id",
 *     in="query",
 *     description="Filter by school ID (case sensitive)",
 *     required=false,
 *     example="SCH123"
 * ),
 * @OA\Parameter(
 *     name="order_by",
 *     in="query",
 *     description="Sort order by ID (ASC or DESC)",
 *     required=false,
 *     example="ASC"
 * ),
 * @OA\Parameter(
 *     name="is_single_search",
 *     in="query",
 *     description="Return a single result instead of paginated results",
 *     required=false,
 *     example="true"
 * ),
 * @OA\Parameter(
 *     name="per_page",
 *     in="query",
 *     description="Number of results per page",
 *     required=false,
 *     example="20"
 * ),



     *      summary="This method is to get students  ",
     *      description="This method is to get students ",
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

     public function getStudents(Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");
             if (!$request->user()->hasPermissionTo('student_update')) {
                 return response()->json([
                     "message" => "You can not perform this action"
                 ], 401);
             }

             $query = Student::with("student_status","course_title");
             $query = $this->query_filters_v2($query);
             $students = $this->retrieveData($query, "id","students");

             return response()->json($students, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }

    /**
     *
     * @OA\Get(
     *      path="/v2.0/students",
     *      operationId="getStudentsV2",
     *      tags={"students"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
* @OA\Parameter(
 *     name="id",
 *     in="query",
 *     description="Filter by student ID",
 *     required=false,
 *     example="123"
 * ),
 * @OA\Parameter(
 *     name="nationality",
 *     in="query",
 *     description="Filter by student's nationality",
 *     required=false,
 *     example="Bangladeshi"
 * ),
 * @OA\Parameter(
 *     name="letter_issue_start_date",
 *     in="query",
 *     description="Filter by letter issue start date (YYYY-MM-DD)",
 *     required=false,
 *     example="2024-01-01"
 * ),
 * @OA\Parameter(
 *     name="letter_issue_end_date",
 *     in="query",
 *     description="Filter by letter issue end date (YYYY-MM-DD)",
 *     required=false,
 *     example="2024-12-31"
 * ),
 * @OA\Parameter(
 *     name="fee_paid_min",
 *     in="query",
 *     description="Minimum fee paid",
 *     required=false,
 *     example="1000"
 * ),
 * @OA\Parameter(
 *     name="fee_paid_max",
 *     in="query",
 *     description="Maximum fee paid",
 *     required=false,
 *     example="5000"
 * ),
 * @OA\Parameter(
 *     name="course_start_date_start_date",
 *     in="query",
 *     description="Filter by course start date (start range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-01-01"
 * ),
 * @OA\Parameter(
 *     name="course_start_date_end_date",
 *     in="query",
 *     description="Filter by course start date (end range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-12-31"
 * ),
 * @OA\Parameter(
 *     name="course_end_date_start_date",
 *     in="query",
 *     description="Filter by course end date (start range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-01-01"
 * ),
 * @OA\Parameter(
 *     name="course_end_date_end_date",
 *     in="query",
 *     description="Filter by course end date (end range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-12-31"
 * ),
 *
 *  * @OA\Parameter(
 *     name="title",
 *     in="query",
 *     description="Filter by student's title",
 *     required=false,
 *     example="John"
 * ),
 * @OA\Parameter(
 *     name="first_name",
 *     in="query",
 *     description="Filter by student's first name",
 *     required=false,
 *     example="John"
 * ),
 * @OA\Parameter(
 *     name="middle_name",
 *     in="query",
 *     description="Filter by student's middle name",
 *     required=false,
 *     example="Paul"
 * ),
 * @OA\Parameter(
 *     name="last_name",
 *     in="query",
 *     description="Filter by student's last name",
 *     required=false,
 *     example="Doe"
 * ),
 * @OA\Parameter(
 *     name="name",
 *     in="query",
 *     description="Filter by student's name (loose search)",
 *     required=false,
 *     example="John Paul"
 * ),
 * @OA\Parameter(
 *     name="search_key",
 *     in="query",
 *     description="Global search across multiple fields",
 *     required=false,
 *     example="passport123"
 * ),
 * @OA\Parameter(
 *     name="start_date",
 *     in="query",
 *     description="Filter by creation date (start range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-01-01"
 * ),
 * @OA\Parameter(
 *     name="end_date",
 *     in="query",
 *     description="Filter by creation date (end range, YYYY-MM-DD)",
 *     required=false,
 *     example="2024-12-31"
 * ),
 * @OA\Parameter(
 *     name="student_status_id",
 *     in="query",
 *     description="Filter by student status ID",
 *     required=false,
 *     example="5"
 * ),
 * @OA\Parameter(
 *     name="is_online_registered",
 *     in="query",
 *     description="Filter by online or offline registration",
 *     required=false,
 *     example="true"
 * ),
 * @OA\Parameter(
 *     name="course_title_id",
 *     in="query",
 *     description="Filter by course title ID",
 *     required=false,
 *     example="10"
 * ),
 * @OA\Parameter(
 *     name="date_of_birth",
 *     in="query",
 *     description="Filter by date of birth (YYYY-MM-DD)",
 *     required=false,
 *     example="2000-01-01"
 * ),
 * @OA\Parameter(
 *     name="student_id",
 *     in="query",
 *     description="Filter by school ID (case sensitive)",
 *     required=false,
 *     example="SCH123"
 * ),
 * @OA\Parameter(
 *     name="order_by",
 *     in="query",
 *     description="Sort order by ID (ASC or DESC)",
 *     required=false,
 *     example="ASC"
 * ),
 * @OA\Parameter(
 *     name="is_single_search",
 *     in="query",
 *     description="Return a single result instead of paginated results",
 *     required=false,
 *     example="true"
 * ),
 * @OA\Parameter(
 *     name="per_page",
 *     in="query",
 *     description="Number of results per page",
 *     required=false,
 *     example="20"
 * ),



     *      summary="This method is to get students  ",
     *      description="This method is to get students ",
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

    public function getStudentsV2(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('student_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $query = Student::with("student_status","course_title");
            $query = $this->query_filters_v2($query)
            ->select(
                "students.id",
                "students.title",
                'students.first_name',
                'students.middle_name',
                'students.last_name',
                "students.student_id",
                'students.nationality',
                "students.course_fee",
                "students.fee_paid",
                'students.passport_number',
                'students.date_of_birth',
                'students.course_start_date',
                'students.course_end_date',
                'students.level',
                'students.letter_issue_date',
                'students.student_status_id',
                "students.course_title_id",
                'students.attachments',
                'students.course_duration',
                'students.course_detail',
                'students.email',
                'students.contact_number',
                'students.sex',
                'students.address',
                'students.country',
                'students.city',
                'students.postcode',
                'students.lat',
                'students.long',
                'students.emergency_contact_details',
                'students.previous_education_history',
                'students.passport_issue_date',
                'students.passport_expiry_date',
                'students.place_of_issue',
                'students.is_active',

            );
            $students = $this->retrieveData($query, "id","students");

            return response()->json($students, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    public function query_filters($query)
    {
        $business_id =  request()->business_id;
        if(!$business_id) {
           $error = [ "message" => "The given data was invalid.",
           "errors" => ["business_id"=>["The business id field is required."]]
           ];
               throw new Exception(json_encode($error),422);
        }

        return   $query->when(request()->filled("business_id"), function($query) {
            $query->where(
                [
                    "students.business_id" => request()->input("business_id")
                ]
                );
         })

         ->when(!empty(request()->id), function ($query)  {
            return $query->where('students.id',request()->id);
        })


        ->when(!empty(request()->title), function ($query)  {
            return $query->where('students.title',request()->title);
        })
        ->when(!empty(request()->first_name), function ($query)  {
            return $query->where('students.first_name',request()->first_name);
        })

        ->when(!empty(request()->middle_name), function ($query)  {
            return $query->where('students.middle_name',request()->middle_name);
        })
        ->when(!empty(request()->last_name), function ($query)  {
            return $query->where('students.last_name',request()->last_name);
        })

             ->when(!empty(request()->search_key), function ($query) {
                 return $query->where(function ($query) {
                     $term = request()->search_key;


                     $query->where("students.title", "like", "%" . $term . "%")
                     ->orWhere("students.first_name", "like", "%" . $term . "%")
                         ->orWhere("students.middle_name", "like", "%" . $term . "%")
                         ->orWhere("students.last_name", "like", "%" . $term . "%")
                         ->orWhere("students.nationality", "like", "%" . $term . "%")
                         ->orWhere("students.passport_number", "like", "%" . $term . "%")
                         ->orWhere("students.student_id", "like", "%" . $term . "%")
                         ->orWhere("students.date_of_birth", "like", "%" . $term . "%");
                 });
             })
             //    ->when(!empty(request()->product_category_id), function ($query) use (request()) {
             //        return $query->where('product_category_id', request()->product_category_id);
             //    })
             ->when(!empty(request()->start_date), function ($query) {
                 return $query->where('students.created_at', ">=", request()->start_date);
             })
             ->when(!empty(request()->end_date), function ($query) {
                 return $query->where('students.created_at', "<=", (request()->end_date . ' 23:59:59'));
             })
             ->when(!empty(request()->student_status_id), function ($query)  {
                 return $query->where('students.student_status_id',request()->student_status_id);
             })
             ->when(!empty(request()->course_title_id), function ($query)  {
                return $query->where('students.course_title_id',request()->course_title_id);
            })
             ->when(!empty(request()->date_of_birth), function ($query) {
                 return $query->where('students.date_of_birth',request()->date_of_birth);
             })
             ->when(!empty(request()->student_id), function ($query) {
                return $query->whereRaw('BINARY students.student_id = ?', [request()->student_id]);
            });
    }

 /**
     *
     * @OA\Get(
     *      path="/v1.0/client/students",
     *      operationId="getStudentsClient",
     *      tags={"students"},
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
     *    *      * *  @OA\Parameter(
     * name="student_status_id",
     * in="query",
     * description="student_status_id",
     * required=true,
     * example="1"
     * ),
     *  *    *      * *  @OA\Parameter(
     * name="course_title_id",
     * in="query",
     * description="course_title_id",
     * required=true,
     * example="1"
     * ),
     * *   * *  @OA\Parameter(
     * name="student_id",
     * in="query",
     * description="student_id",
     * required=true,
     * example="412cbhg"
     * ),
     *   * *  @OA\Parameter(
     * name="date_of_birth",
     * in="query",
     * description="date_of_birth",
     * required=true,
     * example="ASC"
     * ),

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
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     *      * * *  @OA\Parameter(
     * name="is_single_search",
     * in="query",
     * description="is_single_search",
     * required=true,
     * example="ASC"
     * ),
     *    * * *  @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=true,
     * example="id"
     * ),
     *
     *  *     @OA\Parameter(
     * name="title",
     * in="query",
     * description="title",
     * required=true,
     * example="title"
     * ),
     *     @OA\Parameter(
     * name="first_name",
     * in="query",
     * description="first_name",
     * required=true,
     * example="first_name"
     * ),
     *    @OA\Parameter(
     * name="middle_name",
     * in="query",
     * description="middle_name",
     * required=true,
     * example="middle_name"
     * ),
     *    *    @OA\Parameter(
     * name="last_name",
     * in="query",
     * description="last_name",
     * required=true,
     * example="last_name"
     * ),
     *   *    *    @OA\Parameter(
     * name="business_id",
     * in="query",
     * description="business_id",
     * required=true,
     * example="business_id"
     * ),
     *


     *      summary="This method is to get students  ",
     *      description="This method is to get students ",
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

     public function getStudentsClient(Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");
            //  if (!$request->user()->hasPermissionTo('student_update')) {
            //      return response()->json([
            //          "message" => "You can not perform this action"
            //      ], 401);
            //  } test

            $query = Student::with("student_status","course_title");
            $query = $this->query_filters($query);
            $students = $this->retrieveData($query, "id","students");



             return response()->json($students, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }

  /**
     *
     * @OA\Get(
     *      path="/v2.0/client/students",
     *      operationId="getStudentsClientV2",
     *      tags={"students"},
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
     *    *      * *  @OA\Parameter(
     * name="student_status_id",
     * in="query",
     * description="student_status_id",
     * required=true,
     * example="1"
     * ),
     *  *    *      * *  @OA\Parameter(
     * name="course_title_id",
     * in="query",
     * description="course_title_id",
     * required=true,
     * example="1"
     * ),
     * *   * *  @OA\Parameter(
     * name="student_id",
     * in="query",
     * description="student_id",
     * required=true,
     * example="412cbhg"
     * ),
     *   * *  @OA\Parameter(
     * name="date_of_birth",
     * in="query",
     * description="date_of_birth",
     * required=true,
     * example="ASC"
     * ),

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
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     *      * * *  @OA\Parameter(
     * name="is_single_search",
     * in="query",
     * description="is_single_search",
     * required=true,
     * example="ASC"
     * ),
     *    * * *  @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=true,
     * example="id"
     * ),
     *
     *  *     @OA\Parameter(
     * name="title",
     * in="query",
     * description="title",
     * required=true,
     * example="title"
     * ),
     *     @OA\Parameter(
     * name="first_name",
     * in="query",
     * description="first_name",
     * required=true,
     * example="first_name"
     * ),
     *    @OA\Parameter(
     * name="middle_name",
     * in="query",
     * description="middle_name",
     * required=true,
     * example="middle_name"
     * ),
     *    *    @OA\Parameter(
     * name="last_name",
     * in="query",
     * description="last_name",
     * required=true,
     * example="last_name"
     * ),
     *   *    *    @OA\Parameter(
     * name="business_id",
     * in="query",
     * description="business_id",
     * required=true,
     * example="business_id"
     * ),
     *


     *      summary="This method is to get students  ",
     *      description="This method is to get students ",
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

     public function getStudentsClientV2(Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");
            //  if (!$request->user()->hasPermissionTo('student_update')) {
            //      return response()->json([
            //          "message" => "You can not perform this action"
            //      ], 401);
            //  } test

            $query = Student::with(
                [
                    "student_status" => function($query) {
                    $query->select("student_statuses.id","student_statuses.name");
                },
                "course_title"  => function($query) {
                    $query->select("course_titles.id","course_titles.name");
                }
                ]
            );
            $query = $this->query_filters($query)
            ->select(
    "students.id",
    "students.title",
    "students.first_name",
    "students.middle_name",
    "students.last_name",
    "students.student_id",
    "students.course_fee",
    "students.fee_paid",
    "students.date_of_birth",
    "students.course_start_date"
            );
            $students = $this->retrieveData($query, "id","students");



             return response()->json($students, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }




    /**
     *
     * @OA\Get(
     *      path="/v1.0/students/{id}",
     *      operationId="getStudentById",
     *      tags={"students"},
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
     *      summary="This method is to get student by id",
     *      description="This method is to get student by id",
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


    public function getStudentById($id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('student_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business_id =  $request->user()->business_id;

            $student =  Student:: with("student_status")
            ->where([
                "id" => $id,
                "business_id" => $business_id
            ])
                ->first();
            if (!$student) {
                $this->storeError(
                    "no data found"
                    ,
                    404,
                    "front end error",
                    "front end error"
                   );
                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            if(!is_array($student->previous_education_history)) {
                $previous_education_history =   json_decode($student->previous_education_history,true);
            }

         
            foreach ($previous_education_history['student_docs'] as &$student_doc_object) {
                // Ensure each student_doc_object has a file_name property

                    // Modify the file_name by prepending business name and student ID
                    $student_doc_object["file_name"] = "/" . str_replace(' ', '_', $student->business->name) . "/" . base64_encode($student->id) . "/student_docs/".  $student_doc_object["file_name"];
            }
            $student->previous_education_history = $previous_education_history;

            return response()->json($student, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


  /**
     *
     * @OA\Get(
     *      path="/v1.0/client/students/{id}",
     *      operationId="getStudentByIdClient",
     *      tags={"students"},
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
     *      summary="This method is to get student by id",
     *      description="This method is to get student by id",
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


     public function getStudentByIdClient($id, Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");

             $student =  Student:: with("student_status")
             ->where([
                 "id" => $id
             ])
                 ->first();
             if (!$student) {
                 $this->storeError(
                     "no data found"
                     ,
                     404,
                     "front end error",
                     "front end error"
                    );
                 return response()->json([
                     "message" => "no data found"
                 ], 404);
             }

             return response()->json($student, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }


    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/students/{ids}",
     *      operationId="deleteStudentsByIds",
     *      tags={"students"},
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
     *      summary="This method is to delete student by id",
     *      description="This method is to delete student by id",
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

    public function deleteStudentsByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('student_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business_id = $request->user()->business_id;


        $student = Student::with("business")
            ->where([
                "id" => $ids,
                "business_id" => $business_id
            ])
            ->first();

        if (!$student) {
            $this->storeError(
                "no data found",
                404,
                "front end error",
                "front end error"
            );
            return response()->json([
                "message" => "No data found"
            ], 404);
        }

             // Construct the folder path
        $businessFolderName = str_replace(' ', '_', $student->business->name);
        $studentFolderName = base64_encode($student->id); // Base64 encoding the student ID
        $folderPath = public_path("{$businessFolderName}/{$studentFolderName}");

        // Delete the student folder if it exists
        if (File::exists($folderPath)) {
            if (File::deleteDirectory($folderPath)) {
                Log::info("Folder {$folderPath} successfully deleted.");
            } else {
                Log::warning("Failed to delete folder {$folderPath}.");
            }
        }

       // Proceed with deleting the student record
       $student->delete();

            return response()->json(["message" => "data deleted sussfully"], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }






        /**
     *
     * @OA\Get(
     *      path="/v1.0/students/generate/student-id/{business_id}",
     *      operationId="generateStudentId",
     *      tags={"students"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     @OA\Parameter(
     *         name="business_id",
     *         in="path",
     *         description="business_id",
     *         required=true,
     *  example=""
     *      ),
     *
     *
     *      summary="This method is to generate student id",
     *      description="This method is to generate student id",
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
    public function generateStudentId($business_id,Request $request)
    {

        $studentId = $this->generateUniqueId(Business::class, $business_id, Student::class, 'student_id');

        return response()->json(["student_id" => $studentId], 200);
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/students/validate/student-id/{student_id}/{business_id}",
     *      operationId="validateStudentIdV2",
     *      tags={"unused_apis"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="student_id",
     *         in="path",
     *         description="student_id",
     *         required=true,
     *  example=""
     *      ),
     *  *              @OA\Parameter(
     *         name="business_id",
     *         in="path",
     *         description="business_id",
     *         required=true,
     *  example=""
     *      ),
     *    *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),


     *      summary="This method is to validate student id",
     *      description="This method is to validate student id",
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
    public function validateStudentIdV2($student_id,$business_id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $student_id_exists = DB::table('students')->where(
                [
                    'student_id' => $student_id,
                    "business_id" => $business_id
                ]
            )
                ->when(
                    !empty($request->id),
                    function ($query) use ($request) {
                        $query->whereNotIn("id", [$request->id]);
                    }
                )
                ->exists();


            return response()->json(["student_id_exists" => $student_id_exists], 200);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }









}
