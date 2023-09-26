<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreRecordItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tyre_record_id')->constrained('tyre_records')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('tyre_id')->nullable()->constrained('tyres')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->onUpdate( 'restrict')->onDelete('cascade');
            $table->tinyInteger('category')->default(1);
            $table->string('serial_number')->nullable();
            $table->decimal('cost_per_pcs',16,2)->nullable();
            $table->tinyInteger('qty_in')->default(0);
            $table->tinyInteger('qty_out')->default(0);
            $table->decimal('selling_price',16,2)->nullable();
            $table->string('remarks')->nullable();
            $table->string('bill_to')->nullable();
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
        Schema::dropIfExists('tyre_record_items');
    }
}
