<?php





namespace App\Http\Controllers;

use App\Http\Requests\InstallmentPlanCreateRequest;
use App\Http\Requests\InstallmentPlanUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\InstallmentPlan;
use App\Models\DisabledInstallmentPlan;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstallmentPlanController extends Controller
{

    use ErrorUtil, UserActivityUtil, BusinessUtil;


    /**
*
* @OA\Post(
*      path="/v1.0/installment-plans",
*      operationId="createInstallmentPlan",
*      tags={"installment_plans"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to store installment plans",
*      description="This method is to store installment plans",
*
*  @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(

* @OA\Property(property="course_id", type="string", format="string", example="course_id"),
* @OA\Property(property="number_of_installments", type="string", format="string", example="number_of_installments"),
* @OA\Property(property="installment_amount", type="string", format="string", example="installment_amount"),
* @OA\Property(property="start_date", type="string", format="string", example="start_date"),
* @OA\Property(property="end_date", type="string", format="string", example="end_date"),
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

public function createInstallmentPlan(InstallmentPlanCreateRequest $request)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
return DB::transaction(function () use ($request) {
if (!auth()->user()->hasPermissionTo('installment_plan_create')) {
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




$installment_plan =  InstallmentPlan::create($request_data);




return response($installment_plan, 201);
});
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}
    /**
*
* @OA\Put(
*      path="/v1.0/installment-plans",
*      operationId="updateInstallmentPlan",
*      tags={"installment_plans"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to update installment plans ",
*      description="This method is to update installment plans ",
*
*  @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
*      @OA\Property(property="id", type="number", format="number", example="1"),

* @OA\Property(property="course_id", type="string", format="string", example="course_id"),
* @OA\Property(property="number_of_installments", type="string", format="string", example="number_of_installments"),
* @OA\Property(property="installment_amount", type="string", format="string", example="installment_amount"),
* @OA\Property(property="start_date", type="string", format="string", example="start_date"),
* @OA\Property(property="end_date", type="string", format="string", example="end_date"),
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

public function updateInstallmentPlan(InstallmentPlanUpdateRequest $request)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
return DB::transaction(function () use ($request) {
if (!auth()->user()->hasPermissionTo('installment_plan_update')) {
return response()->json([
   "message" => "You can not perform this action"
], 401);
}
$request_data = $request->validated();



$installment_plan_query_params = [
"id" => $request_data["id"],
];

$installment_plan = InstallmentPlan::where($installment_plan_query_params)->first();

if ($installment_plan) {
$installment_plan->fill(collect($request_data)->only([


"course_id",
"number_of_installments",
"installment_amount",
"start_date",
"end_date",
// "is_default",
// "is_active",
// "business_id",
// "created_by"
])->toArray());
$installment_plan->save();
} else {
return response()->json([
   "message" => "something went wrong."
], 500);
}




return response($installment_plan, 201);
});
} catch (Exception $e) {
error_log($e->getMessage());
return $this->sendError($e, 500, $request);
}
}


/**
*
* @OA\Put(
*      path="/v1.0/installment-plans/toggle-active",
*      operationId="toggleActiveInstallmentPlan",
*      tags={"installment_plans"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to toggle installment plans",
*      description="This method is to toggle installment plans",
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

public function toggleActiveInstallmentPlan(GetIdRequest $request)
{

try {

$this->storeActivity($request, "DUMMY activity", "DUMMY description");

if (!$request->user()->hasPermissionTo('installment_plan_activate')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}
$request_data = $request->validated();

$installment_plan =  InstallmentPlan::where([
"id" => $request_data["id"],
])
->first();
if (!$installment_plan) {

return response()->json([
"message" => "no data found"
], 404);
}

$installment_plan->update([
'is_active' => !$installment_plan->is_active
]);




return response()->json(['message' => 'installment plan status updated successfully'], 200);
} catch (Exception $e) {
error_log($e->getMessage());
return $this->sendError($e, 500, $request);
}
}



    /**
*
* @OA\Get(
*      path="/v1.0/installment-plans",
*      operationId="getInstallmentPlans",
*      tags={"installment_plans"},
*       security={
*           {"bearerAuth": {}}
*       },







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




*      summary="This method is to get installment plans  ",
*      description="This method is to get installment plans ",
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

public function getInstallmentPlans(Request $request)
{
try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
if (!$request->user()->hasPermissionTo('installment_plan_view')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}
$created_by  = NULL;
if(auth()->user()->business) {
$created_by = auth()->user()->business->created_by;
}



$installment_plans = InstallmentPlan::
where('installment_plans.business_id', auth()->user()->business_id)











->when(!empty($request->search_key), function ($query) use ($request) {
return $query->where(function ($query) use ($request) {
$term = $request->search_key;
$query

;
});


})


->when(!empty($request->start_date), function ($query) use ($request) {
return $query->where('installment_plans.created_at', ">=", $request->start_date);
})
->when(!empty($request->end_date), function ($query) use ($request) {
return $query->where('installment_plans.created_at', "<=", ($request->end_date . ' 23:59:59'));
})
->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
return $query->orderBy("installment_plans.id", $request->order_by);
}, function ($query) {
return $query->orderBy("installment_plans.id", "DESC");
})
->when($request->filled("id"), function ($query) use ($request) {
return $query
->where("installment_plans.id",$request->input("id"))
->first();
}, function($query) {
return $query->when(!empty(request()->per_page), function ($query) {
return $query->paginate(request()->per_page);
}, function ($query) {
return $query->get();
});
});

if($request->filled("id") && empty($installment_plans)){
throw new Exception("No data found",404);
}


return response()->json($installment_plans, 200);
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}

    /**
*
*     @OA\Delete(
*      path="/v1.0/installment-plans/{ids}",
*      operationId="deleteInstallmentPlansByIds",
*      tags={"installment_plans"},
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
*      summary="This method is to delete installment plan by id",
*      description="This method is to delete installment plan by id",
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

public function deleteInstallmentPlansByIds(Request $request, $ids)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
if (!$request->user()->hasPermissionTo('installment_plan_delete')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}

$idsArray = explode(',', $ids);
$existingIds = InstallmentPlan::whereIn('id', $idsArray)
->where('installment_plans.business_id', auth()->user()->business_id)

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





InstallmentPlan::destroy($existingIds);


return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}




}







