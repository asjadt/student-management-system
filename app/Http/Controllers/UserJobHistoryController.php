<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserJobHistoryCreateRequest;
use App\Http\Requests\UserJobHistoryUpdateRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Department;
use App\Models\UserJobHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserJobHistoryController extends Controller
{
    use ErrorUtil, UserActivityUtil, BusinessUtil;






      /**
       *
       * @OA\Post(
       *      path="/v1.0/user-job-histories",
       *      operationId="createUserJobHistory",
       *      tags={"user_job_histories"},
       *       security={
       *           {"bearerAuth": {}}
       *       },
       *      summary="This method is to store user job history",
       *      description="This method is to store user job history",
       *
       *  @OA\RequestBody(
       *         required=true,
       *         @OA\JsonContent(
  *     @OA\Property(property="user_id", type="integer", format="int", example=1),
 *     @OA\Property(property="company_name", type="string", format="string", example="Your Company Name"),
 *     @OA\Property(property="job_title", type="string", format="string", example="Your Job Title"),
 *     @OA\Property(property="employment_start_date", type="string", format="date", example="2023-01-01"),
 *     @OA\Property(property="employment_end_date", type="string", format="date", example="2023-12-31"),
 *     @OA\Property(property="responsibilities", type="string", format="string", example="Your Responsibilities"),
 *     @OA\Property(property="supervisor_name", type="string", format="string", example="Supervisor Name"),
 *     @OA\Property(property="contact_information", type="string", format="string", example="Contact Information"),
 *     @OA\Property(property="work_location", type="string", format="string", example="Work Location"),
 *     @OA\Property(property="achievements", type="string", format="string", example="Your Achievements")
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

      public function createUserJobHistory(UserJobHistoryCreateRequest $request)
      {
          try {
              $this->storeActivity($request, "DUMMY activity","DUMMY description");
              return DB::transaction(function () use ($request) {
                  if (!$request->user()->hasPermissionTo('employee_job_history_create')) {
                      return response()->json([
                          "message" => "You can not perform this action"
                      ], 401);
                  }

                  $request_data = $request->validated();







                  $request_data["created_by"] = $request->user()->id;

                  $user_job_history =  UserJobHistory::create($request_data);



                  return response($user_job_history, 201);
              });
          } catch (Exception $e) {
              error_log($e->getMessage());
              return $this->sendError($e, 500, $request);
          }
      }

      /**
       *
       * @OA\Put(
       *      path="/v1.0/user-job-histories",
       *      operationId="updateUserJobHistory",
       *      tags={"user_job_histories"},
       *       security={
       *           {"bearerAuth": {}}
       *       },
       *      summary="This method is to update  user job history ",
       *      description="This method is to update user job history",
       *
       *  @OA\RequestBody(
       *         required=true,
       *         @OA\JsonContent(
  *      @OA\Property(property="id", type="number", format="number", example="Updated Christmas"),
 *     @OA\Property(property="user_id", type="integer", format="int", example=1),
 *     @OA\Property(property="company_name", type="string", format="string", example="Your Company Name"),
 *     @OA\Property(property="job_title", type="string", format="string", example="Your Job Title"),
 *     @OA\Property(property="employment_start_date", type="string", format="date", example="2023-01-01"),
 *     @OA\Property(property="employment_end_date", type="string", format="date", example="2023-12-31"),
 *     @OA\Property(property="responsibilities", type="string", format="string", example="Your Responsibilities"),
 *     @OA\Property(property="supervisor_name", type="string", format="string", example="Supervisor Name"),
 *     @OA\Property(property="contact_information", type="string", format="string", example="Contact Information"),

 *     @OA\Property(property="work_location", type="string", format="string", example="Work Location"),
 *     @OA\Property(property="achievements", type="string", format="string", example="Your Achievements")
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

      public function updateUserJobHistory(UserJobHistoryUpdateRequest $request)
      {

          try {
              $this->storeActivity($request, "DUMMY activity","DUMMY description");
              return DB::transaction(function () use ($request) {
                  if (!$request->user()->hasPermissionTo('employee_job_history_update')) {
                      return response()->json([
                          "message" => "You can not perform this action"
                      ], 401);
                  }
                  $business_id =  $request->user()->business_id;
                  $request_data = $request->validated();




                  $user_job_history_query_params = [
                      "id" => $request_data["id"],
                  ];
                  // $user_job_history_prev = UserJobHistory::where($user_job_history_query_params)
                  //     ->first();
                  // if (!$user_job_history_prev) {
                  //     return response()->json([
                  //         "message" => "no user job history found"
                  //     ], 404);
                  // }

                  $user_job_history  =  tap(UserJobHistory::where($user_job_history_query_params))->update(
                      collect($request_data)->only([
                        'user_id',
                        'company_name',
                        'job_title',
                        'employment_start_date',
                        'employment_end_date',
                        'responsibilities',
                        'supervisor_name',
                        'contact_information',
                        'work_location',
                        'achievements',
                        // 'created_by',

                      ])->toArray()
                  )
                      // ->with("somthing")

                      ->first();
                  if (!$user_job_history) {
                      return response()->json([
                          "message" => "something went wrong."
                      ], 500);
                  }

                  return response($user_job_history, 201);
              });
          } catch (Exception $e) {
              error_log($e->getMessage());
              return $this->sendError($e, 500, $request);
          }
      }


      /**
       *
       * @OA\Get(
       *      path="/v1.0/user-job-histories",
       *      operationId="getUserJobHistories",
       *      tags={"user_job_histories"},
       *       security={
       *           {"bearerAuth": {}}
       *       },
       *              @OA\Parameter(
       *         name="user_id",
       *         in="query",
       *         description="user_id",
       *         required=true,
       *  example="1"
       *      ),
       *              @OA\Parameter(
       *         name="per_page",
       *         in="query",
       *         description="per_page",
       *         required=true,
       *  example="6"
       *      ),

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

       *      summary="This method is to get user job histories  ",
       *      description="This method is to get user job histories ",
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

      public function getUserJobHistories(Request $request)
      {
          try {
              $this->storeActivity($request, "DUMMY activity","DUMMY description");
              if (!$request->user()->hasPermissionTo('employee_job_history_view')) {
                  return response()->json([
                      "message" => "You can not perform this action"
                  ], 401);
              }
              $business_id =  $request->user()->business_id;
              $all_manager_department_ids = [];
              $manager_departments = Department::where("manager_id", $request->user()->id)->get();
              foreach ($manager_departments as $manager_department) {
                  $all_manager_department_ids[] = $manager_department->id;
                  $all_manager_department_ids = array_merge($all_manager_department_ids, $manager_department->getAllDescendantIds());
              }
              $user_job_histories = UserJobHistory::with([
                  "creator" => function ($query) {
                      $query->select('users.id', 'users.first_Name','users.middle_Name',
                      'users.last_Name');
                  },

              ])
              ->whereHas("user.departments", function($query) use($all_manager_department_ids) {
                $query->whereIn("departments.id",$all_manager_department_ids);
             })
              ->when(!empty($request->search_key), function ($query) use ($request) {
                      return $query->where(function ($query) use ($request) {
                          $term = $request->search_key;
                          $query->where("user_job_histories.name", "like", "%" . $term . "%");
                          //     ->orWhere("user_job_histories.description", "like", "%" . $term . "%");
                      });
                  })
                  //    ->when(!empty($request->product_category_id), function ($query) use ($request) {
                  //        return $query->where('product_category_id', $request->product_category_id);
                  //    })

                  ->when(!empty($request->user_id), function ($query) use ($request) {
                      return $query->where('user_job_histories.user_id', $request->user_id);
                  })
                  ->when(empty($request->user_id), function ($query) use ($request) {
                      return $query->where('user_job_histories.user_id', $request->user()->id);
                  })
                  ->when(!empty($request->start_date), function ($query) use ($request) {
                      return $query->where('user_job_histories.created_at', ">=", $request->start_date);
                  })
                  ->when(!empty($request->end_date), function ($query) use ($request) {
                      return $query->where('user_job_histories.created_at', "<=", ($request->end_date . ' 23:59:59'));
                  })
                  ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                      return $query->orderBy("user_job_histories.id", $request->order_by);
                  }, function ($query) {
                      return $query->orderBy("user_job_histories.id", "DESC");
                  })
                  ->when(!empty($request->per_page), function ($query) use ($request) {
                      return $query->paginate($request->per_page);
                  }, function ($query) {
                      return $query->get();
                  });;



              return response()->json($user_job_histories, 200);
          } catch (Exception $e) {

              return $this->sendError($e, 500, $request);
          }
      }

      /**
       *
       * @OA\Get(
       *      path="/v1.0/user-job-histories/{id}",
       *      operationId="getUserJobHistoryById",
       *      tags={"user_job_histories"},
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
       *      summary="This method is to get user job history by id",
       *      description="This method is to get user job history by id",
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


      public function getUserJobHistoryById($id, Request $request)
      {
          try {
              $this->storeActivity($request, "DUMMY activity","DUMMY description");
              if (!$request->user()->hasPermissionTo('employee_job_history_view')) {
                  return response()->json([
                      "message" => "You can not perform this action"
                  ], 401);
              }
              $business_id =  $request->user()->business_id;
              $all_manager_department_ids = [];
              $manager_departments = Department::where("manager_id", $request->user()->id)->get();
              foreach ($manager_departments as $manager_department) {
                  $all_manager_department_ids[] = $manager_department->id;
                  $all_manager_department_ids = array_merge($all_manager_department_ids, $manager_department->getAllDescendantIds());
              }
              $user_job_history =  UserJobHistory::where([
                  "id" => $id,
              ])
              ->whereHas("user.departments", function($query) use($all_manager_department_ids) {
                $query->whereIn("departments.id",$all_manager_department_ids);
             })
                  ->first();
              if (!$user_job_history) {
                  return response()->json([
                      "message" => "no data found"
                  ], 404);
              }

              return response()->json($user_job_history, 200);
          } catch (Exception $e) {

              return $this->sendError($e, 500, $request);
          }
      }



      /**
       *
       *     @OA\Delete(
       *      path="/v1.0/user-job-histories/{ids}",
       *      operationId="deleteUserJobHistoriesByIds",
       *      tags={"user_job_histories"},
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
       *      summary="This method is to delete user job history by id",
       *      description="This method is to delete user job history by id",
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

      public function deleteUserJobHistoriesByIds(Request $request, $ids)
      {

          try {
              $this->storeActivity($request, "DUMMY activity","DUMMY description");
              if (!$request->user()->hasPermissionTo('employee_job_history_delete')) {
                  return response()->json([
                      "message" => "You can not perform this action"
                  ], 401);
              }
              $business_id =  $request->user()->business_id;
              $all_manager_department_ids = [];
              $manager_departments = Department::where("manager_id", $request->user()->id)->get();
              foreach ($manager_departments as $manager_department) {
                  $all_manager_department_ids[] = $manager_department->id;
                  $all_manager_department_ids = array_merge($all_manager_department_ids, $manager_department->getAllDescendantIds());
              }
              $idsArray = explode(',', $ids);
              $existingIds = UserJobHistory::whereIn('id', $idsArray)
              ->whereHas("user.departments", function($query) use($all_manager_department_ids) {
                $query->whereIn("departments.id",$all_manager_department_ids);
             })
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
              UserJobHistory::destroy($existingIds);


              return response()->json(["message" => "data deleted sussfully","deleted_ids" => $existingIds], 200);
          } catch (Exception $e) {

              return $this->sendError($e, 500, $request);
          }
      }
}
