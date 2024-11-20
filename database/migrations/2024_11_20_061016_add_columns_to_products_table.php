<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->foreignId('workmanship_id')->nullable()->constrained('workmanships')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('tax_method_id')->nullable()->constrained('tax_methods')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('measurement_unit_id')->nullable()->constrained('measurement_units')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
}
