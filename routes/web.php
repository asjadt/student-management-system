<?php

use App\Http\Controllers\SetUpController;
use App\Http\Controllers\SwaggerLoginController;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateWrapper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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


Route::get("/default-business-setting", function() {

    echo "Starting the process...<br>";

    // Alter the table column
    DB::statement('ALTER TABLE business_settings MODIFY online_student_status_id BIGINT UNSIGNED NULL;');
    echo "Modified 'online_student_status_id' column in 'business_settings' table.<br>";

    // Fetch all businesses
    $businesses = Business::withTrashed()->get();
    echo "Fetched " . count($businesses) . " businesses.<br>";

    foreach ($businesses as $business) {
        echo "Processing business ID: " . $business->id . ", Name: " . $business->name . "<br>";

        $businessSetting = BusinessSetting::where([
            "business_id" => $business->id
        ])->first();

        if ($businessSetting) {
            echo "BusinessSetting exists for business ID: " . $business->id . ", Name: " . $business->name . "<br>";
        } else {
            echo "BusinessSetting does not exist for business ID: " . $business->id . ", Name: " . $business->name . "<br>";
        }

        $businessSettingData = [
            'business_id' => $business->id,
            'student_data_fields' => config("setup-config.student_data_fields"),
            'student_verification_fields' => config("setup-config.student_verification_fields")
        ];

        if (!empty($businessSetting)) {
            $businessSetting->fill($businessSettingData);
            $businessSetting->save();
            echo "Updated BusinessSetting for business ID: " . $business->id . ", Name: " . $business->name . "<br>";
        } else {
            BusinessSetting::create($businessSettingData);
            echo "Created BusinessSetting for business ID: " . $business->id . ", Name: " . $business->name . "<br>";
        }
    }

    echo "Process completed.<br>";
    return "ok";
});

Route::get("/delete-tables", function() {
   // Disable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS = 0');

// Drop tables
DB::statement('DROP TABLE IF EXISTS affiliations');
DB::statement('DROP TABLE IF EXISTS automobile_categories');
DB::statement('DROP TABLE IF EXISTS automobile_fuel_types');
DB::statement('DROP TABLE IF EXISTS automobile_makes');
DB::statement('DROP TABLE IF EXISTS automobile_model_variants');
DB::statement('DROP TABLE IF EXISTS automobile_models');
DB::statement('DROP TABLE IF EXISTS booking_packages');
DB::statement('DROP TABLE IF EXISTS booking_sub_services');
DB::statement('DROP TABLE IF EXISTS bookings');
DB::statement('DROP TABLE IF EXISTS coupons');
DB::statement('DROP TABLE IF EXISTS dashboard_widgets');
DB::statement('DROP TABLE IF EXISTS disabled_awarding_bodies');
DB::statement('DROP TABLE IF EXISTS disabled_course_titles');
DB::statement('DROP TABLE IF EXISTS disabled_letter_templates');
DB::statement('DROP TABLE IF EXISTS disabled_student_statuses');
DB::statement('DROP TABLE IF EXISTS fuel_station_galleries');
DB::statement('DROP TABLE IF EXISTS fuel_station_options');
DB::statement('DROP TABLE IF EXISTS fuel_station_services');
DB::statement('DROP TABLE IF EXISTS fuel_station_times');
DB::statement('DROP TABLE IF EXISTS fuel_stations');
DB::statement('DROP TABLE IF EXISTS garage_affiliations');
DB::statement('DROP TABLE IF EXISTS garage_automobile_makes');
DB::statement('DROP TABLE IF EXISTS garage_automobile_models');
DB::statement('DROP TABLE IF EXISTS garage_package_sub_services');
DB::statement('DROP TABLE IF EXISTS garage_packages');
DB::statement('DROP TABLE IF EXISTS garage_rules');
DB::statement('DROP TABLE IF EXISTS garage_services');
DB::statement('DROP TABLE IF EXISTS garage_sub_service_prices');
DB::statement('DROP TABLE IF EXISTS garage_sub_services');
DB::statement('DROP TABLE IF EXISTS job_bids');
DB::statement('DROP TABLE IF EXISTS job_packages');
DB::statement('DROP TABLE IF EXISTS job_payments');
DB::statement('DROP TABLE IF EXISTS job_sub_services');
DB::statement('DROP TABLE IF EXISTS jobs');
DB::statement('DROP TABLE IF EXISTS pre_booking_sub_services');
DB::statement('DROP TABLE IF EXISTS pre_bookings');
DB::statement('DROP TABLE IF EXISTS product_categories');
DB::statement('DROP TABLE IF EXISTS product_galleries');
DB::statement('DROP TABLE IF EXISTS product_variations');
DB::statement('DROP TABLE IF EXISTS products');
DB::statement('DROP TABLE IF EXISTS questions');
DB::statement('DROP TABLE IF EXISTS qusetion_stars');
DB::statement('DROP TABLE IF EXISTS review_news');
DB::statement('DROP TABLE IF EXISTS review_value_news');
DB::statement('DROP TABLE IF EXISTS services');
DB::statement('DROP TABLE IF EXISTS shop_galleries');
DB::statement('DROP TABLE IF EXISTS shops');
DB::statement('DROP TABLE IF EXISTS star_tags');
DB::statement('DROP TABLE IF EXISTS stars');
DB::statement('DROP TABLE IF EXISTS sub_services');
DB::statement('DROP TABLE IF EXISTS tags');
DB::statement('DROP TABLE IF EXISTS garages');
DB::statement('DROP TABLE IF EXISTS garage_galleries');
DB::statement('DROP TABLE IF EXISTS garage_times');
// Re-enable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS = 1');

return "ok";

});
