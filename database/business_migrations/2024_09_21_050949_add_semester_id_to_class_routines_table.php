<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSemesterIdToClassRoutinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('class_routines', function (Blueprint $table) {
            $table->unsignedBigInteger('semester_id')->nullable()->after('id'); // Add session_id column
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('set null'); // Add foreign key constraint
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('class_routines', function (Blueprint $table) {
            $table->dropForeign(['semester_id']); // Drop foreign key constraint
            $table->dropColumn('semester_id');    // Drop session_id column
        });
    }
}
