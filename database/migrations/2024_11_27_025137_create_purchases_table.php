<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId( 'causer_id' )->nullable()->constrained( 'administrators' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->timestamp('purchase_date')->nullable();
            $table->decimal('amount' , 20, 2 )->nullable();
            $table->decimal('original_amount' , 20, 2)->nullable();
            $table->decimal('final_amount' , 20, 2)->nullable();
            $table->decimal('paid_amount' , 20, 2)->nullable();
            $table->string('attachment')->nullable();
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('purchases');
    }
}
