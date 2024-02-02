<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentCreateRequest;
use App\Http\Requests\StudentUpdateRequest;
use App\Http\Requests\MultipleFileUploadRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Student;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    use ErrorUtil, UserActivityUtil, BusinessUtil;

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

     public function createStudentFileMultiple(MultipleFileUploadRequest $request)
     {
         try{
             $this->storeActivity($request, "DUMMY activity","DUMMY description");

             $insertableData = $request->validated();

             $location =  config("setup-config.student_files_location");

             $files = [];
             if(!empty($insertableData["files"])) {
                 foreach($insertableData["files"] as $file){
                     $new_file_name = time() . '_' . $file->getClientOriginalName();
                     $new_file_name = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                     $file->move(public_path($location), $new_file_name);

                     array_push($files,("/".$location."/".$new_file_name));


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
*     @OA\Property(property="first_name", type="string", format="string", example="John"),
 *     @OA\Property(property="middle_name", type="string", format="string", example=""),
 *     @OA\Property(property="last_name", type="string", format="string", example="Doe"),
 *     @OA\Property(property="nationality", type="string", format="string", example="Country"),
 *     @OA\Property(property="passport_number", type="string", format="string", example="ABC123"),
 *     @OA\Property(property="school_id", type="string", format="string", example="School123"),
 *     @OA\Property(property="date_of_birth", type="string", format="date", example="2000-01-01"),
 *     @OA\Property(property="course_start_date", type="string", format="date", example="2024-01-31"),
 *     @OA\Property(property="letter_issue_date", type="string", format="date", example="2024-02-01"),
 *     @OA\Property(property="student_status_id", type="number", format="number", example=1),
 *     @OA\Property(property="attachments", type="string", format="array", example={"a.png","b.jpeg"})
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




                return response($student, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
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
*     @OA\Property(property="first_name", type="string", format="string", example="John"),
 *     @OA\Property(property="middle_name", type="string", format="string", example=""),
 *     @OA\Property(property="last_name", type="string", format="string", example="Doe"),
 *     @OA\Property(property="nationality", type="string", format="string", example="Country"),
 *     @OA\Property(property="passport_number", type="string", format="string", example="ABC123"),
 *     @OA\Property(property="school_id", type="string", format="string", example="School123"),
 *     @OA\Property(property="date_of_birth", type="string", format="date", example="2000-01-01"),
 *     @OA\Property(property="course_start_date", type="string", format="date", example="2024-01-31"),
 *     @OA\Property(property="letter_issue_date", type="string", format="date", example="2024-02-01"),
 *     @OA\Property(property="student_status_id", type="number", format="number", example=1),
 *   @OA\Property(property="attachments", type="string", format="array", example={"/abcd.jpg","/efgh.jpg"})

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
                        'middle_name',
                        'last_name',
                        'nationality',
                        'passport_number',
                        'school_id',
                        'date_of_birth',
                        'course_start_date',
                        'letter_issue_date',
                        'student_status_id',
                        'attachments',

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

                return response($student, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

 /**
     *
     * @OA\Get(
     *      path="/v1.0/students/validate/school-id/{school_id}",
     *      operationId="validateStudentId",
     *      tags={"students"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *              @OA\Parameter(
     *         name="school_id",
     *         in="path",
     *         description="school_id",
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
    public function validateStudentId($school_id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $school_id_exists =  Student::where(
                [
                    'school_id' => $school_id,
                    "business_id" => $request->user()->business_id
                ]
            )->exists();



            return response()->json(["school_id_exists" => $school_id_exists], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
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
     * *   * *  @OA\Parameter(
     * name="school_id",
     * in="query",
     * description="school_id",
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
            $business_id =  $request->user()->business_id;
            $students = Student::
            with("student_status")

            ->where(
                [
                    "students.business_id" => $business_id
                ]
            )

                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;


                        $query->where("students.first_name", "like", "%" . $term . "%")
                            ->orWhere("students.middle_name", "like", "%" . $term . "%")
                            ->orWhere("students.last_name", "like", "%" . $term . "%")
                            ->orWhere("students.nationality", "like", "%" . $term . "%")
                            ->orWhere("students.passport_number", "like", "%" . $term . "%")
                            ->orWhere("students.school_id", "like", "%" . $term . "%")
                            ->orWhere("students.date_of_birth", "like", "%" . $term . "%");
                    });
                })
                //    ->when(!empty($request->product_category_id), function ($query) use ($request) {
                //        return $query->where('product_category_id', $request->product_category_id);
                //    })
                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('students.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('students.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })
                ->when(!empty($request->student_status_id), function ($query) use ($request) {
                    return $query->where('students.student_status_id',$request->student_status_id);
                })
                ->when(!empty($request->date_of_birth), function ($query) use ($request) {
                    return $query->where('students.date_of_birth',$request->date_of_birth);
                })
                ->when(!empty($request->school_id), function ($query) use ($request) {
                    return $query->where('students.school_id',$request->school_id);
                })

                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("students.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("students.id", "DESC");
                })
                ->when(!empty($request->per_page), function ($query) use ($request) {
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    return $query->get();
                });;



            return response()->json($students, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
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
     *
     *         @OA\Parameter(
     *         name="business_id",
     *         in="query",
     *         description="business_id",
     *         required=true,
     *         example="2"
     *          ),
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
     * *   * *  @OA\Parameter(
     * name="school_id",
     * in="query",
     * description="school_id",
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
            //  }
             $business_id =  $request->business_id;
             if(!$business_id) {
                $error = [ "message" => "The given data was invalid.",
                "errors" => ["business_id"=>["The business id field is required."]]
                ];
                    throw new Exception(json_encode($error),422);
             }


             $students = Student::
             with("student_status")

             ->where(
                 [
                     "students.business_id" => $business_id
                 ]
             )

                 ->when(!empty($request->search_key), function ($query) use ($request) {
                     return $query->where(function ($query) use ($request) {
                         $term = $request->search_key;


                         $query->where("students.first_name", "like", "%" . $term . "%")
                             ->orWhere("students.middle_name", "like", "%" . $term . "%")
                             ->orWhere("students.last_name", "like", "%" . $term . "%")
                             ->orWhere("students.nationality", "like", "%" . $term . "%")
                             ->orWhere("students.passport_number", "like", "%" . $term . "%")
                             ->orWhere("students.school_id", "like", "%" . $term . "%")
                             ->orWhere("students.date_of_birth", "like", "%" . $term . "%");
                     });
                 })
                 //    ->when(!empty($request->product_category_id), function ($query) use ($request) {
                 //        return $query->where('product_category_id', $request->product_category_id);
                 //    })
                 ->when(!empty($request->start_date), function ($query) use ($request) {
                     return $query->where('students.created_at', ">=", $request->start_date);
                 })
                 ->when(!empty($request->end_date), function ($query) use ($request) {
                     return $query->where('students.created_at', "<=", ($request->end_date . ' 23:59:59'));
                 })
                 ->when(!empty($request->student_status_id), function ($query) use ($request) {
                     return $query->where('students.student_status_id',$request->student_status_id);
                 })
                 ->when(!empty($request->date_of_birth), function ($query) use ($request) {
                     return $query->where('students.date_of_birth',$request->date_of_birth);
                 })
                 ->when(!empty($request->school_id), function ($query) use ($request) {
                     return $query->where('students.school_id',$request->school_id);
                 })

                 ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                     return $query->orderBy("students.id", $request->order_by);
                 }, function ($query) {
                     return $query->orderBy("students.id", "DESC");
                 })
                 ->when(!empty($request->per_page), function ($query) use ($request) {
                     return $query->paginate($request->per_page);
                 }, function ($query) {
                     return $query->get();
                 });;



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
            $business_id =  $request->user()->business_id;
            $idsArray = explode(',', $ids);
            $existingIds = Student::where([
                "business_id" => $business_id
            ])
                ->whereIn('id', $idsArray)
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            $nonExistingIds = array_diff($idsArray, $existingIds);

            if (!empty($nonExistingIds)) {
                $this->storeError(
                    "no data found"
                    ,
                    404,
                    "front end error",
                    "front end error"
                   );
                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }
            Student::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully","deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
