<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstallmentPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();










            $table->foreignId('course_id')

            ->constrained('course_titles')
            ->onDelete('cascade');






            $table->integer('number_of_installments');





            $table->double('installment_amount');





            $table->date('start_date')->nullable();

            $table->date('end_date')->nullable();

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
        Schema::dropIfExists('installment_plans');
    }
}



