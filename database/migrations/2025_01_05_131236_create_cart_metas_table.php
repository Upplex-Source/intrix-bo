<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->nullable()->constrained('carts')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate( 'restrict')->onDelete('cascade');
            $table->decimal('total_price', 12, 2)->nullable()->default(0);
            $table->decimal('discount', 12, 2)->nullable()->default(0);
            $table->tinyInteger('status')->default(10);
            $table->json('products')->nullable();
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
        Schema::dropIfExists('cart_metas');
    }
}
