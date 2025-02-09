<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'product_id' )->constrained( 'products' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('color')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->decimal('installment_price', 10, 2)->nullable();
            $table->decimal('installment_rate', 10, 2)->nullable();
            $table->tinyInteger('status')->default(10);
            $table->string('brochure')->nullable();
            $table->string('sku')->nullable();
            $table->string('specification')->nullable();
            $table->longText('features')->nullable();
            $table->longText('whats_included')->nullable();
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
        Schema::dropIfExists('product_variants');
    }
}
