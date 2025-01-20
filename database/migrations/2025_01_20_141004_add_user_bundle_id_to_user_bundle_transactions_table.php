<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserBundleIdToUserBundleTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_bundle_transactions', function (Blueprint $table) {
            //
            $table->foreignId('user_bundle_id')->nullable()->constrained('user_bundles')->onUpdate( 'restrict')->onDelete('cascade');
        });

        Schema::table('user_bundles', function (Blueprint $table) {
            $table->tinyInteger('payment_attempt')->default(1);
            $table->longText('payment_url')->nullable();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_bundle_transactions', function (Blueprint $table) {
            //
            $table->dropForeign(['user_bundle_id']);
            $table->dropColumn('user_bundle_id');
        });

        Schema::table('user_bundles', function (Blueprint $table) {
            $table->dropColumn('payment_attempt');
            $table->dropColumn('payment_url');
        });
    }
}
