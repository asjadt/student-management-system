<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldToStudentsTable extends Migration
{
    public function up()
{
    Schema::table('students', function (Blueprint $table) {
        $table->date('course_duration')->nullable(); // Add your new field here
    });
}

public function down()
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropColumn('course_duration'); // Drop the column in case of rollback
    });
}
}
