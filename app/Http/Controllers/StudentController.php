<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentCreateRequest;
use App\Http\Requests\StudentUpdateRequest;
use App\Http\Requests\MultipleFileUploadRequest;
use App\Http\Requests\MultipleStudentFileUploadRequest;
use App\Http\Requests\StudentCourseUpdateRequest;
use App\Http\Requests\StudentCreateRequestClient;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Agency;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\Student;
use App\Models\StudentCourseSubject;
use App\Models\StudentDocument;
use App\Models\StudentSession;
use App\Models\StudentSessionCourse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PDF;

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
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

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
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
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
     * *  *     @OA\Property(property="session_id", type="number", format="number", example=1),
     *
     *     @OA\Property(property="attachments", type="string", format="array", example={"a.png","b.jpeg"}),

     * *     @OA\Property(property="course_duration", type="string", format="email", example="course_duration", description="course_duration"),
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
     *     @OA\Property(property="place_of_issue", type="string", format="string", example="New York, USA", description="Place where the student's passport was issued"),
     * *     @OA\Property(property="agency_id", type="string", format="string", example="agency_id"),
     *      @OA\Property(property="agency_commission", type="string", format="string", example="agency_commission"),
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
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
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


                $student = Student::create($request_data);

                $student_session = StudentSession::create([
                    'student_id' => $student->id,
                    'session_id' => $request_data["session_id"]
                ]);

                StudentSessionCourse::create([
                    'student_session_id' => $student_session->id,
                    'course_title_id' => $request_data["course_title_id"] ?? "",

                ]);


                $request_data["previous_education_history"] = json_decode($request_data["previous_education_history"], true);

                if (isset($request_data["previous_education_history"]["student_docs"])) {
                    $docs = $request_data["previous_education_history"]["student_docs"];

                    $updated_docs = [];

                    foreach ($docs as $doc) {
                        if (!empty($doc["file_name"])) {
                            $new_file_name = $this->storeUploadedFilesV2(
                                [$doc["file_name"]],
                                "student_docs",
                                $student->id
                            )[0]; // Only one file returned

                            $doc["file_name"] = $new_file_name;
                        }

                        $updated_docs[] = $doc;
                    }

                    $request_data["previous_education_history"]["student_docs"] = $updated_docs;
                } else {
                    $request_data["previous_education_history"]["student_docs"] = [];
                }



                $student->previous_education_history = $request_data["previous_education_history"];
                $student->save();


                $student_documents = $request_data['student_documents'] ?? [];

                foreach ($student_documents as $doc) {
                    $filenames = $doc['filenames']; // array of file paths (strings)

                    // Call the util method with array of strings, get back new array of file names (strings)
                    $new_filenames = $this->storeUploadedFilesV2($filenames, 'student_docs', $student->id);

                    StudentDocument::create([
                        'student_id' => $student->id,
                        'type' => $doc['type'],
                        'filenames' => $new_filenames,
                    ]);
                }


                if (!empty($request_data["agency_id"])) {
                    $student->referral()->create([
                        'agency_id' => $request_data["agency_id"],
                        'agency_commission' => $request_data["agency_commission"] // Assuming commission_rate is a percentage
                    ]);
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
     * *  *     @OA\Property(property="session_id", type="number", format="number", example=1),
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
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
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

                if (!empty($business_setting) && !empty($business_setting->online_student_status_id)) {
                    $request_data["student_status_id"] = $business_setting->online_student_status_id;
                } else {
                    $request_data["student_status_id"] = NULL;
                }

                $student =  Student::create($request_data);




                $business = $student->business;

                $request_data["previous_education_history"] = json_decode($request_data["previous_education_history"], true);

                if (isset($request_data["previous_education_history"]["student_docs"])) {
                    $docs = $request_data["previous_education_history"]["student_docs"];

                    $updated_docs = [];

                    foreach ($docs as $doc) {
                        if (!empty($doc["file_name"])) {
                            $new_file_name = $this->storeUploadedFilesV2(
                                [$doc["file_name"]],
                                "student_docs",
                                $student->id
                            )[0]; // Only one file returned

                            $doc["file_name"] = $new_file_name;
                        }

                        $updated_docs[] = $doc;
                    }

                    $request_data["previous_education_history"]["student_docs"] = $updated_docs;
                } else {
                    $request_data["previous_education_history"]["student_docs"] = [];
                }




                $student->previous_education_history = $request_data["previous_education_history"];
                $student->save();

                $student_documents = $request_data['student_documents'] ?? [];

                foreach ($student_documents as $doc) {
                    $filenames = $doc['filenames']; // array of file paths (strings)

                    // Call the util method with array of strings, get back new array of file names (strings)
                    $new_filenames = $this->storeUploadedFilesV2($filenames, 'student_docs', $student->id, $request_data["business_id"]);

                    StudentDocument::create([
                        'student_id' => $student->id,
                        'type' => $doc['type'],
                        'filenames' => $new_filenames,
                    ]);
                }

                if (!empty($request_data["agency_id"])) {
                    $student->referral()->create([
                        'agency_id' => $request_data["agency_id"],
                        'agency_commission' => 0 // Assuming commission_rate is a percentage
                    ]);
                }
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
     * @OA\Post(
     *      path="/v2.0/client/students",
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
     * *  *     @OA\Property(property="session_id", type="number", format="number", example=1),
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

    public function createStudentClientV2(StudentCreateRequestClient $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
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

                if (!empty($business_setting) && !empty($business_setting->online_student_status_id)) {
                    $request_data["student_status_id"] = $business_setting->online_student_status_id;
                } else {
                    $request_data["student_status_id"] = NULL;
                }

                $student =  Student::create($request_data);




                $business = $student->business;

                $request_data["previous_education_history"] = json_decode($request_data["previous_education_history"], true);

                if (isset($request_data["previous_education_history"]["student_docs"])) {
                    $docs = $request_data["previous_education_history"]["student_docs"];

                    $updated_docs = [];

                    foreach ($docs as $doc) {
                        if (!empty($doc["file_name"])) {
                            $new_file_name = $this->storeUploadedFilesV2(
                                [$doc["file_name"]],
                                "student_docs",
                                $student->id
                            )[0]; // Only one file returned

                            $doc["file_name"] = $new_file_name;
                        }

                        $updated_docs[] = $doc;
                    }

                    $request_data["previous_education_history"]["student_docs"] = $updated_docs;
                } else {
                    $request_data["previous_education_history"]["student_docs"] = [];
                }




                $student->previous_education_history = $request_data["previous_education_history"];
                $student->save();

                $student_documents = $request_data['student_documents'] ?? [];

                foreach ($student_documents as $doc) {
                    $filenames = $doc['filenames']; // array of file paths (strings)

                    // Call the util method with array of strings, get back new array of file names (strings)
                    $new_filenames = $this->storeUploadedFilesV2($filenames, 'student_docs', $student->id);

                    StudentDocument::create([
                        'student_id' => $student->id,
                        'type' => $doc['type'],
                        'filenames' => $new_filenames,
                    ]);
                }


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
     *  *  *  *     @OA\Property(property="session_id", type="number", format="number", example=1),
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
     *     @OA\Property(property="place_of_issue", type="string", format="string", example="New York, USA", description="Place where the student's passport was issued"),
     *  * *     @OA\Property(property="agency_id", type="string", format="string", example="agency_id"),
     *      @OA\Property(property="agency_commission", type="string", format="string", example="agency_commission"),

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
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
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

                $student  =  tap(Student::where($student_query_params))->update(
                    collect($request_data)->only([
                        'first_name',
                        "title",
                        'middle_name',
                        'last_name',
                        'nationality',

                        "course_fee",
                        "fee_paid",
                        'course_start_date',
                        'course_end_date',
                        "course_title_id",
                        "session_id",
                        'course_duration',
                        'course_detail',
                        'level',
                        'letter_issue_date',
                        'passport_number',
                        'student_id',
                        'date_of_birth',

                        'student_status_id',
                        'attachments',
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


                // First, check or create the student session
                $student_session = StudentSession::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'session_id' => $request_data["session_id"]
                    ]
                );

                // Then, only create the course entry if not already present
                $existing_course = StudentSessionCourse::where('student_session_id', $student_session->id)
                    ->where('course_title_id', $request_data["course_title_id"] ?? "")
                    ->exists();

                if (!$existing_course) {
                    StudentSessionCourse::create([
                        'student_session_id' => $student_session->id,
                        'course_title_id' => $request_data["course_title_id"] ?? "",
                    ]);
                }


                // For previous education history cleanup example:

                $request_data["previous_education_history"] = json_decode($request_data["previous_education_history"], true);

                if (isset($request_data["previous_education_history"]["student_docs"])) {
                    $docs = $request_data["previous_education_history"]["student_docs"];

                    $newDocs = [];

                    foreach ($docs as $doc) {
                        $old_file_name = $doc["file_name"] ?? null;

                        if ($old_file_name) {
                            // Move this single file using the simplified util
                            $new_file_names = $this->storeUploadedFilesV2([$old_file_name], "student_docs", $student->id);

                            // Update the doc with the new file name
                            $doc["file_name"] = $new_file_names[0]; // Only one file processed
                        }

                        $newDocs[] = $doc;
                    }

                    $request_data["previous_education_history"]["student_docs"] = $newDocs;

                    // Optional cleanup of old files
                    $existingDocs = $student->previous_education_history["student_docs"] ?? [];
                    $base_path = str_replace(' ', '_', $student->business->name) . "/" . base64_encode($student->id) . "/student_docs/";
                    $this->cleanupOldFiles($existingDocs, $newDocs, $base_path);
                } else {
                    $request_data["previous_education_history"]["student_docs"] = [];
                }


                $student->previous_education_history = $request_data["previous_education_history"];

                $student->save();

                $student_documents = $request_data['student_documents'] ?? [];
                StudentDocument::where('student_id', $student->id)->delete(); // Clear existing docs first

                foreach ($student_documents as $doc) {
                    $filenames = $doc['filenames']; // array of file paths (strings)
                    $new_filenames = $this->storeUploadedFilesV2($filenames, 'student_docs', $student->id);

                    StudentDocument::create([
                        'student_id' => $student->id,
                        'type' => $doc['type'],
                        'filenames' => $new_filenames,
                    ]);
                }

                $student->referral()->delete();
                if (!empty($request_data["agency_id"])) {
                    $student->referral()->create([
                        'agency_id' => $request_data["agency_id"],
                        'agency_commission' => $request_data["agency_commission"] // Assuming commission_rate is a percentage
                    ]);
                }

                return response($student, 201);
            });
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="/v1.0/students-courses",
     *      operationId="updateStudentCourses",
     *      tags={"students"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update student courses",
     *      description="This method is to update student courses",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id", "sessions"},
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="sessions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"session_id", "courses"},
     *                     @OA\Property(property="session_id", type="integer", example=2),
     *                     @OA\Property(
     *                         property="courses",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             required={"course_title_id", "subjects"},
     *                             @OA\Property(property="course_title_id", type="integer", example=3),
     *                             @OA\Property(
     *                                 property="subjects",
     *                                 type="array",
     *                                 @OA\Items(
     *                                     type="object",
     *                                     required={"subject_id"},
     *                                     @OA\Property(property="subject_id", type="integer", example=4)
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
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

    public function updateStudentCourses(StudentCourseUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('student_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();

                StudentSession::where([
                    "student_id" => $request_data["student_id"]
                ])->delete();


                foreach ($request_data["sessions"] as $request_session) {
                    $student_session = StudentSession::create([
                        'student_id' => $request_data["student_id"],
                        'session_id' => $request_session["session_id"]
                    ]);

                    foreach ($request_session["courses"] as $request_course) {
                        $student_session_course = StudentSessionCourse::create([
                            'student_session_id' => $student_session->id,
                            'course_title_id' => $request_course["course_title_id"],
                        ]);

                        foreach ($request_course["subjects"] as $request_subject) {
                            StudentCourseSubject::create([
                                'student_session_course_id' => $student_session_course->id,
                                'subject_id' => $request_subject["subject_id"]
                            ]);
                        }
                    }
                }




                return response(["ok" => true], 201);
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
        )->filterStudent($business_setting);
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
     *     name="course_id",
     *     in="query",
     *     description="Filter by course title ID",
     *     required=false,
     *     example="10"
     * ),
     *  * @OA\Parameter(
     *     name="session_id",
     *     in="query",
     *     description="Filter by course title ID",
     *     required=false,
     *     example="10"
     * ),
     *
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
     *  @OA\Parameter(
     *     name="response_type",
     *     in="query",
     *     description="response_type",
     *     required=false,
     *     example="20"
     * ),
     * @OA\Parameter(
     *     name="file_name",
     *     in="query",
     *     description="file_name",
     *     required=false,
     *     example="20"
     * ),
     *
     *
     *
     *
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

    public function getStudents(Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('student_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $query = Student::with("student_status", "course_title", "student_referral.agency", "session");
            $query = $this->query_filters_v2($query);

            $students = $this->retrieveData($query, "id", "students");


            if (!empty($request->response_type) && in_array(strtoupper($request->response_type), ['PDF', 'CSV'])) {
                if (strtoupper($request->response_type) == 'PDF') {

                    if (empty($students)) {
                        $pdf = PDF::loadView('pdf.no_data', []);
                    } else {
                        $pdf = PDF::loadView('pdf.students', ["students" => $students]);
                    }

                    return $pdf->download(((!empty($request->file_name) ? $request->file_name : 'students') . '.pdf'));
                } elseif (strtoupper($request->response_type) === 'CSV') {

                    return response()->json([
                        "message" => "CSV not supported currently"
                    ], 404);
                    // Excel::download(new AttendancesExport($attendances), ((!empty($request->file_name) ? $request->file_name : 'attendance') . '.csv'));
                }
            } else {
                return response()->json($students, 200);
            }

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
     *     name="course_id",
     *     in="query",
     *     description="Filter by course title ID",
     *     required=false,
     *     example="10"
     * ),
     *  * @OA\Parameter(
     *     name="session_id",
     *     in="query",
     *     description="Filter by course title ID",
     *     required=false,
     *     example="10"
     * ),
     *
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
     *  @OA\Parameter(
     *     name="response_type",
     *     in="query",
     *     description="response_type",
     *     required=false,
     *     example="20"
     * ),
     * @OA\Parameter(
     *     name="file_name",
     *     in="query",
     *     description="file_name",
     *     required=false,
     *     example="20"
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

    public function getStudentsV2(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('student_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $query = Student::with("student_status", "course_title", "session");
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
                    "students.session_id",

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
            $students = $this->retrieveData($query, "id", "students");

            if (!empty($request->response_type) && in_array(strtoupper($request->response_type), ['PDF', 'CSV'])) {
                if (strtoupper($request->response_type) == 'PDF') {
                    if (empty($students)) {
                        $pdf = PDF::loadView('pdf.no_data', []);
                    } else {
                        $pdf = PDF::loadView('pdf.students', ["students" => $students]);
                    }

                    return $pdf->download(((!empty($request->file_name) ? $request->file_name : 'students') . '.pdf'));
                } elseif (strtoupper($request->response_type) === 'CSV') {

                    return response()->json([
                        "message" => "CSV not supported currently"
                    ], 404);
                    // Excel::download(new AttendancesExport($attendances), ((!empty($request->file_name) ? $request->file_name : 'attendance') . '.csv'));
                }
            } else {
                return response()->json($students, 200);
            }

            return response()->json($students, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    public function query_filters($query)
    {
        $business_id =  request()->business_id;
        if (!$business_id) {
            $error = [
                "message" => "The given data was invalid.",
                "errors" => ["business_id" => ["The business id field is required."]]
            ];
            throw new Exception(json_encode($error), 422);
        }
        $business_setting = BusinessSetting::where([
            "business_id" => $business_id
        ])
            ->first();
        return   $query->when(request()->filled("business_id"), function ($query) {
            $query->where(
                [
                    "students.business_id" => request()->input("business_id")
                ]
            );
        })->filterStudent($business_setting);
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
     * name="course_id",
     * in="query",
     * description="course_id",
     * required=true,
     * example="1"
     * ),
     *  *  *    *      * *  @OA\Parameter(
     * name="session_id",
     * in="query",
     * description="session_id",
     * required=true,
     * example="1"
     * ),
     *
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
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            //  if (!$request->user()->hasPermissionTo('student_update')) {
            //      return response()->json([
            //          "message" => "You can not perform this action"
            //      ], 401);
            //  } test

            $query = Student::with("student_status", "course_title", "session");
            $query = $this->query_filters($query);

            $students = $this->retrieveData($query, "id", "students");


            return response()->json($students, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v3.0/client/students",
     *      operationId="getStudentsClientV3",
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
     * name="course_id",
     * in="query",
     * description="course_id",
     * required=true,
     * example="1"
     * ),
     *  *  *    *      * *  @OA\Parameter(
     * name="session_id",
     * in="query",
     * description="session_id",
     * required=true,
     * example="1"
     * ),
     *
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

    public function getStudentsClientV3(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            //  if (!$request->user()->hasPermissionTo('student_update')) {
            //      return response()->json([
            //          "message" => "You can not perform this action"
            //      ], 401);
            //  } test

            $query = Student::with("student_status", "course_title", "session");
            $query = $this->query_filters($query);

            $students = $query->first();


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
     * name="course_id",
     * in="query",
     * description="course_id",
     * required=true,
     * example="1"
     * ),
     *   *  *    *      * *  @OA\Parameter(
     * name="session_id",
     * in="query",
     * description="session_id",
     * required=true,
     * example="1"
     * ),
     *
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
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            //  if (!$request->user()->hasPermissionTo('student_update')) {
            //      return response()->json([
            //          "message" => "You can not perform this action"
            //      ], 401);
            //  } test

            $query = Student::with(
                [
                    "student_status" => function ($query) {
                        $query->select("student_statuses.id", "student_statuses.name");
                    },
                    "course_title"  => function ($query) {
                        $query->select("course_titles.id", "course_titles.name");
                    },

                    "session"  => function ($query) {
                        $query->select("sessions.id", "sessions.name");
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
            $students = $this->retrieveData($query, "id", "students");



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
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('student_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business_id =  $request->user()->business_id;

            $student =  Student::with([
                "student_status",
                "student_sessions",
                "student_sessions.session.class_routines",
                "student_sessions.student_session_courses",
                "student_sessions.student_session_courses.course",
                "student_sessions.student_session_courses.student_session_course_subjects",
                "student_sessions.student_session_courses.student_session_course_subjects.subject",
                "referral",
                "student_documents"
            ])
                ->where([
                    "id" => $id,
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
                    "message" => "no data found"
                ], 404);
            }


            // COUNT TOTAL STUDENT
            $totalCourses = 0;
            $totalSubjects = 0;

            foreach ($student->student_sessions as $studentSession) {
                foreach ($studentSession->student_session_courses as $course) {
                    $totalCourses++; // each student_session_course is a course
                    $totalSubjects += $course->student_session_course_subjects->count();
                }
            }

            // INJECT TOTAL COURSE AND SUBJECT
            $student->total_courses = $totalCourses;
            $student->total_subjects = $totalSubjects;

            // Log::info($student);

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
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $student =  Student::where([
                "id" => $id
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
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('student_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business_id = $request->user()->business_id;


            $student = Student::where([
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
    public function generateStudentId($business_id, Request $request)
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
    public function validateStudentIdV2($student_id, $business_id, Request $request)
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
