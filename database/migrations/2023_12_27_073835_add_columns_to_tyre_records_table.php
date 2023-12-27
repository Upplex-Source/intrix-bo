<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTyreRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'tyre_records', function (Blueprint $table) {
            $table->foreignId( 'part_id' )->nullable()->constrained( 'parts' )->onUpdate( 'restrict' )->onDelete( 'cascade' )->after( 'vehicle_id' );
            $table->foreignId( 'vendor_id' )->nullable()->constrained( 'vendors' )->onUpdate( 'restrict' )->onDelete( 'cascade' )->after( 'vehicle_id' );
            $table->decimal( 'unit_price',12,2 )->default(0)->after( 'vehicle_id' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'tyre_records', function (Blueprint $table) {
            $table->dropForeign( ['part_id'] );
            $table->dropForeign( ['vendor_id'] );
            $table->dropColumn( 'part_id','vendor_id','unit_price' );
        });
    }
}
