<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServicePlanFieldsToBusinessesTable extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->unsignedBigInteger("service_plan_id")->nullable();
            $table->foreign('service_plan_id')->references('id')->on('service_plans')->onDelete('restrict');
            $table->string("service_plan_discount_code")->nullable();
            $table->double("service_plan_discount_amount")->nullable();
            $table->integer("number_of_employees_allowed")->nullable()->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['service_plan_id']);
            $table->dropColumn([
                'service_plan_id',
                'service_plan_discount_code',
                'service_plan_discount_amount',
                'number_of_employees_allowed'
            ]);
        });
    }
    
}
