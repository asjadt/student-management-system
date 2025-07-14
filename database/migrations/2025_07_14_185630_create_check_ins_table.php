<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckInsTable extends Migration
{
      public function up(): void
    {
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['student', 'customer']);
            $table->unsignedBigInteger('student_id')->nullable();

            // For customer only
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('comment')->nullable();

            $table->timestamp('check_in_at');
            $table->timestamp('check_out_at')->nullable();

            $table->foreignId("business_id")->constrained("businesses")->onDelete("cascade");

            $table->timestamps();

            // Foreign key for student (if needed)
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }

}
