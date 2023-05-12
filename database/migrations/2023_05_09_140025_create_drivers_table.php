<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('photo',150)->nullable();
            $table->string('name',100);
            $table->string('email',50);
            $table->string('phone_number',15);
            $table->tinyInteger('status')->default(10);
            $table->tinyInteger('employment_type')->default(1)->comment('1:fulltime 2:parttime');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('license_expiry_date')->nullable();
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
        Schema::dropIfExists('drivers');
    }
}
