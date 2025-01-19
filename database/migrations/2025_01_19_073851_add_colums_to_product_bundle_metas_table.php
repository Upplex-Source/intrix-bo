<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumsToProductBundleMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_bundle_metas', function (Blueprint $table) {
            //
            $table->foreignId('product_bundle_id')->nullable()->constrained('product_bundles')->onUpdate( 'restrict')->onDelete('cascade');
            $table->tinyInteger('status')->default(10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_bundle_metas', function (Blueprint $table) {
            //
            $table->dropForeign(['product_bundle_id']);
            $table->dropColumn('product_bundle_id');
            $table->dropColumn('status');
        });
    }
}
