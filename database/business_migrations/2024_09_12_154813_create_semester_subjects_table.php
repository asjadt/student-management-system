<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSemesterSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('semester_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId("subject_id")
            ->constrained("subjects")
            ->onDelete("CASCADE");

            $table->foreignId("semester_id")
            ->constrained("semesters")
            ->onDelete("CASCADE");

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
        Schema::dropIfExists('course_semesters');
    }
}
