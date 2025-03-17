<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFreeGiftColumnsToOrderMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->foreignId( 'add_on_id' )->nullable()->constrained('product_add_ons')->onDelete('cascade');
            $table->foreignId( 'free_gift_id' )->nullable()->constrained('product_free_gifts')->onDelete('cascade');

        });
        
        Schema::table('carts', function (Blueprint $table) {
            //
            $table->foreignId( 'add_on_id' )->nullable()->constrained('product_add_ons')->onDelete('cascade');
            $table->foreignId( 'free_gift_id' )->nullable()->constrained('product_free_gifts')->onDelete('cascade');

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
            //
            $table->dropForeign(['add_on_id']);
            $table->dropForeign(['free_gift_id']);
            $table->dropColumn('add_on_id');
            $table->dropColumn('free_gift_id');
        });

        Schema::table('carts', function (Blueprint $table) {
            //
            $table->dropForeign(['add_on_id']);
            $table->dropForeign(['free_gift_id']);
            $table->dropColumn('add_on_id');
            $table->dropColumn('free_gift_id');
        });
    }
}
