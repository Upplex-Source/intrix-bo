<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductFreeGiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_free_gifts', function (Blueprint $table) {
            $table->dropForeign( ['product_id'] );
            $table->dropColUmn( 'product_id' );
        });

        Schema::table('product_free_gifts', function (Blueprint $table) {
            $table->foreignId( 'product_id' )->nullable()->constrained( 'products' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->string('code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_free_gifts', function (Blueprint $table) {
            $table->dropForeign( ['product_id'] );
            $table->dropColumn( 'product_id' );
            $table->dropColumn( 'code' );
        });

        Schema::table('product_free_gifts', function (Blueprint $table) {
            $table->foreignId( 'product_id' )->nullable()->constrained( 'products' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
        });
    }
}
