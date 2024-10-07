<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateStudentLettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_letters', function (Blueprint $table) {
            $table->id();
            $table->boolean('email_sent')->default(false);
            $table->boolean('letter_view_required')->default(false);
            $table->boolean('letter_viewed')->default(false);

            $table->date('issue_date');
            $table->text('letter_content');
            $table->string('status');
            $table->boolean('sign_required');

            $table->foreignId('student_id')
            ->constrained('students')
            ->onDelete('cascade');

            $table->json('attachments');

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
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('student_letters');
    }
}
