<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBreakDatesToSemestersTable extends Migration
{
    public function up()
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->date('break_start_date')->nullable();
            $table->date('break_end_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn(['break_start_date', 'break_end_date']);
        });
    }
}
