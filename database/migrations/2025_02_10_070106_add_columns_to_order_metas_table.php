<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToOrderMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_metas', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onUpdate( 'restrict')->onDelete('cascade');
            $table->integer('quantity')->nullable();
            $table->dropColumn('froyos');
            $table->dropColumn('syrups');
            $table->dropColumn('toppings');
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
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
            $table->dropColumn('quantity');
            $table->longText('froyos')->nullable();
            $table->longText('syrups')->nullable();
            $table->longText('toppings')->nullable();
        });
    }
}
