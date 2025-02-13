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
            // Store the activity of registering a user with an agency
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the permission to create an agency
            // If not, return a 401 Unauthorized response
            if (!$request->user()->hasPermissionTo('agency_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Get the validated request data
            $request_data = $request->validated();

            // Get the password from the validated request data
            $password = $request_data['user']['password'];

            // Hash the password
            $request_data['user']['password'] = Hash::make($password);

            // Add a remember token to the user data
            $request_data['user']['remember_token'] = Str::random(10);

            // Set the user as active
            $request_data['user']['is_active'] = true;

            // Set the created by field to the current user's id
            $request_data['user']['created_by'] = $request->user()->id;


            // Create a new user with the validated data
            $user =  User::create($request_data['user']);

            // Assign the agency role to the user
            $user->assignRole('agency');

            // Set the email verified at field to now
            $user->email_verified_at = now();

            // Generate a random token for the password reset
            $token = Str::random(30);

            // Set the reset password token and expiration
            $user->resetPasswordToken = $token;
            $user->resetPasswordExpires = Carbon::now()->subDays(-1);

            // Save the user
            $user->save();

            // Create a new agency with the validated data
            $agency =  Agency::create($request_data['agency']);

            // Set the owner id of the agency to the user's id
            $agency->owner_id = $user->id;

            // Set the created by field to the current user's id
            $agency->created_by = request()->user()->id;

            // Set the agency as active
            $agency->is_active = true;

            // Save the agency
            $agency->save();

            // If the send_password flag is set to true, send a password reset email to the user
            if (env("SEND_EMAIL") == true) {
                Mail::to($request_data['user']['email'])->send(new SendPassword($user, $password));
            }

            // Return a 201 Created response with the user and agency data
            return response()->json([
                "user" => $user,
                "agency" => $agency
            ], 201);
        } catch (Exception $e) {

            // If there is an exception, return a 500 Internal Server Error response with the error message
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
            // Log the activity of updating an agency
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Execute database operations within a transaction
            return DB::transaction(function () use (&$request) {

                // Check if the user has permission to update the agency
                if (!$request->user()->hasPermissionTo('agency_update')) {
                    // If not, return a 401 Unauthorized response
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Get the validated request data
                $request_data = $request->validated();

                // Check for the existence of the user by ID
                $userPrev = User::where([
                    "id" => $request_data["user"]["id"]
                ]);

                // Restrict access for non-superadmin users
                if (!$request->user()->hasRole('superadmin')) {
                    $userPrev  = $userPrev->where(function ($query) {
                        return  $query->where('created_by', auth()->user()->id)
                            ->orWhere('id', auth()->user()->id);
                    });
                }

                // Find the user or return a 404 response if not found
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

                // Check if a new password is provided and hash it
                if (!empty($request_data['user']['password'])) {
                    $request_data['user']['password'] = Hash::make($request_data['user']['password']);
                } else {
                    // Remove password from the request data if not provided
                    unset($request_data['user']['password']);
                }

                // Set user as active and generate a new remember token
                $request_data['user']['is_active'] = true;
                $request_data['user']['remember_token'] = Str::random(10);

                // Update the user details
                $user  = tap(User::where([
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
                )->first();

                // Check if the user update was successful
                if (!$user) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }

                // Retrieve the agency to be updated
                $agency = Agency::where(["id" => $request_data['agency']["id"]])
                    ->where(function ($query) {
                        $query->where("owner_id", auth()->user()->id)
                            ->orWhere("business_id", auth()->user()->business_id);
                    })
                    ->first();

                // Check if the agency was found
                if (!$agency) {
                    return response()->json([
                        "message" => "Agency not found"
                    ], 404);
                }

                // Update the agency details and save
                $agency->fill($request_data['agency']);
                $agency->save();

                // Return the updated user and agency data in the response
                return response([
                    "user" => $user,
                    "agency" => $agency
                ], 201);
            });
        } catch (Exception $e) {
            // Handle any exceptions and return a 500 Internal Server Error response
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
            // Log the activity of retrieving agencies with a dummy activity description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the authenticated user has the permission to view agencies
            if (!$request->user()->hasPermissionTo('agency_view')) {
                // If not, return a 401 Unauthorized response
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Build the query to retrieve agencies related to the authenticated user
            $query = Agency::where(function ($query) {
                // Filter agencies by owner ID or business ID matching the authenticated user
                $query->where("owner_id", auth()->user()->id)
                    ->orWhere("business_id", auth()->user()->business_id);
            });

            // Optionally apply additional query filters (commented out for now)
            // $query = $this->query_filters($query);

            // Retrieve the list of agencies based on the constructed query
            $agencies = $this->retrieveData($query, "id", "agencies");

            // Return the retrieved agencies in a 200 OK response
            return response()->json($agencies, 200);
        } catch (Exception $e) {
            // Handle any exceptions and return a 500 Internal Server Error response
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
        // This method is to delete agency by id

        try {
            // Store the activity in the database
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the required permission to delete the agency
            if (!$request->user()->hasPermissionTo('agency_delete')) {
                // Return a 401 Unauthorized response if the user does not have the required permission
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Get the agency with the specified id, belonging to the current business
            $agency = Agency::where("business_id", auth()->user()->business_id)
                ->where([
                    "id" => $ids
                ])
                ->first();

            // Check if the agency exists
            if (!$agency) {
                // Store an error in the database if the agency does not exist
                $this->storeError("Agency not found", 404, "Front-end error", "Front-end error");
                // Return a 404 Not Found response if the agency does not exist
                return response()->json([
                    "message" => "The specified agency does not exist."
                ], 404);
            }

            // Delete the agency
            $agency->delete();


            // Return a 200 OK response with the deleted ids
            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $ids], 200);
        } catch (Exception $e) {

            // Handle any exceptions and return a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }
}
