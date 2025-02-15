<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AwardingBodyController;
use App\Http\Controllers\DashboardManagementController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EmailTemplateWrapperController;
use App\Http\Controllers\BusinessBackgroundImageController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessSettingController;
use App\Http\Controllers\BusinessTierController;
use App\Http\Controllers\BusinessTimesController;
use App\Http\Controllers\ClassRoutineController;
use App\Http\Controllers\CourseTitleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FileManagementController;
use App\Http\Controllers\InstallmentPaymentController;
use App\Http\Controllers\InstallmentPlanController;
use App\Http\Controllers\LetterTemplateController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\StudentLetterController;
use App\Http\Controllers\StudentStatusController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;

use App\Http\Controllers\UserManagementController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider jistoryin a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/v1.0/register', [AuthController::class, "register"]);
Route::post('/v1.0/login', [AuthController::class, "login"]);

Route::post('/v1.0/token-regenerate', [AuthController::class, "regenerateToken"]);

Route::post('/forgetpassword', [AuthController::class, "storeToken"]);
Route::post('/v2.0/forgetpassword', [AuthController::class, "storeTokenV2"]);

Route::post('/resend-email-verify-mail', [AuthController::class, "resendEmailVerifyToken"]);

Route::patch('/forgetpassword/reset/{token}', [AuthController::class, "changePasswordByToken"]);

Route::post('/auth/check/email', [AuthController::class, "checkEmail"]);


Route::post('/v1.0/user-image', [UserManagementController::class, "createUserImage"]);

Route::post('/v1.0/business-image', [BusinessController::class, "createBusinessImage"]);

Route::post('/v1.0/business-image-multiple', [BusinessController::class, "createBusinessImageMultiple"]);


// !!!!!!!@@@@@@@@@@@@$$$$$$$$$$$$%%%%%%%%%%%%%%%%^^^^^^^^^^
// Protected Routes
// !!!!!!!@@@@@@@@@@@@$$$$$$$$$$$$%%%%%%%%%%%%%%%%^^^^^^^^^^
Route::middleware(['custom.auth'])->group(function () {


    Route::post('/v2.0/files/single-file-upload', [FileManagementController::class, "createFileSingleV2"]);
    Route::post('/v2.0/files/multiple-file-upload', [FileManagementController::class, "createFileMultipleV2"]);






    Route::post('/v1.0/files/multiple-student-file-upload', [FileManagementController::class, "createStudentFileMultipleSecure"]);

    Route::get('/v1.0/file/{filename}', [FileManagementController::class, "getFile"]);


    Route::post('/v1.0/logout', [AuthController::class, "logout"]);
    Route::get('/v1.0/user', [AuthController::class, "getUser"]);
    Route::patch('/auth/changepassword', [AuthController::class, "changePassword"]);
    Route::put('/v1.0/update-user-info', [AuthController::class, "updateUserInfo"]);



    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// modules  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


Route::put('/v1.0/modules/toggle-active', [ModuleController::class, "toggleActiveModule"]);
Route::get('/v1.0/modules', [ModuleController::class, "getModules"]);


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end modules management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/business-tiers', [BusinessTierController::class, "createBusinessTier"]);
Route::put('/v1.0/business-tiers', [BusinessTierController::class, "updateBusinessTier"]);
Route::get('/v1.0/business-tiers', [BusinessTierController::class, "getBusinessTiers"]);
Route::get('/v1.0/business-tiers/{id}', [BusinessTierController::class, "getBusinessTierById"]);
Route::delete('/v1.0/business-tiers/{ids}', [BusinessTierController::class, "deleteBusinessTiersByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end job platform management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// notification management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    Route::get('/v1.0/notifications', [NotificationController::class, "getNotifications"]);

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

Route::put('/v1.0/users/update-password', [UserManagementController::class, "updatePassword"]);

Route::post('/v1.0/users/single-file-upload', [UserManagementController::class, "createUserFileSingle"]);

Route::post('/v1.0/users/multiple-file-upload', [UserManagementController::class, "createUserFileMultiple"]);
Route::post('/v1.0/users', [UserManagementController::class, "createUser"]);
Route::get('/v1.0/users/{id}', [UserManagementController::class, "getUserById"]);
Route::put('/v1.0/users', [UserManagementController::class, "updateUser"]);

Route::put('/v1.0/users/assign-roles', [UserManagementController::class, "assignUserRole"]);

Route::put('/v1.0/users/profile', [UserManagementController::class, "updateUserProfile"]);
Route::put('/v1.0/users/toggle-active', [UserManagementController::class, "toggleActiveUser"]);
Route::get('/v1.0/users', [UserManagementController::class, "getUsers"]);
Route::get('/v4.0/users', [UserManagementController::class, "getUsersV4"]);

Route::get('/v2.0/users', [UserManagementController::class, "getUsersV2"]);
Route::delete('/v1.0/users/{ids}', [UserManagementController::class, "deleteUsersByIds"]);
Route::get('/v1.0/users/get/user-activity', [UserManagementController::class, "getUserActivity"]);



Route::post('/v2.0/users', [UserManagementController::class, "createUserV2"]);
Route::put('/v2.0/users', [UserManagementController::class, "updateUserV2"]);
Route::put('/v1.0/users/update-address', [UserManagementController::class, "updateUserAddress"]);
Route::put('/v1.0/users/update-bank-details', [UserManagementController::class, "updateUserBankDetails"]);
Route::put('/v1.0/users/update-joining-date', [UserManagementController::class, "updateUserJoiningDate"]);


Route::put('/v1.0/users/update-emergency-contact', [UserManagementController::class, "updateEmergencyContact"]);
Route::put('/v1.0/users/store-details', [UserManagementController::class, "storeUserDetails"]);
Route::get('/v3.0/users', [UserManagementController::class, "getUsersV3"]);
Route::get('/v2.0/users/{id}', [UserManagementController::class, "getUserByIdV2"]);
Route::get('/v1.0/users/generate/employee-id', [UserManagementController::class, "generateEmployeeId"]);
Route::get('/v1.0/users/validate/employee-id/{user_id}', [UserManagementController::class, "validateEmployeeId"]);

Route::get('/v1.0/users/get-leave-details/{id}', [UserManagementController::class, "getLeaveDetailsByUserId"]);
Route::get('/v1.0/users/get-holiday-details/{id}', [UserManagementController::class, "getholidayDetailsByUserId"]);
Route::get('/v1.0/users/get-schedule-information/{id}', [UserManagementController::class, "getScheduleInformationByUserId"]);


// ********************************************
// user management section --role
// ********************************************
Route::get('/v1.0/initial-role-permissions', [RolesController::class, "getInitialRolePermissions"]);
Route::get('/v1.0/initial-permissions', [RolesController::class, "getInitialPermissions"]);
Route::post('/v1.0/roles', [RolesController::class, "createRole"]);
Route::put('/v1.0/roles', [RolesController::class, "updateRole"]);
Route::get('/v1.0/roles', [RolesController::class, "getRoles"]);

Route::get('/v1.0/roles/{id}', [RolesController::class, "getRoleById"]);
Route::delete('/v1.0/roles/{ids}', [RolesController::class, "deleteRolesByIds"]);
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// end user management section
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%




Route::post('/v1.0/auth/register-with-agency', [AgencyController::class, "registerUserWithAgency"]);
Route::put('/v1.0/agencies', [AgencyController::class, "updateAgency"]);
Route::get('/v1.0/agencies', [AgencyController::class, "getAgencies"]);
Route::delete('/v1.0/agencies/{ids}', [AgencyController::class, "deleteAgenciesByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// business management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Route::post('/v1.0/auth/check-schedule-conflict', [BusinessController::class, "checkScheduleConflict"]);
Route::post('/v1.0/auth/register-with-business', [BusinessController::class, "registerUserWithBusiness"]);
Route::post('/v1.0/businesses', [BusinessController::class, "createBusiness"]);
Route::post('/v1.0/businesses/generate-database', [BusinessController::class, "generateDatabaseForBusiness"]);

Route::post('/v1.0/business-logo', [BusinessController::class, "createBusinessLogo"]);

Route::put('/v1.0/businesses/toggle-active', [BusinessController::class, "toggleActiveBusiness"]);
Route::put('/v1.0/businesses', [BusinessController::class, "updateBusiness"]);
Route::put('/v1.0/businesses/separate', [BusinessController::class, "updateBusinessSeparate"]);
Route::get('/v1.0/businesses', [BusinessController::class, "getBusinesses"]);
Route::get('/v2.0/businesses', [BusinessController::class, "getBusinessesV2"]);

Route::get('/v1.0/businesses/{id}', [BusinessController::class, "getBusinessById"]);
Route::delete('/v1.0/businesses/{ids}', [BusinessController::class, "deleteBusinessesByIds"]);
Route::get('/v1.0/businesses/by-business-owner/all', [BusinessController::class, "getAllBusinessesByBusinessOwner"]);
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// end business management section
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// start business setting
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/business-settings', [BusinessSettingController::class, "createBusinessSetting"]);
Route::get('/v1.0/business-settings', [BusinessSettingController::class, "getBusinessSetting"]);



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end business setting
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// Garage Time Management
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::patch('/v1.0/business-times', [BusinessTimesController::class, "updateBusinessTimes"]);
Route::get('/v1.0/business-times', [BusinessTimesController::class, "getBusinessTimes"]);


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// Garage Background Image Management
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

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
    // letter templates management section
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    Route::post('/v1.0/letter-templates', [LetterTemplateController::class, "createLetterTemplate"]);
    Route::put('/v1.0/letter-templates', [LetterTemplateController::class, "updateLetterTemplate"]);
    Route::put('/v1.0/letter-templates/toggle-active', [LetterTemplateController::class, "toggleActiveLetterTemplate"]);
    Route::get('/v1.0/letter-templates', [LetterTemplateController::class, "getLetterTemplates"]);
    Route::get('/v2.0/letter-templates', [LetterTemplateController::class, "getLetterTemplatesV2"]);

    Route::delete('/v1.0/letter-templates/{ids}', [LetterTemplateController::class, "deleteLetterTemplatesByIds"]);

    Route::get('/v1.0/letter-template-variables', [LetterTemplateController::class, "getLetterTemplateVariables"]);

    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    // end letter templates management section
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    // user letters management section
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    Route::post('/v1.0/student-letters', [StudentLetterController::class, "createStudentLetter"]);

    Route::post('/v1.0/student-letters/generate', [StudentLetterController::class, "generateStudentLetter"]);

    Route::post('/v1.0/student-letters/download', [StudentLetterController::class, "downloadStudentLetter"]);

    Route::post('/v1.0/student-letters/send', [StudentLetterController::class, "sendStudentLetterEmail"]);


    Route::put('/v1.0/student-letters', [StudentLetterController::class, "updateStudentLetter"]);
    Route::put('/v1.0/student-letters/view', [StudentLetterController::class, "updateStudentLetterView"]);


    Route::get('/v1.0/student-letters-get', [StudentLetterController::class, "getStudentLetters"]);

    Route::get('/v2.0/student-letters-get', [StudentLetterController::class, "getStudentLettersV2"]);

    Route::get('/v1.0/student-letters-histories', [StudentLetterController::class, "getStudentLetterHistories"]);

    Route::delete('/v1.0/student-letters/{ids}', [StudentLetterController::class, "deleteStudentLettersByIds"]);


    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    // end user letters management section
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@






// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// student status management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/student-statuses', [StudentStatusController::class, "createStudentStatus"]);
Route::put('/v1.0/student-statuses-update', [StudentStatusController::class, "updateStudentStatus"]);
Route::put('/v1.0/student-statuses/toggle-active', [StudentStatusController::class, "toggleActiveStudentStatus"]);
Route::get('/v1.0/student-statuses', [StudentStatusController::class, "getStudentStatuses"]);
Route::get('/v2.0/student-statuses', [StudentStatusController::class, "getStudentStatusesV2"]);

Route::get('/v1.0/student-statuses/{id}', [StudentStatusController::class, "getStudentStatusById"]);
Route::delete('/v1.0/student-statuses/{ids}', [StudentStatusController::class, "deleteStudentStatusesByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end student status  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// sessions management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/sessions', [SessionController::class, "createSession"]);
Route::put('/v1.0/sessions', [SessionController::class, "updateSession"]);

Route::put('/v1.0/sessions/toggle-active', [SessionController::class, "toggleActiveSession"]);

Route::get('/v1.0/sessions', [SessionController::class, "getSessions"]);
Route::get('/v2.0/sessions', [SessionController::class, "getSessionsV2"]);
Route::delete('/v1.0/sessions/{ids}', [SessionController::class, "deleteSessionsByIds"]);



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end sessions management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@





// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// installment payments management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/installment-payments', [InstallmentPaymentController::class, "createInstallmentPayment"]);
Route::put('/v1.0/installment-payments', [InstallmentPaymentController::class, "updateInstallmentPayment"]);

Route::put('/v1.0/installment-payments/toggle-active', [InstallmentPaymentController::class, "toggleActiveInstallmentPayment"]);

Route::get('/v1.0/installment-payments', [InstallmentPaymentController::class, "getInstallmentPayments"]);
Route::delete('/v1.0/installment-payments/{ids}', [InstallmentPaymentController::class, "deleteInstallmentPaymentsByIds"]);


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end installment payments management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// installment plans management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/installment-plans', [InstallmentPlanController::class, "createInstallmentPlan"]);
Route::put('/v1.0/installment-plans', [InstallmentPlanController::class, "updateInstallmentPlan"]);



Route::put('/v1.0/installment-plans/toggle-active', [InstallmentPlanController::class, "toggleActiveInstallmentPlan"]);

Route::get('/v1.0/installment-plans', [InstallmentPlanController::class, "getInstallmentPlans"]);
Route::delete('/v1.0/installment-plans/{ids}', [InstallmentPlanController::class, "deleteInstallmentPlansByIds"]);



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end installment plans management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// awarding bodies management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/awarding-bodies', [AwardingBodyController::class, "createAwardingBody"]);

Route::put('/v1.0/awarding-bodies', [AwardingBodyController::class, "updateAwardingBody"]);

Route::put('/v1.0/awarding-bodies/toggle-active', [AwardingBodyController::class, "toggleActiveAwardingBody"]);

Route::get('/v1.0/awarding-bodies', [AwardingBodyController::class, "getAwardingBodies"]);
Route::get('/v2.0/awarding-bodies', [AwardingBodyController::class, "getAwardingBodiesV2"]);
Route::delete('/v1.0/awarding-bodies/{ids}', [AwardingBodyController::class, "deleteAwardingBodiesByIds"]);



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end awarding bodies management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@




// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// teachers management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/teachers', [TeacherController::class, "createTeacher"]);
Route::put('/v1.0/teachers', [TeacherController::class, "updateTeacher"]);

Route::put('/v1.0/teachers/toggle-active', [TeacherController::class, "toggleActiveTeacher"]);

Route::get('/v1.0/teachers', [TeacherController::class, "getTeachers"]);
Route::delete('/v1.0/teachers/{ids}', [TeacherController::class, "deleteTeachersByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end teachers management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// class routines management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/class-routines', [ClassRoutineController::class, "createClassRoutine"]);
Route::put('/v1.0/class-routines', [ClassRoutineController::class, "updateClassRoutine"]);
Route::post('/v1.0/class-routines/week', [ClassRoutineController::class, "createWeeklyClassRoutine"]);
Route::put('/v1.0/class-routines/week', [ClassRoutineController::class, "updateWeeklyClassRoutine"]);



Route::put('/v1.0/class-routines/toggle-active', [ClassRoutineController::class, "toggleActiveClassRoutine"]);

Route::get('/v1.0/class-routines', [ClassRoutineController::class, "getClassRoutines"]);

Route::delete('/v1.0/class-routines/{ids}', [ClassRoutineController::class, "deleteClassRoutinesByIds"]);



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end class routines management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@







// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// subjects management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/subjects', [SubjectController::class, "createSubject"]);
Route::put('/v1.0/subjects', [SubjectController::class, "updateSubject"]);

Route::put('/v1.0/subjects/toggle-active', [SubjectController::class, "toggleActiveSubject"]);

Route::get('/v1.0/subjects', [SubjectController::class, "getSubjects"]);
Route::get('/v2.0/subjects', [SubjectController::class, "getSubjectsV2"]);
Route::delete('/v1.0/subjects/{ids}', [SubjectController::class, "deleteSubjectsByIds"]);



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end subjects management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// semesters management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/semesters', [SemesterController::class, "createSemester"]);
Route::put('/v1.0/semesters', [SemesterController::class, "updateSemester"]);

Route::put('/v1.0/semesters/toggle-active', [SemesterController::class, "toggleActiveSemester"]);

Route::get('/v1.0/semesters', [SemesterController::class, "getSemesters"]);
Route::delete('/v1.0/semesters/{ids}', [SemesterController::class, "deleteSemestersByIds"]);



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end semesters management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@




// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// course title management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::post('/v1.0/course-titles', [CourseTitleController::class, "createCourseTitle"]);
Route::put('/v1.0/course-titles-update', [CourseTitleController::class, "updateCourseTitle"]);
Route::put('/v1.0/course-titles/toggle-active', [CourseTitleController::class, "toggleActiveCourseTitle"]);
Route::get('/v1.0/course-titles', [CourseTitleController::class, "getCourseTitles"]);
Route::get('/v2.0/course-titles', [CourseTitleController::class, "getCourseTitlesV2"]);
Route::get('/v1.0/course-titles/{id}', [CourseTitleController::class, "getCourseTitleById"]);
Route::delete('/v1.0/course-titles/{ids}', [CourseTitleController::class, "deleteCourseTitlesByIds"]);

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end course title  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// students  management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


Route::post('/v1.0/students/multiple-file-upload', [StudentController::class, "createStudentFileMultiple"]);
Route::post('/v1.0/students', [StudentController::class, "createStudent"]);
Route::put('/v1.0/students', [StudentController::class, "updateStudent"]);
Route::get('/v1.0/students/validate/school-id/{student_id}', [StudentController::class, "validateStudentId"]);
Route::get('/v1.0/students', [StudentController::class, "getStudents"]);
Route::get('/v2.0/students', [StudentController::class, "getStudentsV2"]);
Route::get('/v1.0/students/{id}', [StudentController::class, "getStudentById"]);
Route::delete('/v1.0/students/{ids}', [StudentController::class, "deleteStudentsByIds"]);


// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// end students management section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@





// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// dashboard section
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

Route::get('/v1.0/business-owner-dashboard/jobs-in-area/{business_id}', [DashboardManagementController::class, "getBusinessOwnerDashboardDataJobList"]);

Route::get('/v1.0/business-owner-dashboard/jobs-application/{business_id}', [DashboardManagementController::class, "getBusinessOwnerDashboardDataJobApplications"]);


Route::get('/v1.0/business-owner-dashboard/winned-jobs-application/{business_id}', [DashboardManagementController::class, "getBusinessOwnerDashboardDataWinnedJobApplications"]);

Route::get('/v1.0/business-owner-dashboard/completed-bookings/{business_id}', [DashboardManagementController::class, "getBusinessOwnerDashboardDataCompletedBookings"]);

Route::get('/v1.0/business-owner-dashboard/upcoming-jobs/{business_id}/{duration}', [DashboardManagementController::class, "getBusinessOwnerDashboardDataUpcomingJobs"]);



Route::get('/v1.0/superadmin-dashboard', [DashboardManagementController::class, "getSuperAdminDashboardData"]);



Route::get('/v1.0/data-collector-dashboard', [DashboardManagementController::class, "getDataCollectorDashboardData"]);

Route::post('/v1.0/dashboard-widgets', [DashboardManagementController::class, "createDashboardWidget"]);
Route::delete('/v1.0/dashboard-widgets/{ids}', [DashboardManagementController::class, "deleteDashboardWidgetsByIds"]);

Route::get('/v1.0/business-user-dashboard', [DashboardManagementController::class, "getBusinessUserDashboardData"]);

Route::get('/v1.0/business-admin-dashboard', [DashboardManagementController::class, "getBusinessAdminDashboardData"]);
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
// end dashboard section
// %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


});

// !!!!!!!@@@@@@@@@@@@$$$$$$$$$$$$%%%%%%%%%%%%%%%%^^^^^^^^^^
// end admin routes
// !!!!!!!@@@@@@@@@@@@$$$$$$$$$$$$%%%%%%%%%%%%%%%%^^^^^^^^^^


Route::get('/client/v1.0/business-settings', [BusinessSettingController::class, "getBusinessSettingClient"]);

Route::post('/v1.0/client/students', [StudentController::class, "createStudentClient"]);
Route::get('/v1.0/client/students/{id}', [StudentController::class, "getStudentByIdClient"]);

Route::get('/v1.0/client/student-statuses', [StudentStatusController::class, "getStudentStatusesClient"]);
Route::get('/v2.0/client/student-statuses', [StudentStatusController::class, "getStudentStatusesClientV2"]);

Route::get('/v1.0/client/students', [StudentController::class, "getStudentsClient"]);
Route::get('/v2.0/client/students', [StudentController::class, "getStudentsClientV2"]);

Route::get('/v1.0/client/course-titles', [CourseTitleController::class, "getCourseTitlesClient"]);
Route::get('/v2.0/client/course-titles', [CourseTitleController::class, "getCourseTitlesClientV2"]);


Route::get('/v1.0/client/businesses-get-by-url/{url}', [BusinessController::class, "getByUrlClient"]);


Route::get('/v1.0/students/generate/student-id/{business_id}', [StudentController::class, "generateStudentId"]);

Route::get('/v1.0/students/validate/student-id/{student_id}/{business_id}', [StudentController::class, "validateStudentIdV2"]);

Route::get('/v1.0/client/businesses/{id}', [BusinessController::class, "getBusinessByIdClient"]);














































































