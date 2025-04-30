<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_courses', function (Blueprint $table) {
            $table->id();
             // Course Information
             $table->unsignedBigInteger('student_id')->nullable();
             $table->foreign('student_id')
                 ->references('id')
                 ->on('students')
                 ->onDelete('set null');

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

            $table->date('letter_issue_date')->nullable();

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
        Schema::dropIfExists('student_courses');
    }
}
