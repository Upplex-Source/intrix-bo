<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            //
            $table->string('remarks')->nullable()->after('address');
            $table->string('state',50)->nullable()->after('address');
            $table->string('city',50)->nullable()->after('address');
            $table->string('postcode',5)->nullable()->after('address');
            $table->string('address_2')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('address_2','city','state','postcode','remarks');

        });
    }
}
