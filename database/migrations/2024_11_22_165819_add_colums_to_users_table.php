<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('username')->nullable()->after('fullname')->unique();

            $table->string('calling_code')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->tinyInteger('account_type')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('username');
            
            $table->dropColumn('calling_code')->nullable();
            $table->dropColumn('address_1')->nullable();
            $table->dropColumn('address_2')->nullable();
            $table->dropColumn('city')->nullable();
            $table->dropColumn('state')->nullable();
            $table->dropColumn('postcode')->nullable();
            $table->dropColumn('account_type')->nullable();
        });
    }
}
