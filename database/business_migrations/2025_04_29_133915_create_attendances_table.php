<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('class_routine_id')->nullable()->constrained("class_routines")->onDelete("set null");
            $table->foreignId('student_id')->nullable()->constrained("students")->onDelete("set null");

            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->text('remarks')->nullable();

            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_number')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained("subjects")->onDelete("set null");
            $table->foreignId('teacher_id')->nullable()->constrained("users")->onDelete("set null");
            $table->foreignId('semester_id')->nullable()->constrained("semesters")->onDelete("set null");
            $table->foreignId('session_id')->nullable()->constrained("sessions")->onDelete("set null");
            $table->foreignId('course_id')->nullable()->constrained("course_titles")->onDelete("set null");


            $table->foreignId('business_id')->nullable()->constrained("businesses")->onDelete("cascade");
            $table->foreignId('created_by')->nullable()->constrained("users")->onDelete("cascade");


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
        Schema::dropIfExists('attendances');
    }
}
