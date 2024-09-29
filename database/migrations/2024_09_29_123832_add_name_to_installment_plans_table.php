<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToInstallmentPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('installment_plans', function (Blueprint $table) {
            $table->string('name')->nullable(); // Add name column, nullable if not required for existing records
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('installment_plans', function (Blueprint $table) {
            $table->dropColumn('name'); // Drop the column if the migration is rolled back
        });
    }
}
