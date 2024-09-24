<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCoursesIdFromSemesterCoursesTable extends Migration
{
    public function up()
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropForeign(['course_id']); // Drop foreign key if exists
            $table->dropColumn('course_id');    // Remove the 'courses_id' column
        });
    }

    public function down()
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
        });
    }
}
