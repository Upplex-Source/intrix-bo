<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUserBundleHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_bundle_histories', function (Blueprint $table) {
            //
            $table->tinyInteger('batch')->nullable();
            $table->tinyInteger('claimed_cups')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_bundle_histories', function (Blueprint $table) {
            //
            $table->dropColumn('batch');
            $table->dropColumn('claimed_cups');
        });
    }
}
