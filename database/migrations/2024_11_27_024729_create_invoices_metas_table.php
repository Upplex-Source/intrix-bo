<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onUpdate('restrict')->onDelete('cascade');
            $table->decimal('custom_discount', 10, 2)->nullable();
            $table->decimal('custom_tax', 10, 2)->nullable();
            $table->decimal('custom_shipping_cost', 10, 2)->nullable();
            $table->integer('quantity')->nullable();
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
        Schema::dropIfExists('invoices_metas');
    }
}
