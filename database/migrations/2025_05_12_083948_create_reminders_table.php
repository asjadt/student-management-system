<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('duration');
            $table->enum('duration_unit', ['days', 'weeks', 'months']);
            $table->enum('send_time', ['before_expiry', 'after_expiry']);
            $table->integer('frequency_after_first_reminder');
            $table->integer('reminder_limit')->nullable();
            $table->boolean('keep_sending_until_update');
            $table->enum('entity_name', ['passport_expiry_reminder', 'attendance_reminder']);
            $table->unsignedBigInteger('course_id')->nullable();
            $table->decimal('attendance_threshold', 8, 2)->nullable();


            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('course_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
