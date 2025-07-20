<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckInRequest;
use App\Http\Requests\UpdateCheckInRequest;
use App\Http\Utils\BasicUtil;
use App\Models\Business;
use App\Models\CheckIn;
use App\Models\Student;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckInController extends Controller

{
    use BasicUtil;
    /**
     *
     * @OA\Post(
     *      path="/v1.0/check-ins",
     *      operationId="storeCheckIn",
     *      tags={"check_in"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Store a check-in entry (student or customer)",
     *      description="This method stores a check-in record for either a student or a customer",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "check_in_at"},
     *             @OA\Property(property="type", type="string", example="student"),
     *             @OA\Property(property="student_id", type="integer", example=5),
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="phone", type="string", example="01712345678"),
     *             @OA\Property(property="comment", type="string", example="Visiting for inquiry")
     *         )
     *  ),
     *
     *  @OA\Response(
     *      response=200,
     *      description="Successful operation",
     *      @OA\JsonContent()
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *      @OA\JsonContent()
     *  ),
     *  @OA\Response(
     *      response=422,
     *      description="Unprocessable Content",
     *      @OA\JsonContent()
     *  ),
     *  @OA\Response(
     *      response=403,
     *      description="Forbidden",
     *      @OA\JsonContent()
     *  ),
     *  @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *      @OA\JsonContent()
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Not Found",
     *      @OA\JsonContent()
     *  )
     * )
     */
    public function store(StoreCheckInRequest $request): JsonResponse
    {
        // VALIDATE DATA
        $data = $request->validated();
        // CHECK IN AT
        $data['check_in_at'] = now();

        // DEFINE STUDENT STATE
        $student = null;

        // GET STUDENT ID
        if ($request->filled('student_id')) {
            // GET STUDENT
            $student = Student::where('student_id', $data['student_id'])->where('business_id', $data['business_id'])->first();

            // IF NOT FOUND
            if (!$student) {
                return response()->json([
                    'message' => 'Student not found.',
                ], 404);
            }
            // ASSIGN STUDENT ID
            $data['student_id'] = $student->id;
        }


        // CREATE CHECK-IN
        $check_in = CheckIn::create($data);

        // CHECK IF CHECK-IN WAS CREATED
        if (!$check_in) {
            return response()->json([
                'message' => 'Check-in could not be created.',
            ], 400);
        }

        $business = Business::find($request->input('business_id'));

        // RETURN RESPONSE
        $check_in['student_id'] = $request->input('student_id');
        $check_in['student'] = $student;
        $check_in['business'] = $business;

        // 
        return response()->json([
            'message' => 'Check-in recorded successfully.',
            'data' => $check_in,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1.0/check-ins",
     *     operationId="getAllCheckIns",
     *     tags={"check_in"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get all check-ins",
     *     description="Filters: type, student_id, phone, check_in_from, check_in_to",
     *
     *      @OA\Parameter(
     *         name="business_id",
     *         in="query",
     *         required=false,
     *         description="Filter by business ID",
     *         @OA\Schema(type="integer", example="")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         description="student or customer",
     *         @OA\Schema(type="string", example="student, customer")
     *     ),
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         required=false,
     *         description="Filter by student ID",
     *         @OA\Schema(type="integer", example="")
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         required=false,
     *         description="Filter by customer phone",
     *         @OA\Schema(type="string", example="01712345678")
     *     ),
     *     @OA\Parameter(
     *         name="check_in_from",
     *         in="query",
     *         required=false,
     *         description="Start of check-in date range",
     *         @OA\Schema(type="string", format="date", example="2025-07-01")
     *     ),
     *     @OA\Parameter(
     *         name="check_in_to",
     *         in="query",
     *         required=false,
     *         description="End of check-in date range",
     *         @OA\Schema(type="string", format="date", example="2025-07-14")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List returned",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function index(): JsonResponse
    {

        if (!request()->filled("business_id")) {
            return response()->json([
                "message" => "Business ID is required"
            ], 401);
        }

        $check_in_query = CheckIn::with([
            'student.course_title',
            'student.student_sessions.student_session_courses.student_session_course_subjects'
        ])
            ->where([
                "business_id" => request()->input('business_id')
            ])
            ->filter();

        $check_ins = $this->retrieveData($check_in_query, "id", "check_ins");

        return response()->json([
            "message" => "Check-ins retrieved successfully",
            "data" => $check_ins->items(),
            'meta' => [
                'total' => $check_ins->total(),
                'last_page' => $check_ins->lastPage(),
                'current_page' => $check_ins->currentPage(),
                'per_page' => $check_ins->perPage(),
                'has_more_pages' => $check_ins->hasMorePages()
            ]
        ], 200);
    }


    /**
     * @OA\Put(
     *      path="/v1.0/checkout",
     *      operationId="checkout",
     *      tags={"check_in"},
     *      security={{"bearerAuth":{}}},
     *      summary="Update check-in by ID",
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"type", "check_in_at"},
     *              @OA\Property(property="type", type="string", example="student"),
     *              @OA\Property(property="student_id", type="integer", example=5),
     *              @OA\Property(property="first_name", type="string", example="Jane"),
     *              @OA\Property(property="last_name", type="string", example="Doe"),
     *              @OA\Property(property="phone", type="string", example="01712345679"),
     *              @OA\Property(property="comment", type="string", example="Came back again")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Updated", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    public function checkout(UpdateCheckInRequest $request): JsonResponse
    {
        // DEFINE CHECK OUT TIME
        $check_out_at = Carbon::now();
        $request_data = $request->validated();

        // DEFINE CHECK IN
        $check_in = null;
        $student = null;
        $business = null;

        // CHECK BUSINESS
        $business = Business::where('id', $request->input('business_id'))->first();
        if (!$business) {
            return response()->json([
                'message' => 'Business record not found. Please contact the receptionist.',
            ], 404);
        }
        // Log::info($business);
        if ($request->has('type') && $request->input('type') == 'student') {
            // DEFINE STUDENT
            $student = Student::where('business_id', $request->input('business_id'))
                ->where('student_id', $request->input('student_id'))
                ->first();

            // IF STUDENT NOT FOUND, RETURN ERROR
            if (!$student) {
                return response()->json([
                    'message' => 'Student record not found. Please contact the receptionist.',
                ], 404);
            }

            // GET CHECKED IN RECORD
            $check_in = CheckIn::where('student_id', $student->id)
                ->whereDate('check_in_at', Carbon::today())
                ->whereNull('check_out_at')
                ->first();

            // IF CHECK IN NOT FOUND, RETURN ERROR
            if (!$check_in) {
                return response()->json([
                    'message' => 'Check-in record not found. Please contact the receptionist.',
                ], 404);
            }

            // UPDATE CHECK IN RECORD
            $check_in->update([
                'check_out_at' => $check_out_at,
            ]);
            // Log::info('student', [$student->id, $check_in]);
        }

        if ($request->has('type') && $request->input('type') == 'customer') {
            // GET CHECKED IN RECORD
            $check_in = CheckIn::where('type', 'customer')
                ->whereRaw('LOWER(first_name) = ?', [strtolower($request->input('first_name'))])
                ->whereRaw('LOWER(last_name) = ?', [strtolower($request->input('last_name'))])
                ->when(!empty($request->input('phone')), function ($query) use ($request) {
                    $query->where('phone', $request->input('phone'));
                })
                ->whereDate('check_in_at', Carbon::today())
                ->whereNull('check_out_at')
                ->first();

            // IF CHECK IN NOT FOUND, RETURN ERROR
            if (!$check_in) {
                return response()->json([
                    'message' => 'Check-in record not found. Please contact the receptionist.',
                ], 404);
            }

            // UPDATE CHECK IN RECORD
            $check_in->update([
                'check_out_at' => $check_out_at,
            ]);
            // Log::info('student', [$student->id, $check_in]);
        }

        // Log::info($check_in);

        if ($request->has('type') && $request->input('type') == 'student') {
            $check_in['student'] = $student->only('title', 'first_name', 'middle_name', 'last_name', 'email', 'phone', 'student_id', 'course_title_id');
        }
        // ADD BUSINESS DETAILS INTO RESPONSE
        $check_in['business'] = $business;

        // SEND RESPONSE
        return response()->json([
            'message' => 'Check-out updated successfully.',
            'data' => $check_in,
        ]);
    }
    /**
     * @OA\Put(
     *      path="/v1.0/check-ins/{id}",
     *      operationId="updateCheckIn",
     *      tags={"check_in"},
     *      security={{"bearerAuth":{}}},
     *      summary="Update check-in by ID",
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"type", "check_in_at"},
     *              @OA\Property(property="type", type="string", example="student"),
     *              @OA\Property(property="student_id", type="integer", example=5),
     *              @OA\Property(property="first_name", type="string", example="Jane"),
     *              @OA\Property(property="last_name", type="string", example="Doe"),
     *              @OA\Property(property="phone", type="string", example="01712345679"),
     *              @OA\Property(property="comment", type="string", example="Came back again")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Updated", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    public function update(UpdateCheckInRequest $request, $id): JsonResponse
    {
        $check_in = CheckIn::findOrFail($id);



        $data = $request->validated();

        $data["check_out_at"] = now();
        $check_in->fill($data);

        $check_in->save();

        return response()->json([
            'message' => 'Check-in updated successfully.',
            'data' => $check_in,
        ]);
    }

    /**
     * @OA\Delete(
     *      path="/v1.0/check-ins/{id}",
     *      operationId="deleteCheckIn",
     *      tags={"check_in"},
     *      security={{"bearerAuth":{}}},
     *      summary="Delete check-in",
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Deleted", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    public function destroy($id): JsonResponse
    {
        $check_in = CheckIn::findOrFail($id);
        $check_in->delete();

        return response()->json(['message' => 'Check-in deleted successfully.']);
    }
}
