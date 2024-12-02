<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('bundle_id')->nullable()->constrained('bundles')->onUpdate('restrict')->onDelete('cascade');
            $table->integer('original_quantity')->nullable();
            $table->integer('update_quantity')->nullable();
            $table->integer('final_quantity')->nullable();
            $table->tinyInteger('status')->defalt(10);
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
        Schema::dropIfExists('warehouse_histories');
    }
}
