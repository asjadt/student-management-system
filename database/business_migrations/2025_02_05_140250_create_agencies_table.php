<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('agency_name');
            $table->string('contact_person');
            $table->string('email')->unique();
            $table->string('phone_number', 20)->unique();
            $table->text('address');
            $table->decimal('commission_rate', 5, 2);
            $table->foreignId('business_id')
            ->constrained('businesses')
            ->onDelete('cascade');

            $table->foreignId('owner_id')
            ->constrained('users')
            ->onDelete('cascade');

            $table->boolean('is_active')->default(0);
            $table->unsignedBigInteger("created_by");
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
        Schema::dropIfExists('agencies');
    }
}
