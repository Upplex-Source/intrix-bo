<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToBundlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->tinyInteger('promotion_enabled')->nullable()->default(0);
            $table->timestamp('promotion_start')->nullable();
            $table->timestamp('promotion_end')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('promotion_price', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bundles', function (Blueprint $table) {
            //
            $table->dropColumn('promotion_enabled');
            $table->dropColumn('promotion_start');
            $table->dropColumn('promotion_end');
            $table->dropColumn('price');
            $table->dropColumn('promotion_price');
        });
    }
}
