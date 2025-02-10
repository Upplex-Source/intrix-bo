<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryToGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guests', function (Blueprint $table) {
            //
            $table->string('country',)->nullable();
            $table->string('company_name',)->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('country',)->nullable();
            $table->string('company_name',)->nullable();
            $table->string('fullname')->nullable();
            $table->string('email')->nullable();
            $table->string('session_key', 100)->nullable();
            $table->string('calling_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guests', function (Blueprint $table) {
            //
            $table->dropColumn('country');
            $table->dropColumn('company_name');
        });

        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn('country');
            $table->dropColumn('company_name');
            $table->dropColumn('fullname')->nullable();
            $table->dropColumn('email')->nullable();
            $table->dropColumn('session_key', 100)->nullable();
            $table->dropColumn('calling_code')->nullable();
            $table->dropColumn('phone_number')->nullable();
            $table->dropColumn('address_1')->nullable();
            $table->dropColumn('address_2')->nullable();
            $table->dropColumn('city')->nullable();
            $table->dropColumn('state')->nullable();
            $table->dropColumn('postcode')->nullable();
        });
    }
}
