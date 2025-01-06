<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendingMachineStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vending_machine_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vending_machine_id')->nullable()->constrained('vending_machines')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('froyo_id')->nullable()->constrained('froyos')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('syrup_id')->nullable()->constrained('syrups')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('topping_id')->nullable()->constrained('toppings')->onUpdate( 'restrict')->onDelete('cascade');
            $table->integer('quantity')->nullable()->default(0);
            $table->integer('old_quantity')->nullable()->default(0);
            $table->timestamp('last_stock_check')->nullable();
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
        Schema::dropIfExists('vending_machine_stocks');
    }
}
