<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            // Primary Key
            $table->id();

            // Personal Information
            $table->string('school_id')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('nationality');
            $table->string('passport_number')->nullable();
            $table->date('date_of_birth');

            // Course Information
            $table->date('course_start_date');

            $table->unsignedBigInteger('course_title_id')->nullable();
            $table->foreign('course_title_id')
                ->references('id')
                ->on('course_titles')
                ->onDelete('set null');

            // Fee Information
            $table->double('course_fee');
            $table->double('fee_paid');

            // School and Letter Information
            $table->string('school_id')->nullable();
            $table->date('letter_issue_date')->nullable();

            // Status Information
            $table->unsignedBigInteger('student_status_id')->nullable();
            $table->foreign('student_status_id')
                ->references('id')
                ->on('student_statuses')
                ->onDelete('set null');

            // Attachments
            $table->json('attachments')->nullable();

            // Business and User References
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')
            ->references('id')
            ->on(env('DB_DATABASE') . '.businesses')
            ->onDelete('cascade');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Status Flags
            $table->boolean('is_active')->default(true);

            // Timestamps and Soft Deletes
            $table->timestamps();
            $table->softDeletes();
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
