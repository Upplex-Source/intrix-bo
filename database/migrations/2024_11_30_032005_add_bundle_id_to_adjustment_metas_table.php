<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBundleIdToAdjustmentMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adjustment_metas', function (Blueprint $table) {
            $table->foreignId('bundle_id')->nullable()->constrained('bundles')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adjustment_metas', function (Blueprint $table) {
            $table->dropForeign(['bundle']);
            $table->dropColumn('bundle');
        });
    }
}
