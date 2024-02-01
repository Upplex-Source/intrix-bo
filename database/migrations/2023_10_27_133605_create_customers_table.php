<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('address_1')->nullable();
            $table->tinyInteger('status')->default(10);
            $table->string('remarks')->nullable();
            $table->string('state',50)->nullable();
            $table->string('city',50)->nullable();
            $table->string('postcode',5)->nullable();
            $table->string('address_2')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number_2')->nullable();
            $table->string('pic_name')->nullable();
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
        Schema::dropIfExists('customers');
    }
}
