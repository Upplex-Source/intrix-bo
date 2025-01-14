<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckoutLinkToOrderTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            //
            $table->text('payment_url')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            //
            $table->text('payment_url')->nullable();
            $table->foreignId('order_transaction_id')->nullable()->constrained('order_transactions')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            //
            $table->dropColumn('payment_url');
        });
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn('payment_url');
            $table->dropForeign(['order_transaction_id']);
            $table->dropColumn('order_transaction_id');
        });
    }
}
