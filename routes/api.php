<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;




use App\Http\Controllers\DashboardManagementController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EmailTemplateWrapperController;




use App\Http\Controllers\BusinessBackgroundImageController;



use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmploymentStatusController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\JobPlatformController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SettingAttendanceController;
use App\Http\Controllers\SettingLeaveController;
use App\Http\Controllers\SettingLeaveTypeController;
use App\Http\Controllers\SettingPayrollController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WorkShiftController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/v1.0/register', [AuthController::class, "register"]);
Route::post('/v1.0/login', [AuthController::class, "login"]);
Route::post('/v1.0/token-regenerate', [AuthController::class, "regenerateToken"]);

Route::post('/forgetpassword', [AuthController::class, "storeToken"]);
Route::post('/resend-email-verify-mail', [AuthController::class, "resendEmailVerifyToken"]);

Route::patch('/forgetpassword/reset/{token}', [AuthController::class, "changePasswordByToken"]);
Route::post('/auth/check/email', [AuthController::class, "checkEmail"]);




























Route::post('/v1.0/user-image', [UserManagementController::class, "createUserImage"]);

Route::post('/v1.0/business-image', [BusinessController::class, "createBusinessImage"]);
Route::post('/v1.0/business-image-multiple', [BusinessController::class, "createBusinessImageMultiple"]);


// !!!!!!!@@@@@@@@@@@@$$$$$$$$$$$$%%%%%%%%%%%%%%%%^^^^^^^^^^
// Protected Routes
// !!!!!!!@@@@@@@@@@@@$$$$$$$$$$$$%%%%%%%%%%%%%%%%^^^^^^^^^^
Route::middleware(['auth:api'])->group(function () {
    Route::get('/v1.0/user', [AuthController::class, "getUser"]);
    Route::patch('/auth/changepassword', [AuthController::class, "changePassword"]);

    Route::put('/v1.0/update-user-info', [AuthController::class, "updateUserInfo"]);



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// notification management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    Route::get('/v1.0/notifications/{perPage}', [NotificationController::class, "getNotifications"]);

    Route::get('/v1.0/notifications/{business_id}/{perPage}', [NotificationController::class, "getNotificationsByBusinessId"]);

    Route::put('/v1.0/notifications/change-status', [NotificationController::class, "updateNotificationStatus"]);

    Route::delete('/v1.0/notifications/{id}', [NotificationController::class, "deleteNotificationById"]);
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// notification management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// user management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

// ********************************************
// user management section --user
// ********************************************

Route::post('/v1.0/users', [UserManagementController::class, "createUser"]);
Route::get('/v1.0/users/get-by-id/{id}', [UserManagementController::class, "getUserById"]);
Route::put('/v1.0/users', [UserManagementController::class, "updateUser"]);
Route::put('/v1.0/users/profile', [UserManagementController::class, "updateUserProfile"]);
Route::put('/v1.0/users/toggle-active', [UserManagementController::class, "toggleActiveUser"]);
Route::get('/v1.0/users', [UserManagementController::class, "getUsers"]);
Route::delete('/v1.0/users/{ids}', [UserManagementController::class, "deleteUsersByIds"]);


Route::get('/v1.0/users/generate/employee-id', [UserManagementController::class, "generateEmployeeId"]);
Route::get('/v1.0/users/validate/employee-id/{employee_id}', [UserManagementController::class, "validateEmployeeId"]);


// ********************************************
// user management section --role
// ********************************************
Route::get('/v1.0/initial-role-permissions', [RolesController::class, "getInitialRolePermissions"]);
Route::post('/v1.0/roles', [RolesController::class, "createRole"]);
Route::put('/v1.0/roles', [RolesController::class, "updateRole"]);
Route::get('/v1.0/roles', [RolesController::class, "getRoles"]);

Route::get('/v1.0/roles/{id}', [RolesController::class, "getRoleById"]);
Route::delete('/v1.0/roles/{ids}', [RolesController::class, "deleteRolesByIds"]);
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// end user management section
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// business management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/auth/register-with-business', [BusinessController::class, "registerUserWithBusiness"]);
Route::post('/v1.0/businesses', [BusinessController::class, "createBusiness"]);
Route::put('/v1.0/businesses/toggle-active', [BusinessController::class, "toggleActiveBusiness"]);
Route::put('/v1.0/businesses', [BusinessController::class, "updateBusiness"]);
Route::put('/v1.0/businesses/separate', [BusinessController::class, "updateBusinessSeparate"]);
Route::get('/v1.0/businesses', [BusinessController::class, "getBusinesses"]);
Route::get('/v1.0/businesses/{id}', [BusinessController::class, "getBusinessById"]);
Route::delete('/v1.0/businesses/{ids}', [BusinessController::class, "deleteBusinessesByIds"]);
Route::get('/v1.0/businesses/by-business-owner/all', [BusinessController::class, "getAllBusinessesByBusinessOwner"]);
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// end business management section
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// businesses Background Image Management
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/business-background-image', [BusinessBackgroundImageController::class, "updateBusinessBackgroundImage"]);
Route::post('/v1.0/business-background-image/by-user', [BusinessBackgroundImageController::class, "updateBusinessBackgroundImageByUser"]);
Route::get('/v1.0/business-background-image', [BusinessBackgroundImageController::class, "getBusinessBackgroundImage"]);


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// end businesses Background Image Management
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// template management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

// ********************************************
// template management section --wrapper
// ********************************************
Route::put('/v1.0/email-template-wrappers', [EmailTemplateWrapperController::class, "updateEmailTemplateWrapper"]);
Route::get('/v1.0/email-template-wrappers/{perPage}', [EmailTemplateWrapperController::class, "getEmailTemplateWrappers"]);
Route::get('/v1.0/email-template-wrappers/single/{id}', [EmailTemplateWrapperController::class, "getEmailTemplateWrapperById"]);

// ********************************************
// template management section
// ********************************************
Route::post('/v1.0/email-templates', [EmailTemplateController::class, "createEmailTemplate"]);
Route::put('/v1.0/email-templates', [EmailTemplateController::class, "updateEmailTemplate"]);
Route::get('/v1.0/email-templates/{perPage}', [EmailTemplateController::class, "getEmailTemplates"]);
Route::get('/v1.0/email-templates/single/{id}', [EmailTemplateController::class, "getEmailTemplateById"]);
Route::get('/v1.0/email-template-types', [EmailTemplateController::class, "getEmailTemplateTypes"]);
 Route::delete('/v1.0/email-templates/{id}', [EmailTemplateController::class, "deleteEmailTemplateById"]);

// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// template management section
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


// ********************************************
// notification template management section
// ********************************************

Route::put('/v1.0/notification-templates', [NotificationTemplateController::class, "updateNotificationTemplate"]);
Route::get('/v1.0/notification-templates/{perPage}', [NotificationTemplateController::class, "getNotificationTemplates"]);
Route::get('/v1.0/notification-templates/single/{id}', [NotificationTemplateController::class, "getEmailTemplateById"]);
Route::get('/v1.0/notification-template-types', [NotificationTemplateController::class, "getNotificationTemplateTypes"]);
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// notification template management section
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// payment type management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/payment-types', [PaymentTypeController::class, "createPaymentType"]);
Route::put('/v1.0/payment-types', [PaymentTypeController::class, "updatePaymentType"]);
Route::get('/v1.0/payment-types/{perPage}', [PaymentTypeController::class, "getPaymentTypes"]);
Route::delete('/v1.0/payment-types/{id}', [PaymentTypeController::class, "deletePaymentTypeById"]);
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// payment type management section
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%









// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// department  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/departments', [DepartmentController::class, "createDepartment"]);
Route::put('/v1.0/departments', [DepartmentController::class, "updateDepartment"]);
Route::get('/v1.0/departments', [DepartmentController::class, "getDepartments"]);
Route::get('/v1.0/departments/{id}', [DepartmentController::class, "getDepartmentById"]);
Route::delete('/v1.0/departments/{ids}', [DepartmentController::class, "deleteDepartmentsByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end department  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// holiday  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/holidays', [HolidayController::class, "createHoliday"]);
Route::put('/v1.0/holidays', [HolidayController::class, "updateHoliday"]);
Route::get('/v1.0/holidays', [HolidayController::class, "getHolidays"]);
Route::get('/v1.0/holidays/{id}', [HolidayController::class, "getHolidayById"]);
Route::delete('/v1.0/holidays/{ids}', [HolidayController::class, "deleteHolidaysByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end holiday  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@




// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// work shift  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/work-shifts', [WorkShiftController::class, "createWorkShift"]);
Route::put('/v1.0/work-shifts', [WorkShiftController::class, "updateWorkShift"]);
Route::get('/v1.0/work-shifts', [WorkShiftController::class, "getWorkShifts"]);
Route::get('/v1.0/work-shifts/{id}', [WorkShiftController::class, "getWorkShiftById"]);
Route::delete('/v1.0/work-shifts/{ids}', [WorkShiftController::class, "deleteWorkShiftsByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end work shift  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// announcements  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/announcements', [AnnouncementController::class, "createAnnouncement"]);
Route::put('/v1.0/announcements', [AnnouncementController::class, "updateAnnouncement"]);
Route::get('/v1.0/announcements', [AnnouncementController::class, "getAnnouncements"]);
Route::get('/v1.0/announcements/{id}', [AnnouncementController::class, "getAnnouncementById"]);
Route::delete('/v1.0/announcements/{ids}', [AnnouncementController::class, "deleteAnnouncementsByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end announcements management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// job platform  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/job-platforms', [JobPlatformController::class, "createJobPlatform"]);
Route::put('/v1.0/job-platforms', [JobPlatformController::class, "updateJobPlatform"]);
Route::get('/v1.0/job-platforms', [JobPlatformController::class, "getJobPlatforms"]);
Route::get('/v1.0/job-platforms/{id}', [JobPlatformController::class, "getJobPlatformById"]);
Route::delete('/v1.0/job-platforms/{ids}', [JobPlatformController::class, "deleteJobPlatformsByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end job platform management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// designation  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/designations', [DesignationController::class, "createDesignation"]);
Route::put('/v1.0/designations', [DesignationController::class, "updateDesignation"]);
Route::get('/v1.0/designations', [DesignationController::class, "getDesignations"]);
Route::get('/v1.0/designations/{id}', [DesignationController::class, "getDesignationById"]);
Route::delete('/v1.0/designations/{ids}', [DesignationController::class, "deleteDesignationsByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end designation management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// employment status management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/employment-statuses', [EmploymentStatusController::class, "createEmploymentStatus"]);
Route::put('/v1.0/employment-statuses', [EmploymentStatusController::class, "updateEmploymentStatus"]);
Route::get('/v1.0/employment-statuses', [EmploymentStatusController::class, "getEmploymentStatuses"]);
Route::get('/v1.0/employment-statuses/{id}', [EmploymentStatusController::class, "getEmploymentStatusById"]);
Route::delete('/v1.0/employment-statuses/{ids}', [EmploymentStatusController::class, "deleteEmploymentStatusesByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end employment status  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@




// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// setting leave types  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/setting-leave-types', [SettingLeaveTypeController::class, "createSettingLeaveType"]);
Route::put('/v1.0/setting-leave-types', [SettingLeaveTypeController::class, "updateSettingLeaveType"]);
Route::get('/v1.0/setting-leave-types', [SettingLeaveTypeController::class, "getSettingLeaveTypes"]);
Route::get('/v1.0/setting-leave-types/{id}', [SettingLeaveTypeController::class, "getSettingLeaveTypeById"]);
Route::delete('/v1.0/setting-leave-types/{ids}', [SettingLeaveTypeController::class, "deleteSettingLeaveTypesByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end setting leave types management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// setting leave  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/setting-leave', [SettingLeaveController::class, "createSettingLeave"]);
// Route::put('/v1.0/setting-leave', [SettingLeaveTypeController::class, "updateSettingLeaveType"]);
 Route::get('/v1.0/setting-leave', [SettingLeaveController::class, "getSettingLeave"]);
// Route::get('/v1.0/setting-leave/{id}', [SettingLeaveTypeController::class, "getSettingLeaveTypeById"]);
// Route::delete('/v1.0/setting-leave/{ids}', [SettingLeaveTypeController::class, "deleteSettingLeaveTypesByIds"]);
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end setting leave management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@




// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// leaves  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/leaves/multiple-file-upload', [LeaveController::class, "createLeaveFileMultiple"]);
Route::post('/v1.0/leaves', [LeaveController::class, "createLeave"]);
Route::put('/v1.0/leaves', [LeaveController::class, "updateLeave"]);
Route::get('/v1.0/leaves', [LeaveController::class, "getLeaves"]);
Route::get('/v1.0/leaves/{id}', [LeaveController::class, "getLeaveById"]);
Route::delete('/v1.0/leaves/{ids}', [LeaveController::class, "deleteLeavesByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end leaves management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@






// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// setting attendance  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/setting-attendance', [SettingAttendanceController::class, "createSettingAttendance"]);
// Route::put('/v1.0/setting-leave', [SettingLeaveTypeController::class, "updateSettingLeaveType"]);
 Route::get('/v1.0/setting-attendance', [SettingAttendanceController::class, "getSettingAttendance"]);
// Route::get('/v1.0/setting-leave/{id}', [SettingLeaveTypeController::class, "getSettingLeaveTypeById"]);
// Route::delete('/v1.0/setting-leave/{ids}', [SettingLeaveTypeController::class, "deleteSettingLeaveTypesByIds"]);
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end setting attendance management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// attendances  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/attendances', [AttendanceController::class, "createAttendance"]);
Route::put('/v1.0/attendances', [AttendanceController::class, "updateAttendance"]);
Route::get('/v1.0/attendances', [AttendanceController::class, "getAttendances"]);
Route::get('/v1.0/attendances/{id}', [AttendanceController::class, "getAttendanceById"]);
Route::delete('/v1.0/attendances/{ids}', [AttendanceController::class, "deleteAttendancesByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end attendances management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@




// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// setting payrun  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/setting-payrun', [SettingPayrollController::class, "createSettingPayrun"]);
// Route::put('/v1.0/setting-leave', [SettingLeaveTypeController::class, "updateSettingLeaveType"]);
 Route::get('/v1.0/setting-payrun', [SettingPayrollController::class, "getSettingPayrun"]);
// Route::get('/v1.0/setting-leave/{id}', [SettingLeaveTypeController::class, "getSettingLeaveTypeById"]);
// Route::delete('/v1.0/setting-leave/{ids}', [SettingLeaveTypeController::class, "deleteSettingLeaveTypesByIds"]);
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end setting payrun management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// setting payslip  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/setting-payslip/upload-logo', [SettingPayrollController::class, "createSettingPayslipUploadLogo"]);
Route::post('/v1.0/setting-payslip', [SettingPayrollController::class, "createSettingPayslip"]);
// Route::put('/v1.0/setting-leave', [SettingLeaveTypeController::class, "updateSettingLeaveType"]);
 Route::get('/v1.0/setting-payslip', [SettingPayrollController::class, "getSettingPayslip"]);
// Route::get('/v1.0/setting-leave/{id}', [SettingLeaveTypeController::class, "getSettingLeaveTypeById"]);
// Route::delete('/v1.0/setting-leave/{ids}', [SettingLeaveTypeController::class, "deleteSettingLeaveTypesByIds"]);
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end setting payslip management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@




// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// job listings  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/job-listings', [JobListingController::class, "createJobListing"]);
Route::put('/v1.0/job-listings', [JobListingController::class, "updateJobListing"]);
Route::get('/v1.0/job-listings', [JobListingController::class, "getJobListings"]);
Route::get('/v1.0/job-listings/{id}', [JobListingController::class, "getJobListingById"]);
Route::delete('/v1.0/job-listings/{ids}', [JobListingController::class, "deleteJobListingsByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end job listings  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@






// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// candidates  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/candidates/multiple-file-upload', [CandidateController::class, "createCandidateFileMultiple"]);
Route::post('/v1.0/candidates', [CandidateController::class, "createCandidate"]);
Route::put('/v1.0/candidates', [CandidateController::class, "updateCandidate"]);
Route::get('/v1.0/candidates', [CandidateController::class, "getCandidates"]);
Route::get('/v1.0/candidates/{id}', [CandidateController::class, "getCandidateById"]);
Route::delete('/v1.0/candidates/{ids}', [CandidateController::class, "deleteCandidatesByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end candidates management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// project  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/projects', [ProjectController::class, "createProject"]);
Route::put('/v1.0/projects', [ProjectController::class, "updateProject"]);
Route::get('/v1.0/projects', [ProjectController::class, "getProjects"]);
Route::get('/v1.0/projects/{id}', [ProjectController::class, "getProjectById"]);
Route::delete('/v1.0/projects/{ids}', [JobListingController::class, "deleteProjectsByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end project  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@




// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// product category management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/product-categories', [ProductCategoryController::class, "createProductCategory"]);
Route::put('/v1.0/product-categories', [ProductCategoryController::class, "updateProductCategory"]);
Route::get('/v1.0/product-categories/{perPage}', [ProductCategoryController::class, "getProductCategories"]);
Route::delete('/v1.0/product-categories/{id}', [ProductCategoryController::class, "deleteProductCategoryById"]);
Route::get('/v1.0/product-categories/single/get/{id}', [ProductCategoryController::class, "getProductCategoryById"]);

Route::get('/v1.0/product-categories/get/all', [ProductCategoryController::class, "getAllProductCategory"]);


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end product category management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// product management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/products', [ProductController::class, "createProduct"]);
Route::put('/v1.0/products', [ProductController::class, "updateProduct"]);
Route::patch('/v1.0/products/link-product-to-shop', [ProductController::class, "linkProductToShop"]);

Route::get('/v1.0/products/{perPage}', [ProductController::class, "getProducts"]);
Route::get('/v1.0/products/single/get/{id}', [ProductController::class, "getProductById"]);
Route::delete('/v1.0/products/{id}', [ProductController::class, "deleteProductById"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end product  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// dashboard section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



Route::get('/v1.0/business-owner-dashboard/jobs-in-area/{business_id}', [DashboardManagementController::class, "getBusinessOwnerDashboardDataJobList"]);

Route::get('/v1.0/business-owner-dashboard/jobs-application/{business_id}', [DashboardManagementController::class, "getBusinessOwnerDashboardDataJobApplications"]);


Route::get('/v1.0/business-owner-dashboard/winned-jobs-application/{business_id}', [DashboardManagementController::class, "getBusinessOwnerDashboardDataWinnedJobApplications"]);

Route::get('/v1.0/business-owner-dashboard/completed-bookings/{business_id}', [DashboardManagementController::class, "getBusinessOwnerDashboardDataCompletedBookings"]);


Route::get('/v1.0/business-owner-dashboard/upcoming-jobs/{business_id}/{duration}', [DashboardManagementController::class, "getBusinessOwnerDashboardDataUpcomingJobs"]);




Route::get('/v1.0/business-owner-dashboard/{business_id}', [DashboardManagementController::class, "getBusinessOwnerDashboardData"]);

Route::get('/v1.0/superadmin-dashboard', [DashboardManagementController::class, "getSuperAdminDashboardData"]);
Route::get('/v1.0/data-collector-dashboard', [DashboardManagementController::class, "getDataCollectorDashboardData"]);


// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// end dashboard section
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%





});

// !!!!!!!@@@@@@@@@@@@$$$$$$$$$$$$%%%%%%%%%%%%%%%%^^^^^^^^^^
// end admin routes
// !!!!!!!@@@@@@@@@@@@$$$$$$$$$$$$%%%%%%%%%%%%%%%%^^^^^^^^^^


























































































Route::middleware(['auth:api'])->group(function () {
































});


