<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeVisaDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_visa_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("employee_id");
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->string("BRP_number");
            $table->date("visa_issue_date");
            $table->date("visa_expiry_date");
            $table->string("place_of_issue");
            $table->json("visa_docs");


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
        Schema::dropIfExists('employee_visa_details');
    }
}
