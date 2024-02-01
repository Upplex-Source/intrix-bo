<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('photo',150)->nullable();
            $table->string('name',100)->nullable();
            $table->string('email',50)->nullable();
            $table->string('phone_number',15)->nullable();
            $table->string('identification_number',20)->nullable();
            $table->date( 'date_of_birth' )->nullable();
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('employees');
    }
}
