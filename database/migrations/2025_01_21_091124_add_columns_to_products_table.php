<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->tinyInteger('free_froyo_quantity')->nullable()->default(0);
            $table->tinyInteger('free_syrup_quantity')->nullable()->default(0);
            $table->tinyInteger('free_topping_quantity')->nullable()->default(0);
            $table->tinyInteger('product_type')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->dropColumn('free_froyo_quantity');
            $table->dropColumn('free_syrup_quantity');
            $table->dropColumn('free_topping_quantity');
            $table->dropColumn('product_type');
        });
    }
}
