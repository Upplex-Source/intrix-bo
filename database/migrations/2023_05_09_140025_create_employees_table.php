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
            $table->string('name',100);
            $table->string('email',50);
            $table->string('phone_number',15);
            $table->string('identification_number',20)->nullable();
            $table->string('license_number',50)->nullable();
            $table->string('remarks')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->tinyInteger('status')->default(10);
            $table->tinyInteger('designation')->default(1);
            $table->tinyInteger('employment_type')->default(1)->comment('1:fulltime 2:parttime');
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
