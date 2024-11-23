<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'causer_id' )->nullable()->constrained( 'administrators' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->foreignId( 'warehouse_id' )->nullable()->constrained( 'warehouses' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->string('attachment')->nullable();
            $table->string('reference')->nullable();
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
        Schema::dropIfExists('adjustments');
    }
}
