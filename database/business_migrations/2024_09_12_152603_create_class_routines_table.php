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



            $table->integer('day_of_week');





            $table->string('start_time');





            $table->string('end_time');





            $table->string('room_number');



            $table->foreignId('teacher_id')
            ->constrained('users')
            ->onDelete('cascade');

            $table->foreignId('subject_id')
            ->constrained('subjects')
            ->onDelete('cascade');
            $table->unsignedBigInteger('session_id')->nullable();

            // Add foreign key constraint for session_id
            $table->foreign('session_id')
                  ->references('id')
                  ->on('sessions')
                  ->onDelete('set null');



            // Add course_id column with foreign key constraint
            $table->foreignId('course_id')
                  ->constrained('course_titles')
                  ->onDelete('cascade');
            $table->boolean('is_active')->default(false);






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
        Schema::dropIfExists('class_routines');
    }
}



