<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('nationality');
            $table->string('passport_number')->nullable();
            $table->string('school_id')->nullable();
            $table->date('date_of_birth');
            $table->date('course_start_date');
            $table->date('letter_issue_date')->nullable();

            $table->unsignedBigInteger("student_status_id")->nullable();
            $table->foreign('student_status_id')->references('id')->on('student_statuses')->onDelete('set null');
            $table->unsignedBigInteger('course_title_id')->nullable();
            $table->foreign('course_title_id')->references('id')->on('student_statuses')->onDelete('set null');
            $table->json('attachments')->nullable();




            $table->boolean("is_active")->default(true);
            $table->unsignedBigInteger("business_id");
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->unsignedBigInteger("created_by")->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->softDeletes();
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
        Schema::dropIfExists('students');
    }
}
