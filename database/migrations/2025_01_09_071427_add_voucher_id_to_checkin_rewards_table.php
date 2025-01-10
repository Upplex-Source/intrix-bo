<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVoucherIdToCheckinRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checkin_rewards', function (Blueprint $table) {
            //
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onUpdate( 'restrict')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            //
            $table->integer('check_in_streak')->nullable()->default(0);
            $table->integer('total_check_in')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checkin_rewards', function (Blueprint $table) {
            //
            $table->dropForeign('voucher_id');
            $table->dropColumn('voucher_id');
        });

        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('check_in_streak');
            $table->dropColumn('total_check_in');
        });
    }
}
