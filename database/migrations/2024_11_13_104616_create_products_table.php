<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('products')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onUpdate('restrict')->onDelete('cascade');
            $table->integer('type')->nullable();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('product_code')->nullable();
            $table->string('barcode_symbology')->nullable();
            $table->string('workmanship')->nullable();
            $table->string('location')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('purchase_unit')->nullable();
            $table->string('sale_unit')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('price', 11, 2)->nullable();
            $table->decimal('promotional_price', 11, 2)->nullable();
            $table->timestamp('promotion_start')->nullable();
            $table->timestamp('promotion_end')->nullable();
            $table->tinyInteger('promotion_on')->default(0);
            $table->decimal('cost', 11, 2)->nullable();
            $table->integer('alert_quantity')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('imei')->nullable();
            $table->string('serial_number')->nullable();
            $table->tinyInteger('tax_method')->default(1);
            $table->tinyInteger('featured')->default(0);
            $table->tinyInteger('status')->default(10)->comment('20:disabled 10:enabled');
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
        Schema::dropIfExists('products');
    }
}
