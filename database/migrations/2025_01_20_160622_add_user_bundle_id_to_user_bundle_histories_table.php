<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserBundleIdToUserBundleHistoriesTable extends Migration
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
            $table->foreignId('user_bundle_id')->nullable()->constrained('user_bundles')->onUpdate( 'restrict')->onDelete('cascade');
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
            $table->dropForeign(['user_bundle_id']);
            $table->dropColumn('user_bundle_id');
        });
    }
}
