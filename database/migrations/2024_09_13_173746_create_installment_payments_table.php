<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstallmentPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('installment_payments', function (Blueprint $table) {
            $table->id();



            $table->foreignId('installment_plan_id')

            ->constrained('installment_payments')
            ->onDelete('cascade');


            $table->foreignId('student_id')

            ->constrained('students')
            ->onDelete('cascade');



            $table->double('amount_paid');





            $table->date('payment_date');





            $table->enum('status', ['pending', 'paid', 'overdue']);






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
        Schema::dropIfExists('installment_payments');
    }
}



