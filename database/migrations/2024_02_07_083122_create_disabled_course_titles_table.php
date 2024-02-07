<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisabledCourseTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disabled_course_titles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("course_title_id");
            $table->foreign('course_title_id')->references('id')->on('course_titles')->onDelete('cascade');

            $table->unsignedBigInteger("business_id")->nullable();
            $table->foreign('business_id')->references('id')->on('users')->onDelete('cascade');


            $table->unsignedBigInteger("created_by")->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
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
        Schema::dropIfExists('disabled_course_titles');
    }
}
