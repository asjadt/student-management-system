<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateDisabledAwardingBodiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('disabled_awarding_bodies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('awarding_body_id')
            ->constrained('awarding_bodies')
            ->onDelete('cascade');


            $table->unsignedBigInteger("business_id")->nullable(true);
            $table->foreign('business_id')
            ->references('id')
            ->on(env('DB_DATABASE') . '.businesses')
            ->onDelete('cascade');
            $table->foreignId('created_by')
            ->nullable()
            ->constrained('users')
            ->onDelete('set null');


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

        Schema::dropIfExists('disabled_awarding_bodies');
    }
}




