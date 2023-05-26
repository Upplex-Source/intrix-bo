<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('photo',150)->nullable();
            $table->string('name',100);
            $table->string('email',50);
            $table->string('phone_number',15);
            $table->string('address')->nullable();
            $table->string('website',100)->nullable();
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('vendors');
    }
}
