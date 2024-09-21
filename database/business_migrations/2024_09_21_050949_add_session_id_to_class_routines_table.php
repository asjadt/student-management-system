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
            $table->unsignedBigInteger('session_id')->nullable()->after('id'); // Add session_id column
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('set null'); // Add foreign key constraint
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
            $table->dropForeign(['session_id']); // Drop foreign key constraint
            $table->dropColumn('session_id');    // Drop session_id column
        });
    }
}
