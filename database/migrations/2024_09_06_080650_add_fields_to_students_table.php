<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {


            // Course Information
            $table->string('course_dutation')->nullable();
            $table->longText('course_detail')->nullable();


            // Contact Information
            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('sex')->nullable();

            // Address Information
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();

            // Additional Information
            $table->json('emergency_contact_details')->nullable();
            $table->json('previous_education_history')->nullable();

            // Passport Information
            $table->date('passport_issue_date')->nullable();
            $table->date('passport_expiry_date')->nullable();
            $table->string('place_of_issue')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'course_dutation',
                'course_detail',

                'email',
                'contact_number',
                'sex',
                'address',
                'country',
                'city',
                'postcode',
                'lat',
                'long',
                'emergency_contact_details',
                'previous_education_history',
                'passport_issue_date',
                'passport_expiry_date',
                'place_of_issue',
            ]);
        });
    }




}
