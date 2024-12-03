<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionIdToClassRoutinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('class_routines', function (Blueprint $table) {
            // Add session_id column
            $table->unsignedBigInteger('session_id')->nullable()->after('semester_id');

            // Add foreign key constraint for session_id
            $table->foreign('session_id')
                  ->references('id')
                  ->on('sessions')
                  ->onDelete('set null');

        $table->unsignedBigInteger('course_id')->nullable()->after('session_id');

            // Add course_id column with foreign key constraint
            $table->foreignId('course_id')
                  ->constrained('course_titles')
                  ->onDelete('cascade');
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
            // Drop foreign key constraint and session_id column
            $table->dropForeign(['session_id']);
            $table->dropColumn('session_id');

            // Drop foreign key constraint for course_id
            $table->dropForeign(['course_id']);
        });
    }
}
