<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxMethodIdToQuotationRelatedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('tax_method_id')->nullable()->constrained('tax_methods')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('tax_method_id')->nullable()->constrained('tax_methods')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('tax_method_id')->nullable()->constrained('tax_methods')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreignId('tax_method_id')->nullable()->constrained('tax_methods')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['tax_method_id']);
            $table->dropColumn('tax_method_id');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['tax_method_id']);
            $table->dropColumn('tax_method_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['tax_method_id']);
            $table->dropColumn('tax_method_id');
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign(['tax_method_id']);
            $table->dropColumn('tax_method_id');
        });
    }
}
