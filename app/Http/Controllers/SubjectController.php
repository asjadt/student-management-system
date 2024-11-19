<?php





namespace App\Http\Controllers;

use App\Http\Requests\SubjectCreateRequest;
use App\Http\Requests\SubjectUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\CourseSubject;
use App\Models\Subject;
use App\Models\DisabledSubject;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{

    use ErrorUtil, UserActivityUtil, BusinessUtil;


    /**
     *
     * @OA\Post(
     *      path="/v1.0/subjects",
     *      operationId="createSubject",
     *      tags={"subjects"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store subjects",
     *      description="This method is to store subjects",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     * @OA\Property(property="name", type="string", format="string", example="name"),
     * @OA\Property(property="description", type="string", format="string", example="description"),
     *
     *    * @OA\Property(property="teacher_ids", type="string", format="array", example={1,2,3}),
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

    public function createSubject(SubjectCreateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!auth()->user()->hasPermissionTo('subject_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();

                $request_data["is_active"] = 1;


                $request_data["created_by"] = auth()->user()->id;
                $request_data["business_id"] = auth()->user()->business_id;

                if (empty(auth()->user()->business_id)) {
                    $request_data["business_id"] = NULL;
                    if (auth()->user()->hasRole('superadmin')) {
                        $request_data["is_default"] = 1;
                    }
                }

                $subject =  Subject::create($request_data);

                $subject->teachers()->sync($request_data["teacher_ids"]);


                if(!empty($request_data["course_id"])){
                  CourseSubject::create([
                    "course_id" => $request_data["course_id"],
                    "subject_id" => $subject->id
                  ]);
                }

                return response($subject, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     *      path="/v1.0/subjects",
     *      operationId="updateSubject",
     *      tags={"subjects"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update subjects ",
     *      description="This method is to update subjects ",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *      @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="name", type="string", format="string", example="name"),
     * @OA\Property(property="description", type="string", format="string", example="description"),
     * @OA\Property(property="teacher_ids", type="string", format="array", example={1,2,3}),
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

    public function updateSubject(SubjectUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!auth()->user()->hasPermissionTo('subject_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $request_data = $request->validated();



                $subject_query_params = [
                    "id" => $request_data["id"],
                ];

                $subject = Subject::where($subject_query_params)->first();

                if ($subject) {
                    $subject->fill(collect($request_data)->only([

                        "name",
                        "description",
                        // "is_default",
                        // "is_active",
                        // "business_id",
                        // "created_by"
                    ])->toArray());
                    $subject->save();
                } else {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }
                $subject->teachers()->sync($request_data["teacher_ids"]);



                return response($subject, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Put(
     *      path="/v1.0/subjects/toggle-active",
     *      operationId="toggleActiveSubject",
     *      tags={"subjects"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to toggle subjects",
     *      description="This method is to toggle subjects",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(

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

    public function toggleActiveSubject(GetIdRequest $request)
    {

        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('subject_activate')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $request_data = $request->validated();

            $subject =  Subject::where([
                "id" => $request_data["id"],
            ])
                ->first();
            if (!$subject) {

                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            $subject->update([
                'is_active' => !$subject->is_active
            ]);




            return response()->json(['message' => 'subject status updated successfully'], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     * @OA\Get(
     *      path="/v1.0/subjects",
     *      operationId="getSubjects",
     *      tags={"subjects"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *         @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="name",
     *         required=true,
     *  example="6"
     *      ),



     *         @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="description",
     *         required=true,
     *  example="6"
     *      ),



     *         @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="per_page",
     *         required=true,
     *  example="6"
     *      ),

     *     @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=true,
     * example="1"
     * ),
     *     @OA\Parameter(
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
     * *  @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=true,
     * example="ASC"
     * ),
     * * *  @OA\Parameter(
     * name="course_id",
     * in="query",
     * description="course_id",
     * required=true,
     * example="ASC"
     * ),
     *
     *




     *      summary="This method is to get subjects  ",
     *      description="This method is to get subjects ",
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

    public function getSubjects(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('subject_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }



            $subjects = Subject::
            with("teachers","courses")
            ->where('subjects.business_id', auth()->user()->business_id)


                ->when(!empty($request->id), function ($query) use ($request) {
                    return $query->where('subjects.id', $request->id);
                })
                ->when(!empty($request->course_id), function ($query) use($request) {
                    return $query->whereHas('courses', function($query) use($request) {
                        $query->join('course_titles', 'course_titles.id', '=', 'course_subjects.course_id') // Add join with course_titles
                              ->where("course_titles.id", $request->course_id);
                    });
                })
                ->when(!empty($request->name), function ($query) use ($request) {
                    return $query->where('subjects.id', $request->string);
                })
                ->when(!empty($request->description), function ($query) use ($request) {
                    return $query->where('subjects.id', $request->string);
                })



                ->when(!empty($request->description), function ($query) use ($request) {
                    return $query->where('subjects.id', $request->string);
                })





                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                        $query

                            ->orWhere("subjects.name", "like", "%" . $term . "%")
                            ->where("subjects.description", "like", "%" . $term . "%")
                        ;
                    });
                })


                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('subjects.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('subjects.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("subjects.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("subjects.id", "DESC");
                })
                ->when($request->filled("id"), function ($query) use ($request) {
                    return $query
                        ->where("subjects.id", $request->input("id"))
                        ->first();
                }, function ($query) {
                    return $query->when(!empty(request()->per_page), function ($query) {
                        return $query->paginate(request()->per_page);
                    }, function ($query) {
                        return $query->get();
                    });
                });

            if ($request->filled("id") && empty($subjects)) {
                throw new Exception("No data found", 404);
            }


            return response()->json($subjects, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/subjects/{ids}",
     *      operationId="deleteSubjectsByIds",
     *      tags={"subjects"},
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
     *      summary="This method is to delete subject by id",
     *      description="This method is to delete subject by id",
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

    public function deleteSubjectsByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('subject_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $idsArray = explode(',', $ids);
            $existingIds = Subject::whereIn('id', $idsArray)
                ->where('subjects.business_id', auth()->user()->business_id)

                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            $nonExistingIds = array_diff($idsArray, $existingIds);

            if (!empty($nonExistingIds)) {

                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }





            Subject::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
