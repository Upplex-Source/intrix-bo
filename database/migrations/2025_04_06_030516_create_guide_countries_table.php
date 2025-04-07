<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuideCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guide_countries', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->string('image')->nullable();
            $table->string('currency_symbol',10)->nullable();
            $table->string('iso_alpha2_code',2)->nullable();
            $table->string('iso_alpha3_code',3)->nullable();
            $table->string('calling_code')->nullable();
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
        Schema::dropIfExists('guide_countries');
    }
}
