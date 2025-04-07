<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('guide_countries')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('state_id')->nullable()->constrained('guide_states')->onUpdate( 'restrict')->onDelete('cascade');
            $table->tinyInteger('sequence')->nullable()->default(0);
            $table->string('file')->nullable();
            $table->string('title')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->longText('description')->nullable();
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
        Schema::dropIfExists('countries_branches');
    }
}
