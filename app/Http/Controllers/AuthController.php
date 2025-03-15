<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRegenerateTokenRequest;


use App\Http\Requests\AuthRegisterRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\EmailVerifyTokenRequest;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\ForgetPasswordV2Request;
use App\Http\Requests\PasswordChangeRequest;
use App\Http\Requests\UserInfoUpdateRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\ForgetPasswordMail;
use App\Mail\VerifyMail;
use App\Models\Business;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    use ErrorUtil, BusinessUtil, UserActivityUtil, BasicUtil;
    /**
     *
     * @OA\Post(
     *      path="/v1.0/register",
     *      operationId="z.unused",
     *      tags={"auth"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store user",
     *      description="This method is to store user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"first_Name","last_Name","email","password","password_confirmation","phone","address_line_1","address_line_2","country","city","postcode"},
     *             @OA\Property(property="first_Name", type="string", format="string",example="Rifat"),
     *            @OA\Property(property="last_Name", type="string", format="string",example="Al"),
     *            @OA\Property(property="email", type="string", format="string",example="rifat@g.c"),

     * *  @OA\Property(property="password", type="string", format="string",example="12345678"),
     *  * *  @OA\Property(property="password_confirmation", type="string", format="string",example="12345678"),
     *  * *  @OA\Property(property="phone", type="string", format="string",example="01771034383"),
     *  * *  @OA\Property(property="address_line_1", type="string", format="string",example="dhaka"),
     *  * *  @OA\Property(property="address_line_2", type="string", format="string",example="dinajpur"),
     *  * *  @OA\Property(property="country", type="string", format="string",example="bangladesh"),
     *  * *  @OA\Property(property="city", type="string", format="string",example="dhaka"),
     *  * *  @OA\Property(property="postcode", type="string", format="string",example="1207"),
     *      *  * *  @OA\Property(property="lat", type="string", format="string",example="1207"),
     *      *  * *  @OA\Property(property="long", type="string", format="string",example="1207"),
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

    public function register(AuthRegisterRequest $request)
    {
        // This method is to store user to database,
        // and send a verification email to the user.
        try {
            // store activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // validate request
            $request_data = $request->validated();

            // hash the password
            $request_data['password'] = Hash::make($request['password']);

            // generate a random remember token
            $request_data['remember_token'] = Str::random(10);

            // set user status to active
            $request_data['is_active'] = true;


            // create user
            $user =  User::create($request_data);

            // assign user a role
            $user->assignRole("customer");

            // generate a token for the user
            $user->token = $user->createToken('Laravel Password Grant Client')->accessToken;

            // get user's permissions and roles
            $user->permissions = $user->getAllPermissions()->pluck('name');
            $user->roles = $user->roles->pluck('name');

            // generate a random email verification token
            $email_token = Str::random(30);

            // set user's email verification token and expiration
            $user->email_verify_token = $email_token;
            $user->email_verify_token_expires = Carbon::now()->subDays(-1);

            // save user
            $user->save();

            // send verification email if env variable is set to true
            if (env("SEND_EMAIL") == true) {
                Mail::to($user->email)->send(new VerifyMail($user));
            }

            // return user with token
            return response($user, 201);
        } catch (Exception $e) {

            // return error if exception occurs
            return $this->sendError($e, 500, $request);
        }
    }




    /**
     *
     * @OA\Post(
     *      path="/v1.0/login",
     *      operationId="login",
     *      tags={"auth"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to login user",
     *      description="This method is to login user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"email","password"},
     *            @OA\Property(property="email", type="string", format="string",example="asjadtariq@gmail.com"),

     * *  @OA\Property(property="password", type="string", format="string",example="12345678@We"),
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
    
    public function login(Request $request)
    {
        try {
            // Store activity for login attempt
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if user is attempting to login to a specific business
            if (!empty($request->business_id) && env("SELF_DB") == true) {
                // Set the database connection configuration dynamically
                $databaseName = 'svs_business_' . $request->business_id;

                // Set the admin user with privileges
                $adminUser = env('DB_USERNAME', 'root');

                // Set the admin password
                $adminPassword = env('DB_PASSWORD', '');

                // Set the default database connection configuration dynamically
                Config::set('database.connections.mysql.database', $databaseName);
                Config::set('database.connections.mysql.username', $adminUser);
                Config::set('database.connections.mysql.password', $adminPassword);

                // Reconnect to the database using the updated configuration
                DB::purge('mysql');
                DB::reconnect('mysql');
            }

            // Validate the login credentials
            $loginData = $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            // Get the user based on the provided email
            $user = User::where('email', $loginData['email'])->first();

            // Check if user has 5 failed attempts
            if ($user && $user->login_attempts >= 5) {
                // Get the current time
                $now = Carbon::now();

                // Get the time of the last failed login attempt
                $lastFailedAttempt = Carbon::parse($user->last_failed_login_attempt_at);

                // Calculate the difference in minutes between the current time and the last failed login attempt
                $diffInMinutes = $now->diffInMinutes($lastFailedAttempt);

                // Check if the difference is less than 15 minutes
                if ($diffInMinutes < 15) {
                    // Store the error
                    $this->storeError(
                        'You have 5 failed attempts. Reset your password or wait for 15 minutes to access your account.',
                        403,
                        "front end error",
                        "front end error"
                    );

                    // Return a 403 response
                    return response(['message' => 'You have 5 failed attempts. Reset your password or wait for 15 minutes to access your account.'], 403);
                } else {
                    // Reset the login attempts
                    $user->login_attempts = 0;
                    $user->last_failed_login_attempt_at = null;
                    $user->save();
                }
            }

            // Attempt to login the user
            if (!auth()->attempt($loginData)) {
                // Check if the user exists
                if ($user) {
                    // Increase the login attempts
                    $user->login_attempts++;
                    $user->last_failed_login_attempt_at = Carbon::now();
                    $user->save();

                    // Check if the user has 5 failed attempts
                    if ($user->login_attempts >= 5) {
                        // Get the current time
                        $now = Carbon::now();

                        // Get the time of the last failed login attempt
                        $lastFailedAttempt = Carbon::parse($user->last_failed_login_attempt_at);

                        // Calculate the difference in minutes between the current time and the last failed login attempt
                        $diffInMinutes = $now->diffInMinutes($lastFailedAttempt);

                        // Check if the difference is less than 15 minutes
                        if ($diffInMinutes < 15) {
                            // Store the error
                            $this->storeError(
                                'You have 5 failed attempts. Reset your password or wait for 15 minutes to access your account.',
                                403,
                                "front end error",
                                "front end error"
                            );
                            // Return a 403 response
                            return response(['message' => 'You have 5 failed attempts. Reset your password or wait for 15 minutes to access your account.'], 403);
                        } else {
                            // Reset the login attempts
                            $user->login_attempts = 0;
                            $user->last_failed_login_attempt_at = null;
                            $user->save();
                        }
                    }
                }

                // Return a 401 response
                return response(['message' => 'Invalid Credentials'], 401);
            }

            // Get the logged in user
            $user = auth()->user();

            // Check if the user is active
            if (!$user->is_active) {
                // Store the error
                $this->storeError(
                    'User not active',
                    403,
                    "front end error",
                    "front end error"
                );
                // Return a 403 response
                return response(['message' => 'User not active'], 403);
            }

            // Check if the user belongs to a business
            if ($user->business_id) {
                // Get the business
                $business = Business::where([
                    "id" => $user->business_id
                ])
                    ->first();
                // Check if the business exists
                if (!$business) {
                    // Store the error
                    $this->storeError(
                        'Your business not found',
                        403,
                        "front end error",
                        "front end error"
                    );
                    // Return a 403 response
                    return response(['message' => 'Your business not found'], 403);
                }
                // Check if the business is active
                if (!$business->is_active) {
                    // Store the error
                    $this->storeError(
                        'business not active',
                        403,
                        "front end error",
                        "front end error"
                    );
                    // Return a 403 response
                    return response(['message' => 'Business not active'], 403);
                }
            }

            // Check if the user has not verified their email
            $now = time(); // or your date as well
            $user_created_date = strtotime($user->created_at);
            $datediff = $now - $user_created_date;

            if (!$user->email_verified_at && (($datediff / (60 * 60 * 24)) > 1)) {
                // Store the error
                $this->storeError(
                    'please activate your email first',
                    409,
                    "front end error",
                    "front end error"
                );
                // Return a 409 response
                return response(['message' => 'please activate your email first'], 409);
            }

            // Reset the login attempts
            $user->login_attempts = 0;
            $user->last_failed_login_attempt_at = null;

            // Create a token for site redirection
            $site_redirect_token = Str::random(30);
            $site_redirect_token_data["created_at"] = $now;
            $site_redirect_token_data["token"] = $site_redirect_token;
            $user->site_redirect_token = json_encode($site_redirect_token_data);
            $user->save();

            // Set the user's redirect token
            $user->redirect_token = $site_redirect_token;

            // Set the user's token
            $user->token = auth()->user()->createToken('authToken')->accessToken;

            // Set the user's permissions
            $user->permissions = $user->getAllPermissions()->pluck('name');
            $user->roles = $user->roles->pluck('name');

            // Set the user's business
            $business = $user->business;
            if (!empty($business)) {
                $business = $this->getUrlLink($business, "logo", config("setup-config.business_gallery_location"), $business->name);
            }

            $user->business = $business;

            // Login the user
            Auth::login($user);

            // Store the activity
            $this->storeActivity($request, "logged in", "User successfully logged into the system.");

            $user = $user->load(['roles.permissions', 'permissions', 'business.service_plan.modules']);

            // Return the user's info
            return response()->json(['data' => $user,   "ok" => true], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            // Return a 500 response
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Post(
     *      path="/v1.0/logout",
     *      operationId="logout",
     *      tags={"auth"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to logout user",
     *      description="This method is to logout user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
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
    public function logout(Request $request)
    {
        try {
            // Log the user activity indicating that the user has logged out
            $this->storeActivity($request, "logged out", "User logged out of the system.");

            // Revoke the authentication token of the currently authenticated user
            $request->user()->token()->revoke();

            // Return a JSON response indicating successful logout
            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {
            // Handle any exceptions that occur during the logout process
            // Send an error response with the exception details and a 500 status code
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/v1.0/token-regenerate",
     *      operationId="regenerateToken",
     *      tags={"auth"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to regenerate Token",
     *      description="This method is to regenerate Token",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user_id","site_redirect_token"},
     *            @OA\Property(property="user_id", type="number", format="number",example="1"),

     * *  @OA\Property(property="site_redirect_token", type="string", format="string",example="12345678"),
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
    public function regenerateToken(AuthRegenerateTokenRequest $request)
    {

        try {
            // store activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // validate request
            $request_data = $request->validated();

            // find user by id
            $user = User::where([
                "id" => $request_data["user_id"],
            ])
                ->first();

            // if user is not found
            if (!$user) {
                // store error
                $this->storeError(
                    "no data found",
                    404,
                    "front end error",
                    "front end error"
                );
                // send error response
                return response()->json([
                    "message" => "no user found"
                ], 404);
            }

            // get site redirect token from user
            $site_redirect_token_db = (json_decode($user->site_redirect_token, true));

            // if token is not match
            if ($site_redirect_token_db["token"] !== $request_data["site_redirect_token"]) {
                // store error
                $this->storeError(
                    "invalid token",
                    409,
                    "front end error",
                    "front end error"
                );
                // send error response
                return response()->json([
                    "message" => "invalid token"
                ], 409);
            }

            // get current time
            $now = time(); // or your date as well

            // calculate time difference
            $timediff = $now - $site_redirect_token_db["created_at"];

            // if time difference is more than 20 seconds
            if ($timediff > 20) {
                // store error
                $this->storeError(
                    'token expired',
                    409,
                    "front end error",
                    "front end error"
                );
                // send error response
                return response(['message' => 'token expired'], 409);
            }

            // delete user tokens
            $user->tokens()->delete();

            // create new token
            $user->token = $user->createToken('authToken')->accessToken;

            // get user permissions
            $user->permissions = $user->getAllPermissions()->pluck('name');

            // get user roles
            $user->roles = $user->roles->pluck('name');

            // set user time difference
            $user->a = ($timediff);

            // send user data
            return response()->json(['data' => $user,   "ok" => true], 200);
        } catch (Exception $e) {

            // send error response
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Post(
     *      path="/forgetpassword",
     *      operationId="storeToken",
     *      tags={"auth"},

     *      summary="This method is to store token",
     *      description="This method is to store token",

     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="string",* example="test@g.c"),
     *    *             @OA\Property(property="client_site", type="string", format="string",* example="client"),
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function storeToken(ForgetPasswordRequest $request)
    {
        // This method is to store token to the user's table
        // and send a verification email to the user.
        try {
            // store activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // validate request
            $request_data = $request->validated();

            // get user from database
            $user = User::where(["email" => $request_data["email"]])->first();

            // if user is not found
            if (!$user) {
                // store error
                $this->storeError(
                    "no data found",
                    404,
                    "front end error",
                    "front end error"
                );
                // send error response
                return response()->json([
                    "message" => "no user found"
                ], 404);
            }

            // generate a random token
            $token = Str::random(30);

            // set user's token and expiration
            $user->resetPasswordToken = $token;
            $user->resetPasswordExpires = Carbon::now()->subDays(-1);
            $user->save();

            // send verification email
            $result = Mail::to($request_data["email"])->send(new ForgetPasswordMail($user, $request_data["client_site"]));

            // if email is not sent
            if (count(Mail::failures()) > 0) {
                // Handle failed recipients and log the error messages
                foreach (Mail::failures() as $emailFailure) {
                }
                // send error response
                throw new Exception("Failed to send email to:" . $emailFailure);
            }

            // send success response
            return response()->json([
                "message" => "Please check your email."
            ], 200);
        } catch (Exception $e) {

            // send error response
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Post(
     *      path="/v2.0/forgetpassword",
     *      operationId="storeTokenV2",
     *      tags={"auth"},

     *      summary="This method is to store token",
     *      description="This method is to store token",

     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="string",* example="test@g.c"),
     *    *             @OA\Property(property="client_site", type="string", format="string",* example="client"),
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function storeTokenV2(ForgetPasswordV2Request $request)
    {
        try {
            // log user activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // this is a database transaction
            // it will be rolled back if any exception is thrown
            return DB::transaction(function () use (&$request) {
                // get the validated request data
                $request_data = $request->validated();

                // get the user from database
                $user = User::where(["id" => $request_data["id"]])->first();

                // if the user is not found
                if (!$user) {
                    // log the error
                    $this->storeError(
                        "no data found",
                        404,
                        "front end error",
                        "front end error"
                    );

                    // return error response
                    return response()->json(["message" => "no user found"], 404);
                }

                // generate a random token
                $token = Str::random(30);

                // set the user's token and expiration
                $user->resetPasswordToken = $token;
                $user->resetPasswordExpires = Carbon::now()->subDays(-1);

                // save the user
                $user->save();

                // send the verification email
                $result = Mail::to($user->email)->send(new ForgetPasswordMail($user, $request_data["client_site"]));

                // if the email is not sent
                if (count(Mail::failures()) > 0) {
                    // log the error
                    foreach (Mail::failures() as $emailFailure) {
                    }

                    // throw an exception
                    throw new Exception("Failed to send email to:" . $emailFailure);
                }

                // return success response
                return response()->json([
                    "message" => "Please check your email."
                ], 200);
            });
        } catch (Exception $e) {

            // send error response
            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     * @OA\Post(
     *      path="/resend-email-verify-mail",
     *      operationId="resendEmailVerifyToken",
     *      tags={"auth"},

     *      summary="This method is to resend email verify mail",
     *      description="This method is to resend email verify mail",

     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="string",* example="test@g.c"),
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function resendEmailVerifyToken(EmailVerifyTokenRequest $request)
    {
        try {
            // Log the user's activity for auditing purposes
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start a database transaction to ensure data consistency
            return DB::transaction(function () use (&$request) {
                // Validate the incoming request data
                $request_data = $request->validated();

                // Retrieve the user from the database using the provided email
                $user = User::where(["email" => $request_data["email"]])->first();

                // Check if the user exists
                if (!$user) {
                    // Log the error as no user is found with the given email
                    $this->storeError(
                        "no data found",
                        404,
                        "front end error",
                        "front end error"
                    );

                    // Return a JSON response indicating that no user was found
                    return response()->json(["message" => "no user found"], 404);
                }

                // Generate a random token for email verification
                $email_token = Str::random(30);

                // Set the user's email verification token and its expiration time
                $user->email_verify_token = $email_token;
                $user->email_verify_token_expires = Carbon::now()->subDays(-1);

                // Check if the environment variable to send email is set to true
                if (env("SEND_EMAIL") == true) {
                    // Send the verification email to the user's email address
                    Mail::to($user->email)->send(new VerifyMail($user));
                }

                // Save the user's updated information to the database
                $user->save();

                // Return a JSON response indicating that the user should check their email
                return response()->json([
                    "message" => "please check email"
                ]);
            });
        } catch (Exception $e) {
            // Handle any exceptions that occur and send an error response
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Patch(
     *      path="/forgetpassword/reset/{token}",
     *      operationId="changePasswordByToken",
     *      tags={"auth"},
     *  @OA\Parameter(
     * name="token",
     * in="path",
     * description="token",
     * required=true,
     * example="1"
     * ),
     *      summary="This method is to change password",
     *      description="This method is to change password",

     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"password"},
     *
     *     @OA\Property(property="password", type="string", format="string",* example="aaaaaaaa"),

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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */





    /**
     * Change password by token.
     *
     * This method is to change password, and requires a valid token.
     * The token is obtained from the forget password endpoint.
     * The token is valid for 1 hour.
     * This endpoint is used by the client to change the user's password.
     * The user can enter a new password, and the password will be updated.
     * The user's login attempts will be reset to 0.
     * The user's last failed login attempt time will be reset to null.
     *
     * @param string $token the token obtained from the forget password endpoint
     * @param \Illuminate\Http\Request $request the request object
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePasswordByToken($token, ChangePasswordRequest $request)
    {
        try {
            // Log the user's activity for auditing purposes
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start a database transaction to ensure data consistency
            return DB::transaction(function () use (&$request, &$token) {
                // Validate the incoming request data
                $request_data = $request->validated();

                // Retrieve the user from the database using the provided token
                $user = User::where([
                    "resetPasswordToken" => $token,
                ])
                    ->where("resetPasswordExpires", ">", now())
                    ->first();

                // Check if the user exists
                if (!$user) {
                    // Log the error as the token is invalid or expired
                    $this->storeError(
                        "Invalid Token Or Token Expired",
                        400,
                        "front end error",
                        "front end error"
                    );

                    // Return a JSON response indicating that the token is invalid or expired
                    return response()->json([
                        "message" => "Invalid Token Or Token Expired"
                    ], 400);
                }

                // Hash the new password
                $password = Hash::make($request_data["password"]);

                // Update the user's password
                $user->password = $password;

                // Reset the user's login attempts
                $user->login_attempts = 0;

                // Reset the user's last failed login attempt time
                $user->last_failed_login_attempt_at = null;

                // Save the user's updated information to the database
                $user->save();

                // Return a JSON response indicating that the password has been changed
                return response()->json([
                    "message" => "password changed"
                ], 200);
            });
        } catch (Exception $e) {

            // Return a JSON response with the error message
            return $this->sendError($e, 500, $request);
        }
    }







    /**
     *
     * @OA\Get(
     *      path="/v1.0/user",
     *      operationId="getUser",
     *      tags={"auth"},
     *       security={
     *           {"bearerAuth": {}}
     *       },


     *      summary="This method is to get  user ",
     *      description="This method is to get user",
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


    /**
     * This method is used to get the current user's information.
     * This method is protected by the "auth" middleware, so the user must be logged in to access this endpoint.
     * The user's information is retrieved from the database using the "user" relation on the "auth" middleware.
     * The user's token is generated using the "createToken" method on the "auth" middleware.
     * The user's permissions are retrieved from the database using the "getAllPermissions" method on the "user" model.
     * The user's roles are retrieved from the database using the "roles" relation on the "user" model.
     * The user's business is retrieved from the database using the "business" relation on the "user" model.
     * If the business is found, the business's logo is retrieved from the database using the "getUrlLink" method on the "BusinessUtil" class.
     * The user's default background image is retrieved from the database using the "default_background_image" field on the "user" model.
     * The user's information is then returned as a JSON response with a status code of 200.
     * If an error occurs, the error is caught and a JSON response with the error message is returned with a status code of 500.
     *
     * @param \Illuminate\Http\Request $request the request object
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request)
    {
        try {
            // Log the user's activity for auditing purposes
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Retrieve the user from the database using the "user" relation on the "auth" middleware
            $user = $request->user();

            // Generate a token for the user using the "createToken" method on the "auth" middleware
            $user->token = auth()->user()->createToken('authToken')->accessToken;

            // Retrieve the user's permissions from the database using the "getAllPermissions" method on the "user" model
            $user->permissions = $user->getAllPermissions()->pluck('name');

            // Retrieve the user's roles from the database using the "roles" relation on the "user" model
            $user->roles = $user->roles->pluck('name');

            // Retrieve the user's business from the database using the "business" relation on the "user" model
            if (!empty($user->business)) {
                // Retrieve the business's logo from the database using the "getUrlLink" method on the "BusinessUtil" class
                $user->business = $this->getUrlLink($user->business, "logo", config("setup-config.business_gallery_location"), $user->business->name);
            }

            $user = $user->load(['roles.permissions', 'permissions', 'business.service_plan.modules']);
            // Retrieve the user's default background image from the database using the "default_background_image" field on the "user" model
            // $user->default_background_image = ("/".  config("setup-config.business_background_image_location_full"));

            // Return the user's information as a JSON response with a status code of 200
            return response()->json(
                $user,
                200
            );
        } catch (Exception $e) {
            // Catch any errors that occur and return a JSON response with the error message and a status code of 500
            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     * @OA\Post(
     *      path="/auth/check/email",
     *      operationId="checkEmail",
     *      tags={"auth"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to check user",
     *      description="This method is to check user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="string",example="test@g.c"),
     *  *             @OA\Property(property="user_id", type="string", format="string",example="1"),
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */


    /**
     * Check if a user exists with the given email, excluding the user with the given user_id if provided.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(Request $request)
    {
        try {

            // Store the user's activity for auditing purposes
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Get the user from the database with the given email
            $user = User::where([
                "email" => $request->email
            ])
                // If the user_id is provided, exclude the user with that id
                ->when(
                    !empty($request->user_id),
                    function ($query) use ($request) {
                        $query->whereNotIn("id", [$request->user_id]);
                    }
                )
                ->first();

            // If a user is found, return true
            if ($user) {
                return response()->json(["data" => true], 200);
            }

            // If no user is found, return false
            return response()->json(["data" => false], 200);
        } catch (Exception $e) {
            // Catch any errors that occur and return a JSON response with the error message and a status code of 500
            return $this->sendError($e, 500, $request);
        }
    }






    /**
     *
     * @OA\Patch(
     *      path="/auth/changepassword",
     *      operationId="changePassword",
     *      tags={"auth"},
     *
     *      summary="This method is to change password",
     *      description="This method is to change password",
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"password","cpassword"},
     *
     *     @OA\Property(property="password", type="string", format="string",* example="aaaaaaaa"),
     *  * *  @OA\Property(property="password_confirmation", type="string", format="string",example="aaaaaaaa"),
     *     @OA\Property(property="current_password", type="string", format="string",* example="aaaaaaaa"),
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
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */





    public function changePassword(PasswordChangeRequest $request)
    {
        try {
            // Store the user's activity for auditing purposes
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Validate the incoming request data
            $client_request = $request->validated();

            // Get the user from the database
            $user = $request->user();

            // Check if the current password is valid
            if (!Hash::check($client_request["current_password"], $user->password)) {
                // If the current password is invalid, log the error and return a JSON response with a status code of 400
                $this->storeError(
                    "Invalid password",
                    400,
                    "front end error",
                    "front end error"
                );
                return response()->json([
                    "message" => "Invalid password"
                ], 400);
            }

            // Hash the new password to store in the database
            $password = Hash::make($client_request["password"]);

            // Update the user's password in the database
            $user->password = $password;

            // Reset the user's login attempts to 0 and the last failed login attempt time to null
            $user->login_attempts = 0;
            $user->last_failed_login_attempt_at = null;

            // Save the updated user in the database
            $user->save();

            // Return a JSON response with a status code of 200 to indicate that the password was changed successfully
            return response()->json([
                "message" => "password changed"
            ], 200);
        } catch (Exception $e) {
            // Catch any errors that occur and return a JSON response with the error message and a status code of 500
            return $this->sendError($e, 500, $request);
        }
    }






    /**
     *
     * @OA\Put(
     *      path="/v1.0/update-user-info",
     *      operationId="updateUserInfo",
     *      tags={"auth"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update user by user",
     *      description="This method is to update user by user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"first_Name","last_Name","email","password","password_confirmation","phone","address_line_1","address_line_2","country","city","postcode"},
     *             @OA\Property(property="first_Name", type="string", format="string",example="tsa"),
     *            @OA\Property(property="last_Name", type="string", format="string",example="ts"),
     *            @OA\Property(property="email", type="string", format="string",example="asjadtariq@gmail.com"),

     * *  @OA\Property(property="password", type="boolean", format="boolean",example="12345678@We"),
     *  * *  @OA\Property(property="password_confirmation", type="string", format="string",example="12345678"),
     *  * *  @OA\Property(property="phone", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="address_line_1", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="address_line_2", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="country", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="city", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="postcode", type="string", format="string",example="1"),
     *  *  * *  @OA\Property(property="lat", type="string", format="string",example="1"),
     *  *  * *  @OA\Property(property="long", type="string", format="string",example="1"),

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

    public function updateUserInfo(UserInfoUpdateRequest $request)
    {
        // This method is for updating a user's information.
        // The method takes a UserInfoUpdateRequest object as a parameter.
        // The request object contains the data to be updated.
        try {
            // this is a database transaction
            // it will be rolled back if any exception is thrown
            // store the user's activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            // get the validated request data
            $request_data = $request->validated();
            // if the user is trying to change their password
            if (!empty($request_data['password'])) {
                // hash the password
                $request_data['password'] = Hash::make($request_data['password']);
            }
            // if the user is not trying to change their password
            else {
                // remove the password field from the request data
                unset($request_data['password']);
            }

            // the user is active
            // $request_data['is_active'] = true;
            // generate a random remember token
            $request_data['remember_token'] = Str::random(10);

            // update the user in the database
            $user = tap(User::where(["id" => $request->user()->id]))->update(
                // only update the fields that have been changed
                collect($request_data)->only([
                    'first_Name',
                    'middle_Name',
                    'last_Name',
                    'email',
                    'password',
                    'phone',
                    'address_line_1',
                    'address_line_2',
                    'country',
                    'city',
                    'postcode',
                    "lat",
                    "long",
                    'gender',
                    "image"
                ])->toArray()
            )
                // get the user from the database
                // ->with("somthing")

                ->first();
            // if the user is not found
            if (!$user) {
                // return an error response
                return response()->json([
                    "message" => "no user found"
                ]);
            }

            // get the user's roles
            $user->roles = $user->roles->pluck('name');

            // return the updated user
            return response($user, 200);
        } catch (Exception $e) {
            // log the error
            error_log($e->getMessage());
            // return an error response
            return $this->sendError($e, 500, $request);
        }
    }

}
