<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeVendingMachineTableColums extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vending_machines', function (Blueprint $table) {
            $table->dropColumn('longtide');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vending_machines', function (Blueprint $table) {
            $table->string('longtide')->nullable();
        });
    }
}
