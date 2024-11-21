<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdjustmentMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adjustment_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'adjustment_id' )->nullable()->constrained( 'adjustments' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->foreignId( 'product_id' )->nullable()->constrained( 'products' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->foreignId( 'variant_id' )->nullable()->constrained( 'product_variants' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->integer('amount')->nullable();
            $table->integer('original_amount')->nullable();
            $table->integer('final_amount')->nullable();
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
        Schema::dropIfExists('adjustment_metas');
    }
}
