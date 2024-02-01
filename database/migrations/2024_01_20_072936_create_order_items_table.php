<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'order_id' )->nullable()->constrained( 'orders' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->string( 'reference', 25 )->nullable();
            $table->date( 'order_date' )->nullable();
            $table->string( 'grade', 25 )->nullable();
            $table->decimal( 'weight',16,2 )->nullable();
            $table->decimal( 'rate',16,2 )->nullable();
            $table->tinyInteger( 'status' )->default(10);
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
        Schema::dropIfExists('order_items');
    }
}
