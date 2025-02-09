<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentPlanPriceToProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            //
            $table->decimal('upfront', 12, 2)->nullable();
            $table->decimal('monthly', 12, 2)->nullable();
            $table->decimal('outright', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            //
            $table->dropColumn('upfront');
            $table->dropColumn('monthly');
            $table->dropColumn('outright');
        });
    }
}
