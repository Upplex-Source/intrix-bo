<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropInvoiceIdFromDeliveryOrdersMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('delivery_orders_metas', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });

        Schema::table('delivery_orders_metas', function (Blueprint $table) {
            $table->foreignId('delivery_order_id')->nullable()->constrained('delivery_orders')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_orders_metas', function (Blueprint $table) {
            $table->dropForeign(['delivery_order_id']);
            $table->dropColumn('delivery_order_id');
        });

        Schema::table('delivery_orders_metas', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onUpdate('restrict')->onDelete('cascade');
        });
    }
}
