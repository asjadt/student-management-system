<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('student_id')->after('id'); // Adjust 'id' to the appropriate column
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('student_id');
        });
    }
};
