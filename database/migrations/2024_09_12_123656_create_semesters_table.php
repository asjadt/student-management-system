<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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










            $table->foreignId('business_id')
            ->constrained('businesses')
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



