<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateStudentStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger("business_id")->nullable(true);
               // Add foreign key with database name specified from the environment variable
               $table->foreign('business_id')
               ->references('id')
               ->on(env('DB_DATABASE') . '.businesses')
               ->onDelete('cascade');

         $table->unsignedBigInteger("created_by");
            $table->softDeletes();
            $table->timestamps();
        });
          // Get the current database name from the configuration



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('student_statuses');
    }
}
