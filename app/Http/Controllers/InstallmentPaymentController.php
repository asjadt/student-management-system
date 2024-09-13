<?php





namespace App\Http\Controllers;

use App\Http\Requests\InstallmentPaymentCreateRequest;
use App\Http\Requests\InstallmentPaymentUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\InstallmentPayment;
use App\Models\DisabledInstallmentPayment;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstallmentPaymentController extends Controller
{

    use ErrorUtil, UserActivityUtil, BusinessUtil;


    /**
*
* @OA\Post(
*      path="/v1.0/installment-payments",
*      operationId="createInstallmentPayment",
*      tags={"installment_payments"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to store installment payments",
*      description="This method is to store installment payments",
*
*  @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
* @OA\Property(property="installment_plan_id", type="string", format="string", example="installment_plan_id"),
* @OA\Property(property="amount_paid", type="string", format="string", example="amount_paid"),
* @OA\Property(property="payment_date", type="string", format="string", example="payment_date"),
* @OA\Property(property="status", type="string", format="string", example="status"),
* @OA\Property(property="student_id", type="string", format="string", example="student_id"),
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

public function createInstallmentPayment(InstallmentPaymentCreateRequest $request)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
return DB::transaction(function () use ($request) {
if (!auth()->user()->hasPermissionTo('installment_payment_create')) {
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




$installment_payment =  InstallmentPayment::create($request_data);




return response($installment_payment, 201);
});
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}
    /**
*
* @OA\Put(
*      path="/v1.0/installment-payments",
*      operationId="updateInstallmentPayment",
*      tags={"installment_payments"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to update installment payments ",
*      description="This method is to update installment payments ",
*
*  @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
*      @OA\Property(property="id", type="number", format="number", example="1"),
* @OA\Property(property="installment_plan_id", type="string", format="string", example="installment_plan_id"),
* @OA\Property(property="amount_paid", type="string", format="string", example="amount_paid"),
* @OA\Property(property="payment_date", type="string", format="string", example="payment_date"),
* @OA\Property(property="status", type="string", format="string", example="status"),
* @OA\Property(property="student_id", type="string", format="string", example="student_id"),
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

public function updateInstallmentPayment(InstallmentPaymentUpdateRequest $request)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
return DB::transaction(function () use ($request) {
if (!auth()->user()->hasPermissionTo('installment_payment_update')) {
return response()->json([
   "message" => "You can not perform this action"
], 401);
}
$request_data = $request->validated();



$installment_payment_query_params = [
"id" => $request_data["id"],
];

$installment_payment = InstallmentPayment::where($installment_payment_query_params)->first();

if ($installment_payment) {
$installment_payment->fill(collect($request_data)->only([

"installment_plan_id",
"amount_paid",
"payment_date",
"status",
"student_id",
// "is_default",
// "is_active",
// "business_id",
// "created_by"
])->toArray());
$installment_payment->save();
} else {
return response()->json([
   "message" => "something went wrong."
], 500);
}




return response($installment_payment, 201);
});
} catch (Exception $e) {
error_log($e->getMessage());
return $this->sendError($e, 500, $request);
}
}


/**
*
* @OA\Put(
*      path="/v1.0/installment-payments/toggle-active",
*      operationId="toggleActiveInstallmentPayment",
*      tags={"installment_payments"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to toggle installment payments",
*      description="This method is to toggle installment payments",
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

public function toggleActiveInstallmentPayment(GetIdRequest $request)
{

try {

$this->storeActivity($request, "DUMMY activity", "DUMMY description");

if (!$request->user()->hasPermissionTo('installment_payment_activate')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}
$request_data = $request->validated();

$installment_payment =  InstallmentPayment::where([
"id" => $request_data["id"],
])
->first();
if (!$installment_payment) {

return response()->json([
"message" => "no data found"
], 404);
}

$installment_payment->update([
'is_active' => !$installment_payment->is_active
]);




return response()->json(['message' => 'installment payment status updated successfully'], 200);
} catch (Exception $e) {
error_log($e->getMessage());
return $this->sendError($e, 500, $request);
}
}



    /**
*
* @OA\Get(
*      path="/v1.0/installment-payments",
*      operationId="getInstallmentPayments",
*      tags={"installment_payments"},
*       security={
*           {"bearerAuth": {}}
*       },



*         @OA\Parameter(
*         name="start_payment_date",
*         in="query",
*         description="start_payment_date",
*         required=true,
*  example="6"
*      ),
*         @OA\Parameter(
*         name="end_payment_date",
*         in="query",
*         description="end_payment_date",
*         required=true,
*  example="6"
*      ),



*         @OA\Parameter(
*         name="status",
*         in="query",
*         description="status",
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




*      summary="This method is to get installment payments  ",
*      description="This method is to get installment payments ",
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

public function getInstallmentPayments(Request $request)
{
try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
if (!$request->user()->hasPermissionTo('installment_payment_view')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}
$created_by  = NULL;
if(auth()->user()->business) {
$created_by = auth()->user()->business->created_by;
}



$installment_payments = InstallmentPayment::
where('installment_payments.business_id', auth()->user()->business_id)







->when(!empty($request->start_payment_date), function ($query) use ($request) {
return $query->where('installment_payments.payment_date', ">=", $request->start_payment_date);
})
->when(!empty($request->end_payment_date), function ($query) use ($request) {
return $query->where('installment_payments.payment_date', "<=", ($request->end_payment_date . ' 23:59:59'));
})




->when(!empty($request->status), function ($query) use ($request) {
return $query->where('installment_payments.status', $request->status);
})





->when(!empty($request->search_key), function ($query) use ($request) {
return $query->where(function ($query) use ($request) {
$term = $request->search_key;
$query

->orWhere("installment_payments.status", "like", "%" . $term . "%")
;
});


})


->when(!empty($request->start_date), function ($query) use ($request) {
return $query->where('installment_payments.created_at', ">=", $request->start_date);
})
->when(!empty($request->end_date), function ($query) use ($request) {
return $query->where('installment_payments.created_at', "<=", ($request->end_date . ' 23:59:59'));
})
->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
return $query->orderBy("installment_payments.id", $request->order_by);
}, function ($query) {
return $query->orderBy("installment_payments.id", "DESC");
})
->when($request->filled("id"), function ($query) use ($request) {
return $query
->where("installment_payments.id",$request->input("id"))
->first();
}, function($query) {
return $query->when(!empty(request()->per_page), function ($query) {
return $query->paginate(request()->per_page);
}, function ($query) {
return $query->get();
});
});

if($request->filled("id") && empty($installment_payments)){
throw new Exception("No data found",404);
}


return response()->json($installment_payments, 200);
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}

    /**
*
*     @OA\Delete(
*      path="/v1.0/installment-payments/{ids}",
*      operationId="deleteInstallmentPaymentsByIds",
*      tags={"installment_payments"},
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
*      summary="This method is to delete installment payment by id",
*      description="This method is to delete installment payment by id",
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

public function deleteInstallmentPaymentsByIds(Request $request, $ids)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
if (!$request->user()->hasPermissionTo('installment_payment_delete')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}

$idsArray = explode(',', $ids);
$existingIds = InstallmentPayment::whereIn('id', $idsArray)
->where('installment_payments.business_id', auth()->user()->business_id)

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





InstallmentPayment::destroy($existingIds);


return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}




}







