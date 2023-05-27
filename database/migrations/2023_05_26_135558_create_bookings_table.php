<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onUpdate('restrict')->onDelete('cascade');
            $table->string('reference',50);
            $table->string('customer_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('delivery_order_number')->nullable();
            $table->date('delivery_order_date')->nullable();
            $table->text('pickup_address')->nullable();
            $table->text('dropoff_address')->nullable();
            $table->timestamp('pickup_date')->nullable();
            $table->timestamp('dropoff_date')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onUpdate('restrict')->onDelete('cascade');
            $table->tinyInteger('customer_type')->nullable()->default(1);
            $table->decimal('customer_quantity',16,4)->nullable();
            $table->tinyInteger('customer_unit_of_measurement')->nullable()->default(1);
            $table->decimal('customer_rate',16,2)->nullable();
            $table->decimal('customer_total_amount',16,2)->nullable();
            $table->string('customer_remarks')->nullable();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->onUpdate('restrict')->onDelete('cascade');
            $table->decimal('driver_quantity',16,4)->nullable();
            $table->tinyInteger('driver_unit_of_measurement')->nullable()->default(1);
            $table->decimal('driver_rate',16,2)->nullable();
            $table->decimal('driver_total_amount',16,2)->nullable();
            $table->decimal('driver_percentage',16,2)->nullable();
            $table->decimal('driver_final_amount',16,2)->nullable();
            $table->string('internal_status')->default('pending');
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('bookings');
    }
}
