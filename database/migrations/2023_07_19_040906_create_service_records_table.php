<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained('companies')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onUpdate('restrict')->onDelete('cascade');
            $table->string('reference')->nullable();
            $table->string('document_reference')->nullable();
            $table->string('workshop')->nullable();
            $table->string('remarks')->nullable();
            $table->decimal('meter_reading',16,2)->nullable();
            $table->timestamp('service_date')->nullable();
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
        Schema::dropIfExists('service_records');
    }
}
