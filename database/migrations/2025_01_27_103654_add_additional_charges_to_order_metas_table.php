<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalChargesToOrderMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('additional_charges', 8, 2)->nullable();
        });

        Schema::table('order_metas', function (Blueprint $table) {
            $table->decimal('additional_charges', 8, 2)->nullable();
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->decimal('additional_charges', 8, 2)->nullable();
        });

        Schema::table('cart_metas', function (Blueprint $table) {
            $table->decimal('additional_charges', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('additional_charges');
        });

        Schema::table('order_metas', function (Blueprint $table) {
            $table->dropColumn('additional_charges');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('additional_charges');
        });

        Schema::table('cart_metas', function (Blueprint $table) {
            $table->dropColumn('additional_charges');
        });
    }
}
