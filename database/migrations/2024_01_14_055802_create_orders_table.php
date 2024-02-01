<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'owner_id' )->nullable()->constrained( 'users' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->foreignId( 'farm_id' )->nullable()->constrained( 'farms' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->foreignId( 'buyer_id' )->nullable()->constrained( 'buyers' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->string( 'reference', 25 );
            $table->date( 'order_date' )->nullable();
            $table->decimal( 'subtotal',16,2 )->nullable();
            $table->decimal( 'total',16,2 )->nullable();
            $table->string( 'internal_status' )->nullable();
            $table->tinyInteger( 'status' )->default( 10 );
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
        Schema::dropIfExists('orders');
    }
}
