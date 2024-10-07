<?php




use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateAwardingBodiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('awarding_bodies', function (Blueprint $table) {
            $table->id();



            $table->string('name');





            $table->text('description')->nullable();





            $table->date('accreditation_start_date');





            $table->date('accreditation_expiry_date');





            $table->string('logo')->nullable();






                            $table->boolean('is_active')->default(false);

                            $table->boolean('is_default')->default(false);



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

        Schema::dropIfExists('awarding_bodies');
    }
}



