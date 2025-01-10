<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckinRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkin_rewards', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('consecutive_days')->nullable()->default(1);
            $table->tinyInteger('reward_type')->nullable()->default(1);
            $table->decimal('reward_value', 18, 2)->nullable();
            $table->integer('validity_days')->nullable()->default(1);
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
        Schema::dropIfExists('checkin_rewards');
    }
}
