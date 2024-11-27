<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderColumsToMultipleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('order_tax', 10, 2)->nullable();
            $table->decimal('order_discount', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->string('reference')->nullable();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->string('reference')->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('reference')->nullable();
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->string('reference')->nullable();
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('reference')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropcolumn('order_tax');
            $table->dropcolumn('order_discount');
            $table->dropcolumn('shipping_cost');
            $table->dropcolumn('reference');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropcolumn('reference')->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropcolumn('reference')->nullable();
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropcolumn('reference')->nullable();
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropcolumn('reference')->nullable();
        });
    }
}
