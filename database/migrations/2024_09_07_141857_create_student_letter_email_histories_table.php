<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentLetterEmailHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_letter_email_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_letter_id')
            ->constrained('student_letters')
            ->onDelete('cascade');
        $table->timestamp('sent_at')->nullable();
        $table->string('recipient_email');
        $table->text('email_content')->nullable();
        $table->string('status');
        $table->text('error_message')->nullable();
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
        Schema::dropIfExists('student_letter_email_histories');
    }
}
