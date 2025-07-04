<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReminderCreateRequest;
use App\Http\Requests\ReminderUpdateRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Reminder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReminderController extends Controller
{
    use ErrorUtil, UserActivityUtil, BusinessUtil,BasicUtil;
    /**
     *
     * @OA\Post(
     *      path="/v1.0/reminders",
     *      operationId="createReminder",
     *      tags={"reminders"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store reminder",
     *      description="This method is to store reminder",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *
     *
 *     @OA\Property(property="title", type="string", format="string", example="Your Title"),
 *     @OA\Property(property="entity_name", type="string", format="string", example="Your Entity Name"),
 *     @OA\Property(property="duration", type="integer", format="int", example=10),
 *     @OA\Property(property="duration_unit", type="string", format="string", example="days", enum={"days", "weeks", "months"}),
 *     @OA\Property(property="send_time", type="string", format="string", example="before_expiry", enum={"before_expiry", "after_expiry"}),
 *     @OA\Property(property="frequency_after_first_reminder", type="integer", format="int", example=2),
 *  * *     @OA\Property(property="reminder_limit", type="integer", format="int", example=2),
 *     @OA\Property(property="keep_sending_until_update", type="boolean", format="boolean", example=true)
 *
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

     public function createReminder(ReminderCreateRequest $request)
     {
        DB::beginTransaction();
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");

            if (!auth()->user()->hasPermissionTo('reminder_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $request_data = $request->validated();
            $request_data["is_active"] = 1;
            $request_data["created_by"] = auth()->user()->id;
            $request_data["business_id"] = auth()->user()->business_id;

            $reminder =  Reminder::create($request_data);


                 DB::commit();
                 return response($reminder, 201);

         } catch (Exception $e) {
           DB::rollBack();
             return $this->sendError($e, 500, $request);
         }
     }

     /**
      *
      * @OA\Put(
      *      path="/v1.0/reminders",
      *      operationId="updateReminder",
      *      tags={"reminders"},
      *       security={
      *           {"bearerAuth": {}}
      *       },
      *      summary="This method is to update reminder ",
      *      description="This method is to update reminder",
      *
      *  @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
 *      @OA\Property(property="id", type="number", format="number", example="Updated Christmas"),
  *     @OA\Property(property="title", type="string", format="string", example="Your Title"),
  *     @OA\Property(property="entity_name", type="string", format="string", example="Your Entity Name"),
  *     @OA\Property(property="duration", type="integer", format="int", example=10),
  *     @OA\Property(property="duration_unit", type="string", format="string", example="days", enum={"days", "weeks", "months"}),
  *     @OA\Property(property="send_time", type="string", format="string", example="before_expiry", enum={"before_expiry", "after_expiry"}),
  *     @OA\Property(property="frequency_after_first_reminder", type="integer", format="int", example=2),
  * *     @OA\Property(property="reminder_limit", type="integer", format="int", example=2),
  *     @OA\Property(property="keep_sending_until_update", type="boolean", format="boolean", example=true)

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

     public function updateReminder(ReminderUpdateRequest $request)
     {

        DB::beginTransaction();
         try {

             $this->storeActivity($request, "DUMMY activity","DUMMY description");

             if (!auth()->user()->hasPermissionTo('reminder_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

                 $request_data = $request->validated();

                 $reminder  =  Reminder::where(
                     [
                         "id" => $request_data["id"],
                         "business_id" => auth()->user()->business_id
                     ]
                 )
                 ->first();

                 if (!$reminder) {
                     return response()->json([
                         "message" => "something went wrong."
                     ], 500);
                 }
                 $reminder->fill($request_data);
                 $reminder->save();

                 DB::commit();
                 return response($reminder, 201);
         } catch (Exception $e) {
               DB::rollBack();
             return $this->sendError($e, 500, $request);
         }
     }




     /**
      *
      * @OA\Get(
      *      path="/v1.0/reminders",
      *      operationId="getReminders",
      *      tags={"reminders"},
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

      *      summary="This method is to get reminders  ",
      *      description="This method is to get reminders ",
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

     public function getReminders(Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");

             if (!auth()->user()->hasPermissionTo('reminder_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }



             $query = Reminder::where(
                 [
                     "reminders.business_id" => auth()->user()->business_id
                 ]
             )
                 ->when(!empty($request->search_key), function ($query) use ($request) {
                     return $query->where(function ($query) use ($request) {
                         $term = $request->search_key;

                     });
                 })

                 ->when(!empty($request->start_date), function ($query) use ($request) {
                     return $query->where('reminders.created_at', ">=", $request->start_date);
                 })
                 ->when(!empty($request->end_date), function ($query) use ($request) {
                     return $query->where('reminders.created_at', "<=", ($request->end_date . ' 23:59:59'));
                 })
                 ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                     return $query->orderBy("reminders.id", $request->order_by);
                 }, function ($query) {
                     return $query->orderBy("reminders.id", "DESC");
                 });

                 $reminders = $this->retrieveData($query, "id","reminders");


             return response()->json($reminders, 200);

         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }




     /**
      *
      *     @OA\Delete(
      *      path="/v1.0/reminders/{ids}",
      *      operationId="deleteRemindersByIds",
      *      tags={"reminders"},
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
      *      summary="This method is to delete reminder by id",
      *      description="This method is to delete reminder by id",
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

     public function deleteRemindersByIds(Request $request, $ids)
     {

         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");

             if (!$request->user()->hasPermissionTo('reminder_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }


             $idsArray = explode(',', $ids);
             $existingIds = Reminder::where([
                 "business_id" => auth()->user()->business_id
             ])
                 ->whereIn('id', $idsArray)
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

             Reminder::destroy($existingIds);



             return response()->json(["message" => "data deleted sussfully","deleted_ids" => $existingIds], 200);


         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }
}
