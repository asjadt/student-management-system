<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgencyUpdateRequest;
use App\Http\Requests\AuthRegisterAgencyRequest;

use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\SendPassword;
use App\Models\Agency;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AgencyController extends Controller
{
    use ErrorUtil, BusinessUtil, UserActivityUtil, BasicUtil;

     /**
     *
     * @OA\Post(
     *      path="/v1.0/auth/register-with-agency",
     *      operationId="registerUserWithAgency",
     *      tags={"agency_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store user with agency",
     *      description="This method is to store user with agency",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","agency"},
     *             @OA\Property(property="user", type="string", format="array",example={
     * "first_Name":"Rifat",
     * "last_Name":"Al-Ashwad",
     * "middle_Name":"Al-Ashwad",
     * "email":"rifatalashwad@gmail.com",
     *  "password":"12345678",
     *  "password_confirmation":"12345678",
     *  "phone":"01771034383",
     *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     * "send_password":1,
     * "gender":"male"
     *
     * }),
     *
     *
     * * @OA\Property(
 *     property="agency",
 *     type="object",
 *     example={
 *         "agency_name": "Best Agency",
 *         "contact_person": "John Doe",
 *         "email": "johndoe@example.com",
 *         "phone_number": "01771034383",
 *         "address": "123 Main Street, Dhaka, Bangladesh",
 *         "commission_rate": 15.50,
 *         "business_id" : 1,
 *     }
 * ),

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
    public function registerUserWithAgency(AuthRegisterAgencyRequest $request)
    {
        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('agency_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $request_data = $request->validated();

            // user info starts ##############

            $password = $request_data['user']['password'];
            $request_data['user']['password'] = Hash::make($password);




            $request_data['user']['remember_token'] = Str::random(10);
            $request_data['user']['is_active'] = true;
            $request_data['user']['created_by'] = $request->user()->id;


            $user =  User::create($request_data['user']);

            $user->assignRole('agency');
            // end user info ##############


            //  agency info ##############

            $request_data['agency']['owner_id'] = $user->id;
            $request_data['agency']['created_by'] = request()->user()->id;
            $request_data['agency']['is_active'] = true;


            $agency =  Agency::create($request_data['agency']);

            $user->email_verified_at = now();
            $token = Str::random(30);
            $user->resetPasswordToken = $token;
            $user->resetPasswordExpires = Carbon::now()->subDays(-1);
            $user->save();




            //  if($request_data['user']['send_password']) {
            if (env("SEND_EMAIL") == true) {
                Mail::to($request_data['user']['email'])->send(new SendPassword($user, $password));
            }

            // }


            return response()->json([
                "user" => $user,
                "agency" => $agency
            ], 201);
        } catch (Exception $e) {


            return $this->sendError($e, 500, $request);
        }
    }




     /**
     *
     * @OA\Put(
     *      path="/v1.0/agencies",
     *      operationId="updateAgency",
     *      tags={"agency_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update user with agency",
     *      description="This method is to update user with agency",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","agency"},
     *             @OA\Property(property="user", type="string", format="array",example={
     *  * "id":1,
     * "first_Name":"Rifat",
     *  * "middle_Name":"Al-Ashwad",
     *
     * "last_Name":"Al-Ashwad",
     * "email":"rifatalashwad@gmail.com",
     *  "password":"12345678",
     *  "password_confirmation":"12345678",
     *  "phone":"01771034383",
     *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     * "gender":"male"
     *
     *
     * }),
     *
     * * @OA\Property(
 *     property="agency",
 *     type="object",
 *     example={
 *         "agency_name": "Best Agency",
 *         "contact_person": "John Doe",
 *         "email": "johndoe@example.com",
 *         "phone_number": "01771034383",
 *         "address": "123 Main Street, Dhaka, Bangladesh",
 *         "commission_rate": 15.50
 *     }
 * ),

     *       ),
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
    public function updateAgency(AgencyUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return  DB::transaction(function () use (&$request) {

                if (!$request->user()->hasPermissionTo('agency_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }


                $request_data = $request->validated();
                //    user email check
                $userPrev = User::where([
                    "id" => $request_data["user"]["id"]
                ]);
                if (!$request->user()->hasRole('superadmin')) {
                    $userPrev  = $userPrev->where(function ($query) {
                        return  $query->where('created_by', auth()->user()->id)
                            ->orWhere('id', auth()->user()->id);
                    });
                }
                $userPrev = $userPrev->first();
                if (!$userPrev) {
                    $this->storeError(
                        "no data found",
                        404,
                        "front end error",
                        "front end error"
                    );
                    return response()->json([
                        "message" => "no user found with this id"
                    ], 404);
                }

                if (!empty($request_data['user']['password'])) {
                    $request_data['user']['password'] = Hash::make($request_data['user']['password']);
                } else {
                    unset($request_data['user']['password']);
                }
                $request_data['user']['is_active'] = true;
                $request_data['user']['remember_token'] = Str::random(10);



                $user  =  tap(User::where([
                    "id" => $request_data['user']["id"]
                ]))->update(
                    collect($request_data['user'])->only([
                        'first_Name',
                        'middle_Name',
                        'last_Name',
                        'phone',
                        'image',
                        'address_line_1',
                        'address_line_2',
                        'country',
                        'city',
                        'postcode',
                        'email',
                        'password',
                        "lat",
                        "long",
                        "gender"
                    ])->toArray()
                )
                    ->first();

                if (!$user) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }



                $agency = Agency::where(["id" => $request_data['agency']["id"]])
                ->where(function($query) {
                    $query->where("owner_id",auth()->user()->id)
                    ->orWhere("business_id",auth()->user()->business_id);
                })
                    ->first();

                if (!$agency) {
                    return response()->json([
                        "message" => "Agency not found"
                    ], 404);
                }

                $agency->fill($request_data['agency']);
                $agency->save();

                return response([
                    "user" => $user,
                    "agency" => $agency
                ], 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


 /**
     *
     * @OA\Get(
     *      path="/v1.0/agencies",
     *      operationId="getAgencies",
     *      tags={"agency_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *
     *      summary="This method is to get agencies",
     *      description="This method is to get agencies",
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

     public function getAgencies(Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity", "DUMMY description");

             if (!$request->user()->hasPermissionTo('agency_view')) {
                 return response()->json([
                     "message" => "You can not perform this action"
                 ], 401);
             }


             $query = Agency::where(function($query) {
                 $query->where("owner_id",auth()->user()->id)
                 ->orWhere("business_id",auth()->user()->business_id);
             });
            //  $query = $this->query_filters($query);
             $agencies = $this->retrieveData($query, "id","agencies");


             return response()->json($agencies, 200);

         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }

   /**
     *
     * @OA\Delete(
     *      path="/v1.0/agencies/{ids}",
     *      operationId="deleteAgenciesByIds",
     *      tags={"agency_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="ids",
     *         in="path",
     *         description="ids",
     *         required=true,
     *  example="6,7,8"
     *      ),
     *      summary="This method is to delete agency by id",
     *      description="This method is to delete agency by id",
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

     public function deleteAgenciesByIds(Request $request, $ids)
     {

         try {
             $this->storeActivity($request, "DUMMY activity", "DUMMY description");
             if (!$request->user()->hasPermissionTo('agency_delete')) {
                 return response()->json([
                     "message" => "You can not perform this action"
                 ], 401);
             }

             $agency = Agency::where("business_id",auth()->user()->business_id)
                 ->where([
                     "id" => $ids
                 ])
                 ->first();

             if (!$agency) {
                 $this->storeError("Agency not found", 404, "Front-end error", "Front-end error");
                 return response()->json([
                     "message" => "The specified agency does not exist."
                 ], 404);
             }


             $agency->delete();



             return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $ids], 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }



}
