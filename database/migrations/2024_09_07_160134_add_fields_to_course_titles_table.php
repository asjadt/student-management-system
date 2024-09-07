<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCourseTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_titles', function (Blueprint $table) {
            $table->string('fee')->nullable();

            $table->unsignedBigInteger("awarding_body_id");
            $table->foreign('awarding_body_id')->references('id')->on('awarding_bodies')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_titles', function (Blueprint $table) {
            $table->dropColumn(['fee']);
        });
    }
}
