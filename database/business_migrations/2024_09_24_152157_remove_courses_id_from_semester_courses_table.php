<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCoursesIdFromSemesterCoursesTable extends Migration
{
    public function up()
    {
        Schema::table('semester_courses', function (Blueprint $table) {
            $table->dropForeign(['courses_id']); // Drop foreign key if exists
            $table->dropColumn('courses_id');    // Remove the 'courses_id' column
        });
    }

    public function down()
    {
        Schema::table('semester_courses', function (Blueprint $table) {
            $table->foreignId('courses_id')->constrained()->onDelete('cascade');
        });
    }
}
