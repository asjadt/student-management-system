
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePassportColumnsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->date('passport_issue_date')->nullable()->change();
            $table->date('passport_expiry_date')->nullable()->change();
            $table->string('place_of_issue')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->date('passport_issue_date')->nullable(false)->change();
            $table->date('passport_expiry_date')->nullable(false)->change();
            $table->string('place_of_issue')->nullable(false)->change();
        });
    }
}
