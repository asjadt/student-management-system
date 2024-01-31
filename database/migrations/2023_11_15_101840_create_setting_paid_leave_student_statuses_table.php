<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingPaidLeaveStudentStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paid_leave_student_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("setting_leave_id")->nullable();
            $table->foreign('setting_leave_id')->references('id')->on('setting_leaves')->onDelete('cascade');
            $table->unsignedBigInteger('student_status_id')->nullable();
            $table->foreign('student_status_id')->references('id')->on('student_statuses')->onDelete('restrict');
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
        Schema::dropIfExists('setting_paid_leave_student_statuses');
    }
}
