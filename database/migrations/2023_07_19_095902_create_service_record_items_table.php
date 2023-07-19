<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceRecordItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_record_id')->constrained('service_records')->onUpdate( 'restrict')->onDelete('cascade');
            $table->tinyInteger('type')->default(1);
            $table->text('meta')->nullable();
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
        Schema::dropIfExists('service_record_items');
    }
}
