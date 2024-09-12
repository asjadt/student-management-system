<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();



            $table->string('first_name');





            $table->string('middle_name')->nullable();





            $table->string('last_name');





            $table->email('email');





            $table->string('phone');





            $table->string('qualification')->nullable();





            $table->date('hire_date');




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
        Schema::dropIfExists('teachers');
    }
}



