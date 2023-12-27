<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuel_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onUpdate('restrict')->onDelete('cascade');
            $table->string('location')->nullable();
            $table->string('day',2)->nullable();
            $table->string('month',2)->nullable();
            $table->string('year',4)->nullable();
            $table->decimal('amount',12,2)->default(0);
            $table->tinyInteger('station')->default(1);
            $table->tinyInteger('status')->default(10);
            $table->timestamp('transaction_time')->nullable();
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
        Schema::dropIfExists('fuel_expenses');
    }
}
