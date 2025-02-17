<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailTemplateCreateRequest;
use App\Http\Requests\EmailTemplateUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\EmailTemplate;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailTemplateController extends Controller
{
    use ErrorUtil, UserActivityUtil;



    /**
     *
     * @OA\Post(
     *      path="/v1.0/email-templates",
     *      operationId="createEmailTemplate",
     *      tags={"z.unused"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store email template",
     *      description="This method is to store email template",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         description="use {{dynamic-username}} {{dynamic-verify-link}} in the template.",
     *         @OA\JsonContent(
     *            required={"type","template","is_active"},
     * *    @OA\Property(property="name", type="string", format="string",example="emal v1"),
     *    @OA\Property(property="type", type="string", format="string",example="email_verification_mail"),
     *    @OA\Property(property="template", type="string", format="string",example="html template goes here"),
     * *    @OA\Property(property="wrapper_id", type="number", format="number",example="1"),
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
     * @OA\Hidden,
     * @OA\Hidden

     */

    /**
     * Create a new email template.
     *
     * This endpoint is used to create a new email template.
     *
     * @param EmailTemplateCreateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function createEmailTemplate(EmailTemplateCreateRequest $request)
    {
        try {
            // Store the activity of the user
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start a database transaction
            return    DB::transaction(function () use (&$request) {
                // Check if the user has the permission to create a new email template
                if (!$request->user()->hasPermissionTo('template_create')) {
                    // If the user does not have the permission, return an error response
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Get the validated data from the request
                $insertableData = $request->validated();

                // If the wrapper_id is not provided, set it to 1
                $insertableData["wrapper_id"]  = !empty($insertableData["wrapper_id"]) ? $insertableData["wrapper_id"] : 1;

                // Create a new email template
                $template =  EmailTemplate::create($insertableData);

                // If the template is active, then other templates of the same type will be deactivated
                if ($template->is_active) {
                    // Get all the email templates of the same type
                    $templates = EmailTemplate::where("id", "!=", $template->id)
                        ->where([
                            "type" => $template->type
                        ])
                        ->get();

                    // Deactivate all the email templates of the same type
                    foreach ($templates as $template) {
                        $template->is_active = false;
                        $template->save();
                    }
                }

                // Return the newly created email template
                return response($template, 201);
            });
        } catch (Exception $e) {
            // Log the error
            error_log($e->getMessage());

            // Return an error response
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     *      path="/v1.0/email-templates",
     *      operationId="updateEmailTemplate",
     *      tags={"template_management.email"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update email template",
     *      description="This method is to update email template",
     *
     *  @OA\RequestBody(
     *         required=true,
     *  description="use [FirstName],[LastName],[FullName],[AccountVerificationLink],[ForgotPasswordLink]
     * [customer_FirstName],[customer_LastName],[customer_FullName],[business_admin_FirstName],[business_admin_LastName],[business_admin_FullName],[automobile_make],[automobile_model],[car_registration_no],[car_registration_year],[status],[payment_status],[additional_information],[discount_type],[discount_amount],[price],[job_start_date],[job_start_time],[job_end_time],[coupon_code],[fuel],[transmission]
     *  in the template",
     *         @OA\JsonContent(
     *            required={"id","template","is_active"},
     *    @OA\Property(property="id", type="number", format="number", example="1"),
     *   * *    @OA\Property(property="name", type="string", format="string",example="emal v1"),
     * *   * *    @OA\Property(property="is_active", type="number", format="number",example="1"),
     *    @OA\Property(property="template", type="string", format="string",example="html template goes here"),
     *  * *    @OA\Property(property="wrapper_id", type="number", format="number",example="1"),
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

    public function updateEmailTemplate(EmailTemplateUpdateRequest $request)
    {
        try {
            // Log the user activity
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Start a database transaction
            return DB::transaction(function () use (&$request) {
                // Check if the user has permission to update the template
                if (!$request->user()->hasPermissionTo('template_update')) {
                    // If not, return a 401 Unauthorized response
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                // Retrieve and validate the data from the request
                $updatableData = $request->validated();

                // Set a default value for wrapper_id if it is not provided
                $updatableData["wrapper_id"] = !empty($updatableData["wrapper_id"]) ? $updatableData["wrapper_id"] : 1;

                // Find the template by ID and update it with the validated data
                $template = tap(EmailTemplate::where(["id" => $updatableData["id"]]))->update(
                    collect($updatableData)->only([
                        "name",
                        "template",
                        "wrapper_id"
                    ])->toArray()
                )->first();

                // If no template is found, log the error and return a 404 response
                if (!$template) {
                    $this->storeError(
                        "no data found",
                        404,
                        "front end error",
                        "front end error"
                    );
                    return response()->json([
                        "message" => "no template found"
                    ], 404);
                }

                // If the updated template is active, deactivate other templates of the same type
                if ($template->is_active) {
                    EmailTemplate::where("id", "!=", $template->id)
                        ->where([
                            "type" => $template->type
                        ])
                        ->update([
                            "is_active" => false
                        ]);
                }

                // Return a 201 Created response with the updated template
                return response($template, 201);
            });
        } catch (Exception $e) {
            // Log the exception message
            error_log($e->getMessage());

            // Return a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/email-templates/{perPage}",
     *      operationId="getEmailTemplates",
     *      tags={"template_management.email"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *              @OA\Parameter(
     *         name="perPage",
     *         in="path",
     *         description="perPage",
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
     *      summary="This method is to get email templates ",
     *      description="This method is to get email templates",
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
     * Get email templates
     *
     * This method is to get email templates based on the request parameters
     *
     * @param int $perPage
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmailTemplates($perPage, Request $request)
    {
        try {
            // Log the user activity with a dummy activity and description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the permission to view email templates
            if (!$request->user()->hasPermissionTo('template_view')) {
                // Return a 401 Unauthorized response if the user does not have the permission
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Create a new instance of the EmailTemplate model
            $templateQuery = new EmailTemplate();

            // If the search key is provided, filter the query results by the 'type' field
            if (!empty($request->search_key)) {
                // Use the search key
                $term = $request->search_key;
                // Filter the query results by the 'type' field
                $templateQuery = $templateQuery->where(function ($query) use ($request) {
                    $query->where("type", "like", "%" . $term . "%");
                });
            }

            // If the start date is provided, filter the query results by the 'created_at' field
            if (!empty($request->start_date)) {
                // Filter the query results by the 'created_at' field
                $templateQuery = $templateQuery->where('created_at', ">=", $request->start_date);
            }

            // If the end date is provided, filter the query results by the 'created_at' field
            if (!empty($request->end_date)) {
                // Filter the query results by the 'created_at' field
                $templateQuery = $templateQuery->where('created_at', "<=", ($request->end_date . ' 23:59:59'));
            }

            // Paginate the query results by the 'id' field in descending order
            $templates = $templateQuery->orderByDesc("id")->paginate($perPage);

            // Return a 200 Ok response with the paginated query results
            return response()->json($templates, 200);
        } catch (Exception $e) {
            // Log the exception message
            error_log($e->getMessage());

            // Return a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/email-templates/single/{id}",
     *      operationId="getEmailTemplateById",
     *      tags={"template_management.email"},
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
     *      summary="This method is to get email template by id",
     *      description="This method is to get email template by id",
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
     * Get an email template by id
     *
     * This endpoint is to get an email template by its id. It first checks if the user has the permission to view email templates,
     * and then it retrieves the email template from the database by its id. If the email template is not found, it logs an error
     * with the message "no data found" and the status code 404, and then it returns a json response with the message "no email template found"
     * and the status code 404. If there is an error in the database query, it logs the error and returns a json response with the message
     * "Internal Server Error" and the status code 500.
     *
     * @param int $id The id of the email template to retrieve
     * @param Request $request The request object
     * @return \Illuminate\Http\JsonResponse The json response object
     */
    public function getEmailTemplateById($id, Request $request)
    {
        try {
            // Log the user activity with a dummy activity and description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the permission to view email templates
            if (!$request->user()->hasPermissionTo('template_view')) {
                // Return a 401 Unauthorized response if the user does not have the permission
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Retrieve the email template from the database by its id
            $template = EmailTemplate::where([
                "id" => $id
            ])
                ->first();

            // If the email template is not found, log an error and return a 404 response
            if (!$template) {
                $this->storeError(
                    "no data found",
                    404,
                    "front end error",
                    "front end error"
                );
                return response()->json([
                    "message" => "no email template found"
                ], 404);
            }

            // Return the email template as a json response
            return response()->json($template, 200);
        } catch (Exception $e) {

            // Log the error and return a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/email-template-types",
     *      operationId="getEmailTemplateTypes",
     *      tags={"template_management.email"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *
     *      summary="This method is to get email template types ",
     *      description="This method is to get email template types",
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
     * Get email template types
     *
     * This method is to get email template types.
     *
     * It first logs the user activity with a dummy activity and description.
     * Then it checks if the user has the permission to view email templates.
     * If the user does not have the permission, it returns a 401 Unauthorized response.
     *
     * Otherwise, it retrieves the email template types from the database.
     * The types are hard-coded in the code.
     * If there is an error in the database query, it logs the error and returns a 500 Internal Server Error response.
     *
     * @param Request $request The request object
     * @return \Illuminate\Http\JsonResponse The json response object
     */
    public function getEmailTemplateTypes(Request $request)
    {
        try {
            // Log the user activity with a dummy activity and description
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the permission to view email templates
            if (!$request->user()->hasPermissionTo('template_view')) {
                // Return a 401 Unauthorized response if the user does not have the permission
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Retrieve the email template types from the database
            // The types are hard-coded in the code
            $types = [
                "email_verification_mail",
                "forget_password_mail",
                "welcome_message",

                "booking_updated_by_business_admin",
                "booking_status_changed_by_business_admin",
                "booking_confirmed_by_business_admin",
                "booking_deleted_by_business_admin",
                "booking_rejected_by_business_admin",

                "booking_created_by_client",
                "booking_updated_by_client",
                "booking_deleted_by_client",
                "booking_accepted_by_client",
                "booking_rejected_by_client",


                "job_created_by_business_admin",
                "job_updated_by_business_admin",
                "job_status_changed_by_business_admin",
                "job_deleted_by_business_admin",
            ];

            // Return the email template types as a json response
            return response()->json($types, 200);
        } catch (Exception $e) {

            // Log the error and return a 500 Internal Server Error response
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/email-templates/{id}",
     *      operationId="deleteEmailTemplateById",
     *      tags={"z.unused"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="This method is to delete email template by id",
     *      description="This method is to delete email template by id",
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

    public function deleteEmailTemplateById($id, Request $request)
    {
        // This method is to delete an email template by its id.

        try {
            // Log the user activity with a dummy activity and description.
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // Check if the user has the permission to delete email templates.
            // If the user does not have the permission, return a 401 Unauthorized response.
            if (!$request->user()->hasPermissionTo('template_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // Retrieve the email template from the database by its id and delete it.
            EmailTemplate::where([
                "id" => $id
            ])
                ->delete();

            // Return a successful response with a 200 status code.
            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            // If there is an error, log the error and return a 500 Internal Server Error response.
            return $this->sendError($e, 500, $request);
        }
    }
}
