<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentSessionCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_session_courses', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_session_id');
            $table->foreign('student_session_id')
                ->references('id')
                ->on('student_sessions')
                ->onDelete('CASCADE');

             $table->date('course_start_date');
             $table->date('course_end_date');
             $table->unsignedBigInteger('course_title_id');
             $table->foreign('course_title_id')
                 ->references('id')
                 ->on('course_titles')
                 ->onDelete('set null');

             // Fee Information
             $table->double('course_fee');
             $table->double('fee_paid');

             // School and Letter Information

            $table->string('level')->nullable();

            $table->string('course_duration')->nullable();
            $table->longText('course_detail')->nullable();


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
        Schema::dropIfExists('student_session_courses');
    }
}
