<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsProductFreegiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('product_free_gifts', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->default(0);
            $table->decimal('discount_price', 10, 2)->nullable()->default(0);
        });


        Schema::create('products_product_free_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'product_id' )->constrained('products')->onDelete('cascade');
            $table->foreignId( 'free_gift_id' )->constrained('product_free_gifts')->onDelete('cascade');
            $table->timestamps();
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
            $table->dropColumn('price');
            $table->dropColumn('discount_price');
        });

        Schema::dropIfExists('products_product_free_gifts');
    }
}
