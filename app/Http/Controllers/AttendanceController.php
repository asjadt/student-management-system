<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCreateRequest;
use App\Http\Requests\AttendanceCreateRequestV2;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Attendance;
use App\Models\ClassRoutine;
use App\Models\Student;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{

    use ErrorUtil, UserActivityUtil, BusinessUtil;


    /**
     *
     * @OA\Post(
     *      path="/v1.0/attendances",
     *      operationId="createAttendance",
     *      tags={"attendances"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store attendances",
     *      description="This method is to store attendances",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="class_routine_id", type="integer", example=1),
     *             @OA\Property(property="day_of_week", type="integer", example=1),
     *             @OA\Property(property="start_time", type="integer", example=1),
     *             @OA\Property(property="end_time", type="integer", example=1),
     *             @OA\Property(property="room_number", type="integer", example=1),
     *             @OA\Property(property="subject_id", type="integer", example=1),
     *             @OA\Property(property="teacher_id", type="integer", example=1),

     *             @OA\Property(property="session_id", type="integer", example=1),
     *             @OA\Property(property="course_id", type="integer", example=1),

     *             @OA\Property(property="attendance_date", type="string", format="date", example="2025-04-29"),
     *             @OA\Property(
     *                 property="students",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id", "status"},
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="status", type="string", enum={"present", "absent", "late", "excused"}, example="present"),
     *                     @OA\Property(property="remarks", type="string", example="Came late due to traffic")
     *                 )
     *             ),

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

    public function createAttendance(AttendanceCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            // Log the user's activity for creating a attendance
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start a database transaction to ensure data consistency

            // Check if the authenticated user has permission to create a attendance
            if (!auth()->user()->hasPermissionTo('attendance_create')) {
                // If not, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Validate the request data
            $request_data = $request->validated();


            // Set the user who created this attendance
            $request_data["created_by"] = auth()->user()->id;

            // Set the business ID from the authenticated user's business ID
            $request_data["business_id"] = auth()->user()->business_id;



            foreach ($request_data["students"] as $student) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $student['id'],
                        'attendance_date' => $request->attendance_date,
                    ],
                    [
                        'class_routine_id' => $request_data["class_routine_id"],
                        'status' => $student['status'],
                        'remarks' => $student['remarks'] ?? null,

                        // Routine snapshot
                        'day_of_week' => $request_data["day_of_week"] ?? "",
                        'start_time' => $request_data["start_time"] ?? "",
                        'end_time' => $request_data["end_time"] ?? "",
                        'room_number' => $request_data["room_number"] ?? "",
                        'subject_id' => $request_data["subject_id"] ?? "",
                        'teacher_id' => $request_data["teacher_id"] ?? "",

                        'session_id' => $request_data["session_id"] ?? "",
                        'course_id' => $request_data["course_id"] ?? "",

                        'business_id' => $request_data["business_id"] ?? "",
                        'created_by' => $request_data["created_by"] ?? "",
                    ]
                );
            }
            DB::commit();
            return response()->json(['message' => 'Attendance recorded for all students.'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            // If an exception occurs, handle the error by returning a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Post(
     *      path="/v2.0/attendances",
     *      operationId="createAttendanceV2",
     *      tags={"attendances"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store attendances",
     *      description="This method is to store attendances",
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"attendance_date", "students"},
     *              @OA\Property(property="attendance_date", type="string", format="date", example="2025-04-29"),
     *              @OA\Property(
     *                  property="students",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      required={"id", "subjects"},
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(
     *                          property="attendances",
     *                          type="array",
     *                          @OA\Items(
     *                              type="object",
     *                              required={"subject_id", "teacher_id", "status"},
     *                              @OA\Property(property="class_routine_id", type="integer", example=1),
     *                              @OA\Property(property="day_of_week", type="integer", example=1),
     *                              @OA\Property(property="start_time", type="string", format="time", example="09:00"),
     *                              @OA\Property(property="end_time", type="string", format="time", example="10:00"),
     *                              @OA\Property(property="room_number", type="string", example="A101"),
     *                              @OA\Property(property="subject_id", type="integer", example=10),
     *                              @OA\Property(property="teacher_id", type="integer", example=5),
     *                              @OA\Property(property="session_id", type="integer", example=1),
     *                              @OA\Property(property="course_id", type="integer", example=1),
     *                              @OA\Property(property="status", type="string", enum={"present", "absent", "late", "excused"}, example="present"),
     *                              @OA\Property(property="remarks", type="string", example="Came late due to traffic")
     *                          )
     *                      )
     *                  )
     *              )
     *          )
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

    public function createAttendanceV2(AttendanceCreateRequestV2 $request)
    {
        DB::beginTransaction();
        try {
            // Log the user's activity for creating a attendance
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start a database transaction to ensure data consistency

            // Check if the authenticated user has permission to create a attendance
            if (!auth()->user()->hasPermissionTo('attendance_create')) {
                // If not, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Validate the request data
            $request_data = $request->validated();


            // Set the user who created this attendance
            $request_data["created_by"] = auth()->user()->id;

            // Set the business ID from the authenticated user's business ID
            $request_data["business_id"] = auth()->user()->business_id;



            foreach ($request_data["students"] as $student) {
                foreach ($student["attendances"] as $attendance_entry) {
                    Attendance::updateOrCreate(
                        [
                            'student_id' => $student['id'],
                            'subject_id' => $attendance_entry['subject_id'],
                            'teacher_id' => $attendance_entry['teacher_id'],
                            'attendance_date' => $request->attendance_date,
                        ],
                        [
                            'class_routine_id' => $attendance_entry["class_routine_id"] ?? null,
                            'status' => $attendance_entry['status'],
                            'remarks' => $attendance_entry['remarks'] ?? null,
                            'day_of_week' => $attendance_entry["day_of_week"] ?? null,
                            'start_time' => $attendance_entry["start_time"] ?? null,
                            'end_time' => $attendance_entry["end_time"] ?? null,
                            'room_number' => $attendance_entry["room_number"] ?? null,
                            'session_id' => $attendance_entry["session_id"] ?? null,
                            'course_id' => $attendance_entry["course_id"] ?? null,
                            'business_id' => $request_data["business_id"],
                            'created_by' => $request_data["created_by"],
                        ]
                    );
                }
            }

            DB::commit();
            return response()->json(['message' => 'Attendance recorded for all students.'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            // If an exception occurs, handle the error by returning a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="/v1.0/attendances",
     *      operationId="updateAttendance",
     *      tags={"attendances"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update attendance ",
     *      description="This method is to update attendance ",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *      @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="status", type="string", format="string", example="status"),
     * @OA\Property(property="remarks", type="string", format="string", example="remarks")
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



    public function updateAttendance(AttendanceUpdateRequest $request)
    {

        DB::beginTransaction();
        try {
            // Store the activity for updating a attendance
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start a transaction to ensure data consistency

            // Check if the authenticated user has permission to update a attendance
            if (!auth()->user()->hasPermissionTo('attendance_update')) {
                // If not, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Validate the request data
            $request_data = $request->validated();


            $attendance = Attendance::where([
                "business_id" => auth()->user()->business_id,
                "id" => $request_data["id"],
            ])->first();

            if (empty($attendance)) {
                return response()->json([
                    "message" => "No attendance found"
                ], 500);
            }



            // Fill the attendance object with the validated request data
            $attendance->fill($request_data);

            // Save the changes to the attendance
            $attendance->save();

            DB::commit();
            // Return the updated attendance
            return response($attendance, 201);
        } catch (Exception $e) {

            DB::rollBack();
            // Return a 500 Internal Server Error response with the error message
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     * @OA\Get(
     *      path="/v1.0/attendances",
     *      operationId="getAttendances",
     *      tags={"attendances"},
     *      security={{"bearerAuth": {}}},
     *
     *      @OA\Parameter(name="id", in="query", description="Attendance ID", required=false, example="1"),
     *      @OA\Parameter(name="class_routine_id", in="query", description="Class routine ID", required=false, example="5"),
     *      @OA\Parameter(name="student_id", in="query", description="Student ID", required=false, example="12"),
     *      @OA\Parameter(name="attendance_date", in="query", description="Exact attendance date", required=false, example="2025-04-15"),
     *      @OA\Parameter(name="status", in="query", description="Attendance status", required=false, example="present"),
     *      @OA\Parameter(name="remarks", in="query", description="Remarks for the attendance", required=false, example="Late arrival"),
     *      @OA\Parameter(name="day_of_week", in="query", description="Day of the week", required=false, example="Monday"),
     *      @OA\Parameter(name="start_time", in="query", description="Start time of the attendance", required=false, example="08:00"),
     *      @OA\Parameter(name="end_time", in="query", description="End time of the attendance", required=false, example="10:00"),
     *      @OA\Parameter(name="room_number", in="query", description="Room number", required=false, example="101"),
     *      @OA\Parameter(name="subject_id", in="query", description="Subject ID", required=false, example="3"),
     *      @OA\Parameter(name="teacher_id", in="query", description="Teacher ID", required=false, example="7"),

     *      @OA\Parameter(name="session_id", in="query", description="Session ID", required=false, example="1"),
     *      @OA\Parameter(name="course_id", in="query", description="Course ID", required=false, example="6"),
     *      @OA\Parameter(name="created_by", in="query", description="Created by user ID", required=false, example="15"),
     *      @OA\Parameter(name="start_date", in="query", description="Start date to filter attendances", required=false, example="2025-04-01"),
     *      @OA\Parameter(name="end_date", in="query", description="End date to filter attendances", required=false, example="2025-04-30"),

     *      @OA\Parameter(name="order_by", in="query", description="Order direction (ASC or DESC)", required=false, example="DESC"),
     *      @OA\Parameter(name="per_page", in="query", description="Number of records per page", required=false, example="10"),
     *      summary="Get attendances based on filters",
     *      description="This method retrieves attendance records based on given filters such as attendance time, room, date, and more.",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function getAttendances(Request $request)
    {
        try {
            // Store the activity for getting attendances
            $this->storeActivity($request, "Attendance fetched", "Fetched attendance records with given filters");

            // Check if the user has permission to view attendances
            if (!$request->user()->hasPermissionTo('attendance_view')) {
                return response()->json([
                    "message" => "You are not authorized to perform this action"
                ], 401);
            }



            $attendances = Attendance::with(
                [
                    "class_routine.teacher",
                    "class_routine.subject",
                    "class_routine.course",
                    "class_routine.session",
                    "student"
                ]
            )
                ->filterAttendance();

            // Paginate or get all
            $attendances = $request->filled("per_page")
                ? $attendances->paginate($request->per_page)
                : $attendances->get();



            // Return the attendance data
            return response()->json($attendances, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     * @OA\Get(
     *      path="/v2.0/attendances",
     *      operationId="getAttendancesV2",
     *      tags={"attendances"},
     *      security={{"bearerAuth": {}}},
     *
     *      @OA\Parameter(name="id", in="query", description="Attendance ID", required=false, example="1"),
     *      @OA\Parameter(name="class_routine_id", in="query", description="Class routine ID", required=false, example="5"),
     *      @OA\Parameter(name="student_id", in="query", description="Student ID", required=false, example="12"),
     *      @OA\Parameter(name="attendance_date", in="query", description="Exact attendance date", required=false, example="2025-04-15"),
     *      @OA\Parameter(name="status", in="query", description="Attendance status", required=false, example="present"),
     *      @OA\Parameter(name="remarks", in="query", description="Remarks for the attendance", required=false, example="Late arrival"),
     *      @OA\Parameter(name="day_of_week", in="query", description="Day of the week", required=false, example="Monday"),
     *      @OA\Parameter(name="start_time", in="query", description="Start time of the attendance", required=false, example="08:00"),
     *      @OA\Parameter(name="end_time", in="query", description="End time of the attendance", required=false, example="10:00"),
     *      @OA\Parameter(name="room_number", in="query", description="Room number", required=false, example="101"),
     *      @OA\Parameter(name="subject_id", in="query", description="Subject ID", required=false, example="3"),
     *      @OA\Parameter(name="teacher_id", in="query", description="Teacher ID", required=false, example="7"),

     *      @OA\Parameter(name="session_id", in="query", description="Session ID", required=false, example="1"),
     *      @OA\Parameter(name="course_id", in="query", description="Course ID", required=false, example="6"),
     *      @OA\Parameter(name="created_by", in="query", description="Created by user ID", required=false, example="15"),
     *      @OA\Parameter(name="start_date", in="query", description="Start date to filter attendances", required=false, example="2025-04-01"),
     *      @OA\Parameter(name="end_date", in="query", description="End date to filter attendances", required=false, example="2025-04-30"),

     *      @OA\Parameter(name="order_by", in="query", description="Order direction (ASC or DESC)", required=false, example="DESC"),
     *      @OA\Parameter(name="per_page", in="query", description="Number of records per page", required=false, example="10"),
     *      summary="Get attendances based on filters",
     *      description="This method retrieves attendance records based on given filters such as attendance time, room, date, and more.",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function getAttendancesV2(Request $request)
    {
        try {
            // Store the activity for getting attendances
            $this->storeActivity($request, "Attendance fetched", "Fetched attendance records with given filters");

            // Check if the user has permission to view attendances
            if (!$request->user()->hasPermissionTo('attendance_view')) {
                return response()->json([
                    "message" => "You are not authorized to perform this action"
                ], 401);
            }


            $attendances = Attendance::with([
                "class_routine.teacher",
                "class_routine.subject",
                "class_routine.course",
                "class_routine.session",
                "student"
            ])
                ->filterAttendance();


            // Paginate or get all
            $attendances = $request->filled("per_page")
                ? $attendances->paginate($request->per_page)
                : $attendances->get();

            $total_attendances = $attendances->count();

            $present_count = $attendances->filter(function ($attendance) {
                return $attendance->status === 'present';
            })->count();

            $absent_count = $attendances->filter(function ($attendance) {
                return $attendance->status === 'absent';
            })->count();

            $present_percentage = $total_attendances > 0 ? round(($present_count / $total_attendances) * 100, 2) : 0;
            $absent_percentage = $total_attendances > 0 ? round(($absent_count / $total_attendances) * 100, 2) : 0;

            return response()->json([
                'data' => $attendances,
                'summary' => [
                    'total_attendances' => $total_attendances,
                    'present' => $present_count,
                    'absent' => $absent_count,
                    'present_percentage' => $present_percentage,
                    'absent_percentage' => $absent_percentage,
                ]
            ], 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     * @OA\Get(
     *      path="/v3.0/attendances",
     *      operationId="getAttendancesV3",
     *      tags={"attendances"},
     *      security={{"bearerAuth": {}}},
     *
     *      @OA\Parameter(name="id", in="query", description="Attendance ID", required=false, example="1"),
     *      @OA\Parameter(name="class_routine_id", in="query", description="Class routine ID", required=false, example="5"),
     *      @OA\Parameter(name="student_id", in="query", description="Student ID", required=false, example="12"),
     *      @OA\Parameter(name="attendance_date", in="query", description="Exact attendance date", required=false, example="2025-04-15"),
     *      @OA\Parameter(name="status", in="query", description="Attendance status", required=false, example="present"),
     *      @OA\Parameter(name="remarks", in="query", description="Remarks for the attendance", required=false, example="Late arrival"),
     *      @OA\Parameter(name="day_of_week", in="query", description="Day of the week", required=false, example="Monday"),
     *      @OA\Parameter(name="start_time", in="query", description="Start time of the attendance", required=false, example="08:00"),
     *      @OA\Parameter(name="end_time", in="query", description="End time of the attendance", required=false, example="10:00"),
     *      @OA\Parameter(name="room_number", in="query", description="Room number", required=false, example="101"),
     *      @OA\Parameter(name="subject_id", in="query", description="Subject ID", required=false, example="3"),
     *      @OA\Parameter(name="teacher_id", in="query", description="Teacher ID", required=false, example="7"),

     *      @OA\Parameter(name="session_id", in="query", description="Session ID", required=false, example="1"),
     *      @OA\Parameter(name="course_id", in="query", description="Course ID", required=false, example="6"),
     *      @OA\Parameter(name="created_by", in="query", description="Created by user ID", required=false, example="15"),
     *      @OA\Parameter(name="start_date", in="query", description="Start date to filter attendances", required=false, example="2025-04-01"),
     *      @OA\Parameter(name="end_date", in="query", description="End date to filter attendances", required=false, example="2025-04-30"),

     *      @OA\Parameter(name="order_by", in="query", description="Order direction (ASC or DESC)", required=false, example="DESC"),
     *      @OA\Parameter(name="per_page", in="query", description="Number of records per page", required=false, example="10"),
     *      summary="Get attendances based on filters",
     *      description="This method retrieves attendance records based on given filters such as attendance time, room, date, and more.",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function getAttendancesV3(Request $request)
    {
        try {
            // Store the activity for getting attendances
            $this->storeActivity($request, "Attendance fetched", "Fetched attendance records with given filters");

            // Check if the user has permission to view attendances
            if (!$request->user()->hasPermissionTo('attendance_view')) {
                return response()->json([
                    "message" => "You are not authorized to perform this action"
                ], 401);
            }


            // GET ATTENDANCE ACCORDING TO FILTERS
            $attendances = Attendance::with([
                "class_routine.teacher",
                "class_routine.subject",
                "class_routine.course",
                "class_routine.session",
                "student"
            ])->filterAttendance();


            // Paginate or get all
            $attendances_collection = $attendances->get();

            $grouped = $attendances_collection->groupBy(function ($item) {
                return $item->class_routine_id . '-' . $item->attendance_date;
            })->map(function ($group) {
                // Filter present and absent students
                $present_students = $group->filter(function ($attendance) {
                    return $attendance->status === 'present';
                });

                $absent_students = $group->filter(function ($attendance) {
                    return $attendance->status === 'absent';
                });

                // Get unique student IDs for present and absent students
                $present_student_ids = $present_students->pluck('student_id')->unique()->values();
                $absent_student_ids = $absent_students->pluck('student_id')->unique()->values();

                return [
                    'attendance_date' => $group->first()->attendance_date,
                    'attendanceIds' => $group->pluck('id')->toArray(),
                    'class_routine' => ClassRoutine::with("teacher", "subject", "course", "session")->where([
                        "id" => $group->first()->class_routine_id
                    ])->first(),
                    'present_students' => Student::whereIn("id", $present_student_ids)->get(),
                    'absent_students' => Student::whereIn("id", $absent_student_ids)->get(),
                ];
            })->values();



            return response()->json([
                'data' => $grouped,
                'summary' => [
                    'total_attendances' => $attendances_collection->count(),
                    'present' => $attendances_collection->where('status', 'present')->count(),
                    'absent' => $attendances_collection->where('status', 'absent')->count(),
                    'present_percentage' => $attendances_collection->count() > 0
                        ? round(($attendances_collection->where('status', 'present')->count() / $attendances_collection->count()) * 100, 2)
                        : 0,
                    'absent_percentage' => $attendances_collection->count() > 0
                        ? round(($attendances_collection->where('status', 'absent')->count() / $attendances_collection->count()) * 100, 2)
                        : 0,
                    'total_students' => $attendances_collection->pluck('student_id')->unique()->count(),
                ]
            ], 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/attendances/{ids}",
     *      operationId="deleteAttendancesByIds",
     *      tags={"attendances"},
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
     *      summary="This method is to delete attendance by id",
     *      description="This method is to delete attendance by id",
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
     * Deletes one or more attendance by ID.
     *
     * @param Request $request
     * @param string $ids
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAttendancesByIds(Request $request, $ids)
    {
        try {

            // Log the user's activity in the database
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has permission to delete attendance
            if (!$request->user()->hasPermissionTo('attendance_delete')) {
                // If the user does not have permission, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Split the given IDs by comma and convert to an array
            $idsArray = explode(',', $ids);

            // Retrieve the existing IDs in the database
            $existingIds = Attendance::whereIn('id', $idsArray)
                // The attendance must belong to the same business as the user
                ->where('attendances.business_id', auth()->user()->business_id)

                // Select only the 'id' column
                ->select('id')
                // Retrieve the data from the database
                ->get()
                // Convert the data to an array of IDs
                ->pluck('id')
                ->toArray();

            // Calculate the IDs that do not exist in the database
            $nonExistingIds = array_diff($idsArray, $existingIds);

            // If there are any non-existing IDs, return a 404 Not Found response
            if (!empty($nonExistingIds)) {

                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }

            // Delete the attendance
            Attendance::destroy($existingIds);

            // Return a 200 OK response with a message indicating that the data was deleted successfully
            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            // Return an error response if an exception is thrown
            return $this->sendError($e, 500, $request);
        }
    }
}
