<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderSelectionsColumnsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_metas', function (Blueprint $table) {
            //
            $table->json('froyos')->nullable();
            $table->json('syrups')->nullable();
            $table->json('toppings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_metas', function (Blueprint $table) {
            //
            $table->dropColumn('froyos');
            $table->dropColumn('syrups');
            $table->dropColumn('toppings');
        });
    }
}
