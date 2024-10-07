<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateSemestersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();



            $table->string('name');





            $table->date('start_date');


            $table->date('end_date');


            $table->boolean('is_active')->default(false);


            $table->foreignId('course_id')
            ->nullable()
            ->constrained('course_titles')
            ->onDelete('cascade');

            $table->unsignedBigInteger("business_id")->nullable(true);
            $table->foreign('business_id')
            ->references('id')
            ->on(env('DB_DATABASE') . '.businesses')
            ->onDelete('cascade');


            $table->unsignedBigInteger("created_by");
            $table->softDeletes();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {

        Schema::dropIfExists('semesters');
    }
}



