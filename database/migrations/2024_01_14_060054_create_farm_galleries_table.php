<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFarmGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farm_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'farm_id' )->nullable()->constrained( 'farms' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->string('title')->nullable();
            $table->string('file');
            $table->tinyInteger('type')->default(1);
            $table->string('file_type')->nullable();
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
        Schema::dropIfExists('farm_galleries');
    }
}
