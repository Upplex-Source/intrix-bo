<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkmanshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workmanships', function (Blueprint $table) {
            $table->id();
            $table->string('fullname')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->tinyInteger('calculation_type')->nullable();
            $table->decimal('calculation_rate', 10, 2)->nullable();
            $table->tinyInteger('status')->default(10)->comment('20:disabled 10:enabled');
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
        Schema::dropIfExists('workmanships');
    }
}
