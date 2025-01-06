<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('product_bundle_id')->nullable()->constrained('product_bundles')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('froyo_id')->nullable()->constrained('froyos')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('syrup_id')->nullable()->constrained('syrups')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('topping_id')->nullable()->constrained('toppings')->onUpdate( 'restrict')->onDelete('cascade');
            $table->integer('froyo_quantity')->nullable()->default(0);
            $table->integer('syrup_quantity')->nullable()->default(0);
            $table->integer('topping_quantity')->nullable()->default(0);
            $table->tinyInteger('status')->default(10);
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
        Schema::dropIfExists('order_metas');
    }
}
