<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLevelToCourseTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_titles', function (Blueprint $table) {
            $table->string('level')->after('name')->nullable(); // Replace 'column_name' with the column after which you want 'level' to appear.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_titles', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
}
