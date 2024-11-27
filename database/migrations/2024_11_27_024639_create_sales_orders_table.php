<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('salesman_id')->nullable()->constrained('administrators')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onUpdate('restrict')->onDelete('cascade');
            $table->decimal('order_tax', 10, 2)->nullable();
            $table->decimal('order_discount', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
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
        Schema::dropIfExists('sales_orders');
    }
}
