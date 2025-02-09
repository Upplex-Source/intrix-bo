<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentPlanToCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            //
            $table->tinyInteger('payment_plan')->nullable()->default(1);
            $table->mediumText('remarks')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            //
            $table->tinyInteger('payment_plan')->nullable()->default(1);
            $table->mediumText('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            //
            $table->dropColumn('payment_plan');
            $table->mediumText('remarks')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn('payment_plan');
            $table->mediumText('remarks')->nullable();
        });
    }
}
