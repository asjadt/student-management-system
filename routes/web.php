<?php

use App\Http\Controllers\SetUpController;
use App\Http\Controllers\SwaggerLoginController;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateWrapper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/frontend-error-log', [SetUpController::class, "getFrontEndErrorLogs"])->name("frontend-error-log");

Route::get('/error-log', [SetUpController::class, "getErrorLogs"])->name("error-log");

Route::get('/activity-log', [SetUpController::class, "getActivityLogs"])->name("activity-log");



Route::get('/setup', [SetUpController::class, "setUp"])->name("setup");


Route::get('/backup', [SetUpController::class, "backup"])->name("backup");


Route::get('/roleRefresh', [SetUpController::class, "roleRefresh"])->name("roleRefresh");

Route::get('/swagger-refresh', [SetUpController::class, "swaggerRefresh"]);
Route::get('/migrate', [SetUpController::class, "migrate"]);





Route::get("/swagger-login",[SwaggerLoginController::class,"login"])->name("login.view");
Route::post("/swagger-login",[SwaggerLoginController::class,"passUser"]);






Route::get("/activate/{token}", function (Request $request, $token) {
    $user = User::where([
        "email_verify_token" => $token,
    ])
        ->where("email_verify_token_expires", ">", now())
        ->first();

    if (!$user) {
        return response()->json([
            "message" => "Invalid URL or URL expired",
        ], 400);
    }

    // Mark the email as verified
    $user->email_verified_at = now();
    $user->save();

    return view("email.welcome", [
        'first_name' => $user->first_Name,
        'last_name' => $user->last_Name,
        'reset_password_link' => env('FRONT_END_URL') . "/forget-password/{$user->resetPasswordToken}",
    ]);
});



Route::get("/test",function() {
    $html_content = EmailTemplate::where([
        "type" => "email_verification_mail",
        "is_active" => 1

    ])->first()->template;
    return view('email.dynamic_mail',["contactEmail"=>"rest@gmail.com","user"=>[],"html_content"=>$html_content]);
});


