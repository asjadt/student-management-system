<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateDisabledLetterTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('disabled_letter_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('letter_template_id')
            ->constrained('letter_templates')
            ->onDelete('cascade');



            $table->foreignId('created_by')
            ->nullable()
            ->constrained('users')
            ->onDelete('set null');

            $table->unsignedBigInteger("business_id")->nullable(true);
            $table->foreign('business_id')
            ->references('id')
            ->on(env('DB_DATABASE') . '.businesses')
            ->onDelete('cascade');
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

        Schema::dropIfExists('disabled_letter_templates');
    }
}




