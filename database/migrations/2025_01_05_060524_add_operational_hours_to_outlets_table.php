<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOperationalHoursToOutletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('outlets', function (Blueprint $table) {
            //
            $table->timestamp('opening_hour')->nullable();
            $table->timestamp('closing_hour')->nullable();
        });

        Schema::table('vending_machines', function (Blueprint $table) {
            //
            $table->timestamp('opening_hour')->nullable();
            $table->timestamp('closing_hour')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('outlets', function (Blueprint $table) {
            //
            $table->dropColumn('opening_hour');
            $table->dropColumn('closing_hour');
        });
        Schema::table('vending_machines', function (Blueprint $table) {
            //
            $table->dropColumn('opening_hour');
            $table->dropColumn('closing_hour');
        });
    }
}
