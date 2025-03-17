<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShipmentColumsToCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            //
            $table->string('country',)->nullable();
            $table->string('company_name',)->nullable();
            $table->string('fullname')->nullable();
            $table->string('email')->nullable();
            $table->string('calling_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->tinyInteger('step')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            //
            $table->dropColumn('country',);
            $table->dropColumn('company_name',);
            $table->dropColumn('fullname');
            $table->dropColumn('email');
            $table->dropColumn('calling_code');
            $table->dropColumn('phone_number');
            $table->dropColumn('address_1');
            $table->dropColumn('address_2');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('postcode');
        });
    }
}
