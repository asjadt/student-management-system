<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_sessions', function (Blueprint $table) {
            $table->id();
             // Course Information
             $table->unsignedBigInteger('student_id');
             $table->foreign('student_id')
                 ->references('id')
                 ->on('students')
                 ->onDelete('CASCADE');

                 $table->unsignedBigInteger('session_id')->nullable();
                 $table->foreign('session_id')
                     ->references('id')
                     ->on('sessions')
                     ->onDelete('CASCADE');

           

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
        Schema::dropIfExists('student_courses');
    }
}
