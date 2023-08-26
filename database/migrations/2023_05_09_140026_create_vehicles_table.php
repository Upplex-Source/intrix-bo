<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->onUpdate('restrict')->onDelete('cascade');
            $table->string('photo',150)->nullable();
            $table->string('name',100);
            $table->string('license_plate',10);
            $table->string('road_tax_number',75)->nullable();
            $table->string('insurance_number',75)->nullable();
            $table->string('permit_number',75)->nullable();
            $table->date('road_tax_expiry_date')->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->date('permit_expiry_date')->nullable();
            $table->tinyInteger('in_service')->default(0);
            $table->tinyInteger('type')->default(1);
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
        Schema::dropIfExists('vehicles');
    }
}
