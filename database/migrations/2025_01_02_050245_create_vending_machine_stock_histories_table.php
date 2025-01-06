<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendingMachineStockHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vending_machine_stock_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vending_machine_id')->nullable()->constrained('vending_machines')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onUpdate( 'restrict')->onDelete('cascade');            
            $table->integer('quantity')->nullable()->default(0);
            $table->integer('old_quantity')->nullable()->default(0);
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
        Schema::dropIfExists('vending_machine_stock_histories');
    }
}
