<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckInRequest;
use App\Http\Requests\UpdateCheckInRequest;
use App\Models\Business;
use App\Models\CheckIn;
use App\Models\Student;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
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
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         description="student or customer",
     *         @OA\Schema(type="string", example="student")
     *     ),
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         required=false,
     *         description="Filter by student ID",
     *         @OA\Schema(type="integer", example=12)
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

        if (!request()->filled("busiess_id")) {
            return response()->json([
                "message" => "Business ID is required"
            ], 401);
        }

        $check_in_query = CheckIn::where([
            "business_id" => request()->user()->business_id
        ]);

        if (request()->has('type')) {
            $check_in_query->where('type', request('type'));
        }

        if (request()->has('student_id')) {
            $check_in_query->where('student_id', request('student_id'));
        }

        if (request()->has('phone')) {
            $check_in_query->where('phone', 'like', '%' . request('phone') . '%');
        }

        if (request()->has('check_in_from')) {
            $check_in_query->whereDate('check_in_at', '>=', request('check_in_from'));
        }

        if (request()->has('check_in_to')) {
            $check_in_query->whereDate('check_in_at', '<=', request('check_in_to'));
        }

        $check_ins = $this->retrieveData($check_in_query, "id", "check_ins");

        return response()->json($check_ins);
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
