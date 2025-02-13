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
        // This method is to register a user

        try {
            // Store the activity in the activity log
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Validate the request data and store it in $request_data
            $request_data = $request->validated();

            // Hash the password and store it in $request_data
            $request_data['password'] = Hash::make($request['password']);

            // Generate a random remember token and store it in $request_data
            $request_data['remember_token'] = Str::random(10);

            // Set the user to active
            $request_data['is_active'] = true;


            // Create the user in the database
            $user =  User::create($request_data);

            // Verify the email starts
            // Generate a random email token and store it in $user
            $email_token = Str::random(30);
            $user->email_verify_token = $email_token;
            // Set the email token expiration date
            $user->email_verify_token_expires = Carbon::now()->subDays(-1);
            // Save the changes to the user
            $user->save();

            // Assign the user the role of customer
            $user->assignRole("customer");

            // Generate an access token for the user
            $user->token = $user->createToken('Laravel Password Grant Client')->accessToken;

            // Get the user's permissions and store them in $user->permissions
            $user->permissions = $user->getAllPermissions()->pluck('name');

            // Get the user's roles and store them in $user->roles
            $user->roles = $user->roles->pluck('name');

            // If the environment variable SEND_EMAIL is true, send the user a verification email
            if (env("SEND_EMAIL") == true) {
                Mail::to($user->email)->send(new VerifyMail($user));
            }

            // Return the user object with the access token, permissions and roles
            return response($user, 201);
        } catch (Exception $e) {

            // If there is an error, log it and return a 500 error response
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
        // Try to log in the user
        try {
            // Store the activity of the user
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // If the business ID is provided, switch to the corresponding database
            if (!empty($request->business_id) && env("SELF_DB") == true) {
                $databaseName = 'svs_business_' . $request->business_id;

                // Get the default admin user and password
                $adminUser = env('DB_USERNAME', 'root'); // Admin user with privileges
                $adminPassword = env('DB_PASSWORD', '');

                // Dynamically set the default database connection configuration
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

            // Get the user with the provided email
            $user = User::where('email', $loginData['email'])->first();

            // If the user is found
            if ($user) {
                // Check if the user has 5 failed login attempts
                if ($user->login_attempts >= 5) {
                    // Get the current time
                    $now = Carbon::now();
                    // Get the last failed login attempt time
                    $lastFailedAttempt = Carbon::parse($user->last_failed_login_attempt_at);
                    // Calculate the time difference in minutes
                    $diffInMinutes = $now->diffInMinutes($lastFailedAttempt);

                    // If the time difference is less than 15 minutes
                    if ($diffInMinutes < 15) {
                        // Store the error
                        $this->storeError(
                            'You have 5 failed attempts. Reset your password or wait for 15 minutes to access your account.',
                            403,
                            "front end error",
                            "front end error"
                        );

                        // Return a 403 Forbidden response
                        return response(['message' => 'You have 5 failed attempts. Reset your password or wait for 15 minutes to access your account.'], 403);
                    } else {
                        // Reset the failed login attempts
                        $user->login_attempts = 0;
                        // Reset the last failed login attempt time
                        $user->last_failed_login_attempt_at = null;
                        // Save the changes
                        $user->save();
                    }
                }
            }

            // Try to log in the user with the provided credentials
            if (!auth()->attempt($loginData)) {
                // If the user is found but the login failed
                if ($user) {
                    // Increment the failed login attempts
                    $user->login_attempts++;
                    // Set the last failed login attempt time
                    $user->last_failed_login_attempt_at = Carbon::now();
                    // Save the changes
                    $user->save();

                    // If the user has 5 failed login attempts
                    if ($user->login_attempts >= 5) {
                        // Get the current time
                        $now = Carbon::now();
                        // Get the last failed login attempt time
                        $lastFailedAttempt = Carbon::parse($user->last_failed_login_attempt_at);
                        // Calculate the time difference in minutes
                        $diffInMinutes = $now->diffInMinutes($lastFailedAttempt);

                        // If the time difference is less than 15 minutes
                        if ($diffInMinutes < 15) {
                            // Store the error
                            $this->storeError(
                                'You have 5 failed attempts. Reset your password or wait for 15 minutes to access your account.',
                                403,
                                "front end error",
                                "front end error"
                            );

                            // Return a 403 Forbidden response
                            return response(['message' => 'You have 5 failed attempts. Reset your password or wait for 15 minutes to access your account.'], 403);
                        } else {
                            // Reset the failed login attempts
                            $user->login_attempts = 0;
                            // Reset the last failed login attempt time
                            $user->last_failed_login_attempt_at = null;
                            // Save the changes
                            $user->save();
                        }
                    }
                }

                // Return a 401 Unauthorized response
                return response(['message' => 'Invalid Credentials'], 401);
            }

            // Get the authenticated user
            $user = auth()->user();

            // If the user is not active
            if (!$user->is_active) {
                // Store the error
                $this->storeError(
                    'User not active',
                    403,
                    "front end error",
                    "front end error"
                );

                // Return a 403 Forbidden response
                return response(['message' => 'User not active'], 403);
            }

            // If the user is part of a business
            if ($user->business_id) {
                // Get the business
                $business = Business::where([
                    "id" => $user->business_id
                ])
                    ->first();
                // If the business is not found
                if (!$business) {
                    // Store the error
                    $this->storeError(
                        'Your business not found',
                        403,
                        "front end error",
                        "front end error"
                    );

                    // Return a 403 Forbidden response
                    return response(['message' => 'Your business not found'], 403);
                }
                // If the business is not active
                if (!$business->is_active) {
                    // Store the error
                    $this->storeError(
                        'business not active',
                        403,
                        "front end error",
                        "front end error"
                    );
                    // Return a 403 Forbidden response
                    return response(['message' => 'Business not active'], 403);
                }
            }




            // Get the current time
            $now = time(); // or your date as well

            // Get the creation date of the user
            $user_created_date = strtotime($user->created_at);

            // Calculate the difference in days between the current time and the creation date
            // of the user
            $datediff = $now - $user_created_date;

            // If the user's email is not verified and the difference in days is greater than 1
            if (!$user->email_verified_at && (($datediff / (60 * 60 * 24)) > 1)) {
                // Store the error
                $this->storeError(
                    'please activate your email first',
                    409,
                    "front end error",
                    "front end error"
                );
                // Return a 409 Conflict response
                return response(['message' => 'please activate your email first'], 409);
            }

            // Reset the user's login attempts and the last failed login attempt date
            // to 0 and null respectively
            $user->login_attempts = 0;
            $user->last_failed_login_attempt_at = null;

            // Generate a random site redirect token
            $site_redirect_token = Str::random(30);

            // Create an array to store the site redirect token data
            $site_redirect_token_data = [];
            // Set the created at date to the current time
            $site_redirect_token_data["created_at"] = $now;
            // Set the token to the generated site redirect token
            $site_redirect_token_data["token"] = $site_redirect_token;
            // Update the user's site redirect token with the generated site redirect token
            $user->site_redirect_token = json_encode($site_redirect_token_data);
            // Save the changes to the user
            $user->save();

            // Set the user's redirect token to the generated site redirect token
            $user->redirect_token = $site_redirect_token;

            // Generate a new token for the user
            $user->token = auth()->user()->createToken('authToken')->accessToken;

            // Get the user's permissions and store them in $user->permissions
            $user->permissions = $user->getAllPermissions()->pluck('name');
            // Get the user's roles and store them in $user->roles
            $user->roles = $user->roles->pluck('name');

            // If the user is part of a business
            if (!empty($business)) {
                // Get the business
                $business = $this->getUrlLink($business, "logo", config("setup-config.business_gallery_location"), $business->name);
            }

            // Set the user's business to the business
            $user->business = $business;

            // Log the user in
            Auth::login($user);

            // Store the activity in the activity log
            $this->storeActivity($request, "logged in", "User successfully logged into the system.");

            // Return the user data with a 200 OK response
            return response()->json(['data' => $user, "ok" => true], 200);
        } catch (Exception $e) {
            // Log the error
            error_log($e->getMessage());
            // Return the error with a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
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
    /**
     * Logs the user out of the system and revokes their authentication token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Store the activity in the activity log
            $this->storeActivity($request, "logged out", "User logged out of the system.");

            // Revoke the user's authentication token
            // This will log the user out of the system
            $request->user()->token()->revoke();

            // Return a 200 OK response
            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {
            // Log the error
            error_log($e->getMessage());

            // Return the error with a 500 Internal Server Error response
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
        /*
         * This function regenerates a user's authentication token.
         * It takes the user ID and the site redirect token as input.
         * The site redirect token is a token that is stored in the user's
         * database record. It is used to verify that the request is coming from
         * the correct site.
         */

        try {
            // Store the activity in the activity log
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Validate the request data and store it in $request_data
            $request_data = $request->validated();

            // Get the user from the database
            $user = User::where([
                "id" => $request_data["user_id"],
            ])
                ->first();

            // If the user is not found, return an error
            if (!$user) {
                $this->storeError(
                    "user not found",
                    404,
                    "front end error",
                    "front end error"
                );
                return response()->json([
                    "message" => "user not found"
                ], 404);
            }

            // Get the site redirect token from the user's database record
            $site_redirect_token_db = (json_decode($user->site_redirect_token, true));

            // If the site redirect token is invalid, return an error
            if ($site_redirect_token_db["token"] !== $request_data["site_redirect_token"]) {
                $this->storeError(
                    "invalid token",
                    409,
                    "front end error",
                    "front end error"
                );
                return response()->json([
                    "message" => "invalid token"
                ], 409);
            }

            // Get the current time
            $now = time(); // or your date as well

            // Calculate the time difference between the current time and the time the site redirect token was created
            $timediff = $now - $site_redirect_token_db["created_at"];

            // If the time difference is greater than 20 seconds, the token is considered expired
            if ($timediff > 20) {
                $this->storeError(
                    'token expired',
                    409,
                    "front end error",
                    "front end error"
                );
                return response(['message' => 'token expired'], 409);
            }

            // Delete all existing tokens for the user
            $user->tokens()->delete();

            // Generate a new token and store it in the user's database record
            $user->token = $user->createToken('authToken')->accessToken;

            // Get the user's permissions and store them in $user->permissions
            $user->permissions = $user->getAllPermissions()->pluck('name');

            // Get the user's roles and store them in $user->roles
            $user->roles = $user->roles->pluck('name');

            // Store the time difference in $user->a
            $user->a = ($timediff);

            // Return the user with the new token
            return response()->json(['data' => $user,   "ok" => true], 200);
        } catch (Exception $e) {

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

    /**
     * This method is to store a token for password reset
     *
     * 1. Validate the request data
     * 2. Find the user by email
     * 3. If the user is not found, return a 404 error with a message "no user found"
     * 4. Generate a random token and store it in the user model
     * 5. Set the expiration date of the token to 1 day ago
     * 6. Save the changes to the user model
     * 7. Send an email to the user with the token
     * 8. If the email fails to send, throw an exception
     * 9. Return a 200 response with a message "Please check your email."
     *
     * @param ForgetPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeToken(ForgetPasswordRequest $request)
    {

        try {
            // Store the activity in the activity log
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            // Start a database transaction
            return DB::transaction(function () use (&$request) {
                // Validate the request data and store it in $request_data
                $request_data = $request->validated();

                // Find the user by email
                $user = User::where(["email" => $request_data["email"]])->first();
                // If the user is not found, return a 404 error with a message "no user found"
                if (!$user) {
                    $this->storeError(
                        "no data found",
                        404,
                        "front end error",
                        "front end error"
                    );
                    return response()->json(["message" => "no user found"], 404);
                }

                // Generate a random token and store it in the user model
                $token = Str::random(30);
                $user->resetPasswordToken = $token;

                // Set the expiration date of the token to 1 day ago
                $user->resetPasswordExpires = Carbon::now()->subDays(-1);

                // Save the changes to the user model
                $user->save();


                // Send an email to the user with the token
                $result = Mail::to($request_data["email"])->send(new ForgetPasswordMail($user, $request_data["client_site"]));

                // If the email fails to send, throw an exception
                if (count(Mail::failures()) > 0) {
                    // Handle failed recipients and log the error messages
                    foreach (Mail::failures() as $emailFailure) {
                    }
                    throw new Exception("Failed to send email to:" . $emailFailure);
                }

                // Return a 200 response with a message "Please check your email."
                return response()->json([
                    "message" => "Please check your email."
                ], 200);
            });
        } catch (Exception $e) {

            // If an exception is thrown, return a 500 error with the exception message
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

    /**
     * Store a newly created token in storage.
     *
     * @param ForgetPasswordV2Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeTokenV2(ForgetPasswordV2Request $request)
    {
        try {
            // Log the activity of storing a token with a dummy activity description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Begin a database transaction
            return DB::transaction(function () use (&$request) {
                // Validate the request data and store it in $request_data
                $request_data = $request->validated();

                // Retrieve the user from the database by ID
                $user = User::where(["id" => $request_data["id"]])->first();

                // If the user is not found, throw a 404 error with a message "no user found"
                if (!$user) {
                    $this->storeError(
                        "no data found",
                        404,
                        "front end error",
                        "front end error"
                    );
                    return response()->json(["message" => "no user found"], 404);
                }

                // Generate a random token and store it in the user model
                $token = Str::random(30);
                $user->resetPasswordToken = $token;

                // Set the expiration date of the token to 1 day ago
                $user->resetPasswordExpires = Carbon::now()->subDays(-1);

                // Save the changes to the user model
                $user->save();

                // Send an email to the user with the token
                $result = Mail::to($user->email)->send(new ForgetPasswordMail($user, $request_data["client_site"]));

                // If the email fails to send, throw an exception
                if (count(Mail::failures()) > 0) {
                    // Handle failed recipients and log the error messages
                    foreach (Mail::failures() as $emailFailure) {
                        // Log the error message
                        Log::error("Failed to send email to: " . $emailFailure);
                    }
                    throw new Exception("Failed to send email to:" . $emailFailure);
                }

                // Return a 200 response with a message "Please check your email."
                return response()->json([
                    "message" => "Please check your email."
                ], 200);
            });
        } catch (Exception $e) {
            // Handle any exceptions and return a 500 Internal Server Error response
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

    /**
     * Resend an email verify token to a user.
     *
     * This method first logs the activity of resending an email verify token
     * to a user. Then, it validates the request data and stores it in
     * $request_data. It then retrieves the user from the database by email.
     * If the user is not found, it throws a 404 error with a message "no user
     * found". Otherwise, it generates a new random email verify token and
     * stores it in the user model. It then sets the expiration date of the
     * token to 1 day ago. If the environment variable SEND_EMAIL is true,
     * it sends an email to the user with the new token. Finally, it saves the
     * changes to the user model and returns a 200 response with a message
     * "please check email".
     *
     * If any exceptions are thrown, it catches them and returns a 500 Internal
     * Server Error response.
     *
     * @param EmailVerifyTokenRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendEmailVerifyToken(EmailVerifyTokenRequest $request)
    {

        try {
            // Log the activity of resending an email verify token to a user
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Begin a database transaction
            return DB::transaction(function () use (&$request) {
                // Validate the request data and store it in $request_data
                $request_data = $request->validated();

                // Retrieve the user from the database by email
                $user = User::where(["email" => $request_data["email"]])->first();

                // If the user is not found, throw a 404 error with a message "no user found"
                if (!$user) {
                    // Log the error message
                    $this->storeError(
                        "no data found",
                        404,
                        "front end error",
                        "front end error"
                    );
                    // Return a 404 response with a message "no user found"
                    return response()->json(["message" => "no user found"], 404);
                }

                // Generate a new random email verify token and store it in the user model
                $email_token = Str::random(30);
                $user->email_verify_token = $email_token;

                // Set the expiration date of the token to 1 day ago
                $user->email_verify_token_expires = Carbon::now()->subDays(-1);

                // If the environment variable SEND_EMAIL is true, send an email to the user with the new token
                if (env("SEND_EMAIL") == true) {
                    Mail::to($user->email)->send(new VerifyMail($user));
                }

                // Save the changes to the user model
                $user->save();

                // Return a 200 response with a message "please check email"
                return response()->json([
                    "message" => "please check email"
                ]);
            });
        } catch (Exception $e) {
            // Handle any exceptions and return a 500 Internal Server Error response
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





    public function changePasswordByToken($token, ChangePasswordRequest $request)
    {
        try {
            // Log the activity of changing the password by token with a dummy activity description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Begin a database transaction
            return DB::transaction(function () use (&$request, &$token) {
                // Validate the request data and store it in $request_data
                $request_data = $request->validated();

                // Retrieve the user from the database by the reset password token
                $user = User::where([
                    "resetPasswordToken" => $token,
                ])
                    // Ensure the token has not expired
                    ->where("resetPasswordExpires", ">", now())
                    ->first();

                // If the user is not found or the token is expired, return a 400 error
                if (!$user) {
                    $this->storeError(
                        "Invalid Token Or Token Expired",
                        400,
                        "front end error",
                        "front end error"
                    );
                    return response()->json([
                        "message" => "Invalid Token Or Token Expired"
                    ], 400);
                }

                // Hash the new password and assign it to the user model
                $password = Hash::make($request_data["password"]);
                $user->password = $password;

                // Reset login attempts and last failed login attempt timestamp
                $user->login_attempts = 0;
                $user->last_failed_login_attempt_at = null;

                // Save the changes to the user model
                $user->save();

                // Return a 200 response with a message "password changed"
                return response()->json([
                    "message" => "password changed"
                ], 200);
            });
        } catch (Exception $e) {
            // Handle any exceptions and return a 500 Internal Server Error response
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


    public function getUser(Request $request)
    {
        try {
            // Log the activity of getting the current user with a dummy activity description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Retrieve the authenticated user from the request
            $user = $request->user();


            // Generate a new access token for the user
            $user->token = auth()->user()->createToken('authToken')->accessToken;

            // Retrieve all of the user's permissions and store them in the user model
            $user->permissions = $user->getAllPermissions()->pluck('name');

            // Retrieve all of the user's roles and store them in the user model
            $user->roles = $user->roles->pluck('name');


            // If the user has a business, retrieve the business and store it in the user model
            // Also, generate a url for the business logo
            if (!empty($user->business)) {
                $user->business = $this->getUrlLink($user->business, "logo", config("setup-config.business_gallery_location"), $user->business->name);
            }

            // Return a 200 response with the user model
            return response()->json(
                $user,
                200
            );
        } catch (Exception $e) {
            // Handle any exceptions and return a 500 Internal Server Error response
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
     * This method is to check whether a given email already exists in the
     * users table, and whether the given user_id is not the same as the
     * user_id associated with the given email.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(Request $request)
    {
        try {
            // Log the activity of checking an email with a dummy activity description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Retrieve the user from the database using the given email
            // and if the given user_id is not empty, then exclude the user
            // with the given user_id.
            $user = User::where([
                "email" => $request->email
            ])
                ->when(
                    // If the given user_id is not empty, then exclude the user
                    // with the given user_id.
                    !empty($request->user_id),
                    function ($query) use ($request) {
                        $query->whereNotIn("id", [$request->user_id]);
                    }

                )


                // Retrieve the first user that matches the conditions
                ->first();

            // If the user exists, then return a response with a status code
            // of 200 and a boolean value of true.
            if ($user) {
                return response()->json(["data" => true], 200);
            }

            // If the user does not exist, then return a response with a
            // status code of 200 and a boolean value of false.
            return response()->json(["data" => false], 200);
        } catch (Exception $e) {
            // Handle any exceptions and return a 500 Internal Server Error
            // response.
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





    /**
     * This method is to change the password of a user.
     *
     * @param PasswordChangeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(PasswordChangeRequest $request)
    {
        try {
            // Store the activity in the activity log
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Validate the request data and store it in $client_request
            $client_request = $request->validated();

            // Retrieve the user from the database using the current user
            $user = $request->user();

            // Check if the current password is valid
            if (!Hash::check($client_request["current_password"], $user->password)) {
                // If the current password is invalid, then store an error in the activity log
                // with a status code of 400 and a message "Invalid password"
                $this->storeError(
                    "Invalid password",
                    400,
                    "front end error",
                    "front end error"
                );

                // Return a 400 response with a message "Invalid password"
                return response()->json([
                    "message" => "Invalid password"
                ], 400);
            }

            // Hash the new password and store it in the user model
            $password = Hash::make($client_request["password"]);
            $user->password = $password;

            // Reset the login attempts and last failed login attempt timestamp
            $user->login_attempts = 0;
            $user->last_failed_login_attempt_at = null;

            // Save the changes to the user model
            $user->save();

            // Return a 200 response with a message "password changed"
            return response()->json([
                "message" => "password changed"
            ], 200);
        } catch (Exception $e) {
            // Handle any exceptions and return a 500 Internal Server Error response
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
        // This method is to update the User Information
        // The request data is validated using the UserInfoUpdateRequest class
        // The user is retrieved from the database using the user_id
        // The request data is then updated in the database
        // If the password is provided in the request, it is hashed and updated
        // If the password is not provided, the password is not updated
        // The user is returned with the updated information
        // An exception is thrown if any errors occur

        try {
            // Store the activity in the activity log
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Get the validated request data
            $request_data = $request->validated();


            // If the password is provided in the request, hash it and update it
            if (!empty($request_data['password'])) {
                // Hash the password
                $request_data['password'] = Hash::make($request_data['password']);
            } else {
                // If the password is not provided, remove the password key from the request data
                unset($request_data['password']);
            }

            // Set the remember token to a random string
            $request_data['remember_token'] = Str::random(10);

            // Update the user information in the database
            $user  =  tap(User::where(["id" => $request->user()->id]))->update(
                // Get the fields that are allowed to be updated
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
                // Get the roles of the user
                ->with("roles")

                // Get the first user in the query result
                ->first();
            // If the user is not found, return a 404 response
            if (!$user) {
                return response()->json([
                    "message" => "no user found"
                ], 404);
            }

            // Return the user with the updated information
            return response($user, 200);
        } catch (Exception $e) {
            // Log the error message
            error_log($e->getMessage());

            // Return an error response
            return $this->sendError($e, 500, $request);
        }
    }
}
