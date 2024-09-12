<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassRoutinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('class_routines', function (Blueprint $table) {
            $table->id();



            $table->numeric('day_of_week');





            $table->string('start_time');





            $table->string('end_time');





            $table->string('room_number');




            $table->foreignId('teacher_id')
            ->constrained('teachers')
            ->onDelete('cascade');

            $table->foreignId('subject_id')
            ->constrained('subjects')
            ->onDelete('cascade');





                            $table->boolean('is_active')->default(false);






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
        Schema::dropIfExists('class_routines');
    }
}



