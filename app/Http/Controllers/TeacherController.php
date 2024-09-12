<?php





namespace App\Http\Controllers;

use App\Http\Requests\TeacherCreateRequest;
use App\Http\Requests\TeacherUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Teacher;
use App\Models\DisabledTeacher;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{

    use ErrorUtil, UserActivityUtil, BusinessUtil;


    /**
*
* @OA\Post(
*      path="/v1.0/teachers",
*      operationId="createTeacher",
*      tags={"teachers"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to store teachers",
*      description="This method is to store teachers",
*
*  @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
* @OA\Property(property="first_name", type="string", format="string", example="first_name"),
* @OA\Property(property="middle_name", type="string", format="string", example="middle_name"),
* @OA\Property(property="last_name", type="string", format="string", example="last_name"),
* @OA\Property(property="email", type="string", format="string", example="email"),
* @OA\Property(property="phone", type="string", format="string", example="phone"),
* @OA\Property(property="qualification", type="string", format="string", example="qualification"),
* @OA\Property(property="hire_date", type="string", format="string", example="hire_date"),
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

public function createTeacher(TeacherCreateRequest $request)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
return DB::transaction(function () use ($request) {
if (!auth()->user()->hasPermissionTo('teacher_create')) {
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




$teacher =  Teacher::create($request_data);




return response($teacher, 201);
});
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}
    /**
*
* @OA\Put(
*      path="/v1.0/teachers",
*      operationId="updateTeacher",
*      tags={"teachers"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to update teachers ",
*      description="This method is to update teachers ",
*
*  @OA\RequestBody(
*         required=true,
*         @OA\JsonContent(
*      @OA\Property(property="id", type="number", format="number", example="1"),
* @OA\Property(property="first_name", type="string", format="string", example="first_name"),
* @OA\Property(property="middle_name", type="string", format="string", example="middle_name"),
* @OA\Property(property="last_name", type="string", format="string", example="last_name"),
* @OA\Property(property="email", type="string", format="string", example="email"),
* @OA\Property(property="phone", type="string", format="string", example="phone"),
* @OA\Property(property="qualification", type="string", format="string", example="qualification"),
* @OA\Property(property="hire_date", type="string", format="string", example="hire_date"),
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

public function updateTeacher(TeacherUpdateRequest $request)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
return DB::transaction(function () use ($request) {
if (!auth()->user()->hasPermissionTo('teacher_update')) {
return response()->json([
   "message" => "You can not perform this action"
], 401);
}
$request_data = $request->validated();



$teacher_query_params = [
"id" => $request_data["id"],
];

$teacher = Teacher::where($teacher_query_params)->first();

if ($teacher) {
$teacher->fill(collect($request_data)->only([

"first_name",
"middle_name",
"last_name",
"email",
"phone",
"qualification",
"hire_date",
// "is_default",
// "is_active",
// "business_id",
// "created_by"
])->toArray());
$teacher->save();
} else {
return response()->json([
   "message" => "something went wrong."
], 500);
}




return response($teacher, 201);
});
} catch (Exception $e) {
error_log($e->getMessage());
return $this->sendError($e, 500, $request);
}
}


/**
*
* @OA\Put(
*      path="/v1.0/teachers/toggle-active",
*      operationId="toggleActiveTeacher",
*      tags={"teachers"},
*       security={
*           {"bearerAuth": {}}
*       },
*      summary="This method is to toggle teachers",
*      description="This method is to toggle teachers",
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

public function toggleActiveTeacher(GetIdRequest $request)
{

try {

$this->storeActivity($request, "DUMMY activity", "DUMMY description");

if (!$request->user()->hasPermissionTo('teacher_activate')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}
$request_data = $request->validated();

$teacher =  Teacher::where([
"id" => $request_data["id"],
])
->first();
if (!$teacher) {

return response()->json([
"message" => "no data found"
], 404);
}

$teacher->update([
'is_active' => !$teacher->is_active
]);




return response()->json(['message' => 'teacher status updated successfully'], 200);
} catch (Exception $e) {
error_log($e->getMessage());
return $this->sendError($e, 500, $request);
}
}



    /**
*
* @OA\Get(
*      path="/v1.0/teachers",
*      operationId="getTeachers",
*      tags={"teachers"},
*       security={
*           {"bearerAuth": {}}
*       },

*         @OA\Parameter(
*         name="first_name",
*         in="query",
*         description="first_name",
*         required=true,
*  example="6"
*      ),



*         @OA\Parameter(
*         name="middle_name",
*         in="query",
*         description="middle_name",
*         required=true,
*  example="6"
*      ),



*         @OA\Parameter(
*         name="last_name",
*         in="query",
*         description="last_name",
*         required=true,
*  example="6"
*      ),



*         @OA\Parameter(
*         name="email",
*         in="query",
*         description="email",
*         required=true,
*  example="6"
*      ),



*         @OA\Parameter(
*         name="phone",
*         in="query",
*         description="phone",
*         required=true,
*  example="6"
*      ),



*         @OA\Parameter(
*         name="qualification",
*         in="query",
*         description="qualification",
*         required=true,
*  example="6"
*      ),



*         @OA\Parameter(
*         name="start_hire_date",
*         in="query",
*         description="start_hire_date",
*         required=true,
*  example="6"
*      ),
*         @OA\Parameter(
*         name="end_hire_date",
*         in="query",
*         description="end_hire_date",
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




*      summary="This method is to get teachers  ",
*      description="This method is to get teachers ",
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

public function getTeachers(Request $request)
{
try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
if (!$request->user()->hasPermissionTo('teacher_view')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}
$created_by  = NULL;
if(auth()->user()->business) {
$created_by = auth()->user()->business->created_by;
}



$teachers = Teacher::
where('teachers.business_id', auth()->user()->business_id)



->when(!empty($request->id), function ($query) use ($request) {
return $query->where('teachers.id', $request->id);
})

->when(!empty($request->first_name), function ($query) use ($request) {
return $query->where('teachers.id', $request->string);
})





->when(!empty($request->middle_name), function ($query) use ($request) {
return $query->where('teachers.id', $request->string);
})





->when(!empty($request->last_name), function ($query) use ($request) {
return $query->where('teachers.id', $request->string);
})





->when(!empty($request->email), function ($query) use ($request) {
return $query->where('teachers.id', $request->string);
})





->when(!empty($request->phone), function ($query) use ($request) {
return $query->where('teachers.id', $request->string);
})





->when(!empty($request->qualification), function ($query) use ($request) {
return $query->where('teachers.id', $request->string);
})





->when(!empty($request->start_hire_date), function ($query) use ($request) {
return $query->where('teachers.hire_date', ">=", $request->start_hire_date);
})
->when(!empty($request->end_hire_date), function ($query) use ($request) {
return $query->where('teachers.hire_date', "<=", ($request->end_hire_date . ' 23:59:59'));
})





->when(!empty($request->search_key), function ($query) use ($request) {
return $query->where(function ($query) use ($request) {
$term = $request->search_key;
$query

->orWhere("teachers.first_name", "like", "%" . $term . "%")
->where("teachers.middle_name", "like", "%" . $term . "%")
->orWhere("teachers.last_name", "like", "%" . $term . "%")
->orWhere("teachers.email", "like", "%" . $term . "%")
->orWhere("teachers.phone", "like", "%" . $term . "%")
->orWhere("teachers.qualification", "like", "%" . $term . "%")
;
});


})


->when(!empty($request->start_date), function ($query) use ($request) {
return $query->where('teachers.created_at', ">=", $request->start_date);
})
->when(!empty($request->end_date), function ($query) use ($request) {
return $query->where('teachers.created_at', "<=", ($request->end_date . ' 23:59:59'));
})
->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
return $query->orderBy("teachers.id", $request->order_by);
}, function ($query) {
return $query->orderBy("teachers.id", "DESC");
})
->when($request->filled("id"), function ($query) use ($request) {
return $query
->where("teachers.id",$request->input("id"))
->first();
}, function($query) {
return $query->when(!empty(request()->per_page), function ($query) {
return $query->paginate(request()->per_page);
}, function ($query) {
return $query->get();
});
});

if($request->filled("id") && empty($teachers)){
throw new Exception("No data found",404);
}


return response()->json($teachers, 200);
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}

    /**
*
*     @OA\Delete(
*      path="/v1.0/teachers/{ids}",
*      operationId="deleteTeachersByIds",
*      tags={"teachers"},
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
*      summary="This method is to delete teacher by id",
*      description="This method is to delete teacher by id",
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

public function deleteTeachersByIds(Request $request, $ids)
{

try {
$this->storeActivity($request, "DUMMY activity", "DUMMY description");
if (!$request->user()->hasPermissionTo('teacher_delete')) {
return response()->json([
"message" => "You can not perform this action"
], 401);
}

$idsArray = explode(',', $ids);
$existingIds = Teacher::whereIn('id', $idsArray)
->where('teachers.business_id', auth()->user()->business_id)

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





Teacher::destroy($existingIds);


return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
} catch (Exception $e) {

return $this->sendError($e, 500, $request);
}
}




}







