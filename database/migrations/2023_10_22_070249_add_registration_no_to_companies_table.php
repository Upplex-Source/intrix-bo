<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegistrationNoToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('registration_no')->nullable()->after('name');
            $table->string('email',50)->nullable()->after('registration_no');
            $table->string('phone_number',15)->nullable()->after('email');
            $table->text('address')->nullable()->after('phone_number');
            $table->string('bank_name')->nullable()->after('address');
            $table->string('account_no')->nullable()->after('bank_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('registration_no','address','email','phone_number','bank_name','account_no');
        });
    }
}
