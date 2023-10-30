<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('driver_id')->constrained('companies')->onUpdate( 'restrict')->onDelete('cascade');
            $table->string('trailer_number')->after('name')->nullable();
            $table->tinyInteger('permit_type')->default(1)->after('permit_number');
            $table->timestamp('permit_start_date')->after('insurance_expiry_date')->nullable();
            $table->timestamp('inspection_expiry_date')->after('permit_expiry_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id','trailer_number','permit_type','permit_start_date','inspection_expiry_date');
        });
    }
}
