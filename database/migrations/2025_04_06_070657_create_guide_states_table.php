<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuideStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guide_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('guide_countries')->onUpdate( 'restrict')->onDelete('cascade');
            $table->text('name')->nullable();
            $table->tinyInteger('sequence')->nullable()->default(0);
            $table->string('image')->nullable();
            $table->string('calling_code')->nullable();
            $table->string('postcode')->nullable();
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
        Schema::dropIfExists('guide_states');
    }
}
