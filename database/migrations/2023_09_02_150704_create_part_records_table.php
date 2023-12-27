<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('part_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('part_id')->nullable()->constrained('parts')->onUpdate( 'restrict')->onDelete('cascade');
            $table->string('reference')->nullable();
            $table->decimal('unit_price',12,2)->default(0);
            $table->timestamp('part_date')->nullable();
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
        Schema::dropIfExists('part_records');
    }
}
