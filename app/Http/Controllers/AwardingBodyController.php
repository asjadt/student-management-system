<?php







namespace App\Http\Controllers;

use App\Http\Requests\AwardingBodyCreateRequest;
use App\Http\Requests\AwardingBodyUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\AwardingBody;
use App\Models\DisabledAwardingBody;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AwardingBodyController extends Controller
{

    use ErrorUtil, UserActivityUtil, BusinessUtil;


    /**
*
* @OA\Post(
*      path="/v1.0/awarding-bodies",
*      operationId="createAwardingBody",
*      tags={"awarding_bodies"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to store awarding bodies",
*      description="This method is to store awarding bodies",
*
*  @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
* @OA\Property(property="name", type="string", format="string", example="name"),
* @OA\Property(property="description", type="string", format="string", example="description"),
* @OA\Property(property="accreditation_start_date", type="string", format="string", example="accreditation_start_date"),
* @OA\Property(property="accreditation_expiry_date", type="string", format="string", example="accreditation_expiry_date"),
* @OA\Property(property="logo", type="string", format="string", example="logo"),
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

public function createAwardingBody(AwardingBodyCreateRequest $request)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
return DB::transaction(function () use ($request) {
if (!auth()->user()->hasPermissionTo('awarding_body_create')) {
return response()->json([
   "message" => "You can not perform this action"
], 401);
}

$request_data = $request->validated();

      $request_data["is_active"] = 1;

      $request_data["is_default"] = 0;




$request_data["created_by"] = auth()->user()->id;
$request_data["business_id"] = auth()->user()->business_id;

if (empty(auth()->user()->business_id)) {
$request_data["business_id"] = NULL;
if (auth()->user()->hasRole('superadmin')) {
   $request_data["is_default"] = 1;
}
}




$awarding_body =  AwardingBody::create($request_data);




return response($awarding_body, 201);
});
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}
    /**
*
* @OA\Put(
*      path="/v1.0/awarding-bodies",
*      operationId="updateAwardingBody",
*      tags={"awarding_bodies"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to update awarding bodies ",
*      description="This method is to update awarding bodies ",
*
*  @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
*      @OA\Property(property="id", type="number", format="number", example="1"),
* @OA\Property(property="name", type="string", format="string", example="name"),
* @OA\Property(property="description", type="string", format="string", example="description"),
* @OA\Property(property="accreditation_start_date", type="string", format="string", example="accreditation_start_date"),
* @OA\Property(property="accreditation_expiry_date", type="string", format="string", example="accreditation_expiry_date"),
* @OA\Property(property="logo", type="string", format="string", example="logo"),
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

public function updateAwardingBody(AwardingBodyUpdateRequest $request)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
return DB::transaction(function () use ($request) {
if (!auth()->user()->hasPermissionTo('awarding_body_update')) {
return response()->json([
   "message" => "You can not perform this action"
], 401);
}
$request_data = $request->validated();



$awarding_body_query_params = [
"id" => $request_data["id"],
];

$awarding_body = AwardingBody::where($awarding_body_query_params)->first();

if ($awarding_body) {
$awarding_body->fill(collect($request_data)->only([

"name",
"description",
"accreditation_start_date",
"accreditation_expiry_date",
"logo",
// "is_default",
// "is_active",
// "business_id",
// "created_by"
])->toArray());
$awarding_body->save();
} else {
return response()->json([
   "message" => "something went wrong."
], 500);
}




return response($awarding_body, 201);
});
} catch (Exception $e) {
error_log($e->getMessage());
return $this->sendError($e, 500, $request);
}
}

/**
*
* @OA\Put(
*      path="/v1.0/awarding-bodies/toggle-active",
*      operationId="toggleActiveAwardingBody",
*      tags={"awarding_bodies"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to toggle awarding bodies",
*      description="This method is to toggle awarding bodies",
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

public function toggleActiveAwardingBody(GetIdRequest $request)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
if (!$request->user()->hasPermissionTo('awarding_body_activate')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}
$request_data = $request->validated();

$awarding_body =  AwardingBody::where([
"id" => $request_data["id"],
])
->first();
if (!$awarding_body) {

return response()->json([
"message" => "no data found"
], 404);
}




$this->toggleActivation(
AwardingBody::class,
DisabledAwardingBody::class,
'awarding_body',
$request_data["id"],
auth()->user()
);



return response()->json(['message' => 'awarding body status updated successfully'], 200);
} catch (Exception $e) {
error_log($e->getMessage());
return $this->sendError($e, 500, $request);
}
}



    /**
*
* @OA\Get(
*      path="/v1.0/awarding-bodies",
*      operationId="getAwardingBodies",
*      tags={"awarding_bodies"},
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
*         name="start_accreditation_start_date",
*         in="query",
*         description="start_accreditation_start_date",
*         required=true,
*  example="6"
*      ),
*         @OA\Parameter(
*         name="end_accreditation_start_date",
*         in="query",
*         description="end_accreditation_start_date",
*         required=true,
*  example="6"
*      ),



*         @OA\Parameter(
*         name="start_accreditation_expiry_date",
*         in="query",
*         description="start_accreditation_expiry_date",
*         required=true,
*  example="6"
*      ),
*         @OA\Parameter(
*         name="end_accreditation_expiry_date",
*         in="query",
*         description="end_accreditation_expiry_date",
*         required=true,
*  example="6"
*      ),



*         @OA\Parameter(
*         name="logo",
*         in="query",
*         description="logo",
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
* *  @OA\Parameter(
* name="is_single_search",
* in="query",
* description="is_single_search",
* required=true,
* example="ASC"
* ),




*      summary="This method is to get awarding bodies  ",
*      description="This method is to get awarding bodies ",
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

public function getAwardingBodies(Request $request)
{
try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
if (!$request->user()->hasPermissionTo('awarding_body_view')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}
$created_by  = NULL;
if(auth()->user()->business) {
$created_by = auth()->user()->business->created_by;
}



$awarding_bodies = AwardingBody::
when(empty(auth()->user()->business_id), function ($query) use ($request, $created_by) {
$query->when(auth()->user()->hasRole('superadmin'), function ($query) use ($request) {
$query->forSuperAdmin('awarding_bodies');
}, function ($query) use ($request, $created_by) {
$query->forNonSuperAdmin('awarding_bodies', 'disabled_awarding_bodies', $created_by);
});
})




->when(!empty($request->id), function ($query) use ($request) {
return $query->where('awarding_bodies.id', $request->id);
})

->when(!empty($request->name), function ($query) use ($request) {
return $query->where('awarding_bodies.id', $request->string);
})





->when(!empty($request->description), function ($query) use ($request) {
return $query->where('awarding_bodies.id', $request->string);
})





->when(!empty($request->start_accreditation_start_date), function ($query) use ($request) {
return $query->where('awarding_bodies.accreditation_start_date', ">=", $request->start_accreditation_start_date);
})
->when(!empty($request->end_accreditation_start_date), function ($query) use ($request) {
return $query->where('awarding_bodies.accreditation_start_date', "<=", ($request->end_accreditation_start_date . ' 23:59:59'));
})





->when(!empty($request->start_accreditation_expiry_date), function ($query) use ($request) {
return $query->where('awarding_bodies.accreditation_expiry_date', ">=", $request->start_accreditation_expiry_date);
})
->when(!empty($request->end_accreditation_expiry_date), function ($query) use ($request) {
return $query->where('awarding_bodies.accreditation_expiry_date', "<=", ($request->end_accreditation_expiry_date . ' 23:59:59'));
})





->when(!empty($request->logo), function ($query) use ($request) {
return $query->where('awarding_bodies.id', $request->string);
})





->when(!empty($request->search_key), function ($query) use ($request) {
return $query->where(function ($query) use ($request) {
$term = $request->search_key;
$query

->orWhere("awarding_bodies.name", "like", "%" . $term . "%")
->where("awarding_bodies.description", "like", "%" . $term . "%")
->orWhere("awarding_bodies.logo", "like", "%" . $term . "%")
;
});


})


->when(!empty($request->start_date), function ($query) use ($request) {
return $query->where('awarding_bodies.created_at', ">=", $request->start_date);
})
->when(!empty($request->end_date), function ($query) use ($request) {
return $query->where('awarding_bodies.created_at', "<=", ($request->end_date . ' 23:59:59'));
})
->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
return $query->orderBy("awarding_bodies.id", $request->order_by);
}, function ($query) {
return $query->orderBy("awarding_bodies.id", "DESC");
})
->when($request->filled("is_single_search") && $request->boolean("is_single_search"), function ($query) use ($request) {
return $query->first();
}, function($query) {
return $query->when(!empty(request()->per_page), function ($query) {
return $query->paginate(request()->per_page);
}, function ($query) {
return $query->get();
});
});

if($request->filled("is_single_search") && empty($awarding_bodies)){
throw new Exception("No data found",404);
}


return response()->json($awarding_bodies, 200);
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}

    /**
*
*     @OA\Delete(
*      path="/v1.0/awarding-bodies/{ids}",
*      operationId="deleteAwardingBodiesByIds",
*      tags={"awarding_bodies"},
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
*      summary="This method is to delete awarding body by id",
*      description="This method is to delete awarding body by id",
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

public function deleteAwardingBodiesByIds(Request $request, $ids)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
if (!$request->user()->hasPermissionTo('awarding_body_delete')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}

$idsArray = explode(',', $ids);
$existingIds = AwardingBody::whereIn('id', $idsArray)
->when(empty(auth()->user()->business_id), function ($query) use ($request) {
if ($request->user()->hasRole("superadmin")) {
return $query->where('awarding_bodies.business_id', NULL)
->where('awarding_bodies.is_default', 1);
} else {
return $query->where('awarding_bodies.business_id', NULL)
->where('awarding_bodies.is_default', 0)
->where('awarding_bodies.created_by', $request->user()->id);
}
})
->when(!empty(auth()->user()->business_id), function ($query) use ($request) {
return $query->where('awarding_bodies.business_id', auth()->user()->business_id)
->where('awarding_bodies.is_default', 0);
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





AwardingBody::destroy($existingIds);


return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}




}







