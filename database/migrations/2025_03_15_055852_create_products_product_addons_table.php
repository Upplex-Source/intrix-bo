<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsProductAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_product_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'product_id' )->constrained('products')->onDelete('cascade');
            $table->foreignId( 'add_on_id' )->constrained('product_add_ons')->onDelete('cascade');
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
        Schema::dropIfExists('products_product_add_ons');
    }
}
