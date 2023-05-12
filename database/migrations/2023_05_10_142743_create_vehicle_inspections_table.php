<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleInspectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('administrators')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('administrators')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onUpdate('restrict')->onDelete('cascade');
            $table->integer('meter_reading_outgoing')->nullable();
            $table->integer('meter_reading_incoming')->nullable();
            $table->tinyInteger('fuel_level_outgoing')->nullable()->comment('1:.25 2:.50 3:.75 4:1');
            $table->tinyInteger('fuel_level_incoming')->nullable()->comment('1:.25 2:.50 3:.75 4:1');
            $table->tinyInteger('petrol_card')->default(1);
            $table->string('petrol_card_remark')->nullable();
            $table->tinyInteger('light_indicator')->default(1);
            $table->string('light_indicator_remark')->nullable();
            $table->tinyInteger('inverter_cigrette')->default(1);
            $table->string('inverter_cigrette_remark')->nullable();
            $table->tinyInteger('car_mat_seat_cover')->default(1);
            $table->string('car_mat_seat_cover_remark')->nullable();
            $table->tinyInteger('interior_damage')->default(1);
            $table->string('interior_damage_remark')->nullable();
            $table->tinyInteger('interior_light')->default(1);
            $table->string('interior_light_remark')->nullable();
            $table->timestamp('outgoing_time')->nullable();
            $table->timestamp('incoming_time')->nullable();
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
        Schema::dropIfExists('vehicle_inspections');
    }
}
