<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_Name');
            $table->string('middle_Name')->nullable();
            $table->string('last_Name');


            $table->string('color_theme_name')->default("default");

            $table->string('user_id')->nullable();

            $table->enum('gender', ['male', 'female', 'other'])->nullable();



            $table->string('phone')->nullable();
            $table->string('image')->nullable();

            $table->string("address_line_1")->nullable();
            $table->string("address_line_2")->nullable();
            $table->string("country")->nullable();
            $table->string("city")->nullable();
            $table->string("postcode")->nullable();
            $table->string("lat")->nullable();
            $table->string("long")->nullable();

            $table->string('email')->unique();
            $table->string('email_verify_token')->nullable();
            $table->string('email_verify_token_expires')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('resetPasswordToken')->nullable();
            $table->string('resetPasswordExpires')->nullable();

            $table->string('site_redirect_token')->nullable();


            $table->integer('login_attempts')->default(0);
            $table->dateTime('last_failed_login_attempt_at')->nullable();


            $table->string("background_image")->nullable();



        

            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger("business_id")->nullable(true);
            $table->foreign('business_id')
            ->references('id')
            ->on(env('DB_DATABASE') . '.businesses')
            ->onDelete('cascade');

      $table->unsignedBigInteger("created_by")->nullable();
            $table->foreign('created_by')
        ->references('id')
        ->on('users')
        ->onDelete('set null');

            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('users');
    }
}
